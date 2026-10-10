<?php
/**
 * Test d'intégration AUDIT-006 : module « gestionnaire » accessible sans droit de gestion.
 *
 * Exécution (depuis la racine du dépôt) :
 *     php tests/test_audit006_gestionnaire.php
 *
 * Aucune dépendance : PHP CLI + pdo_sqlite. Aucune base MySQL requise.
 * Le vrai ContGestionnaire / ModeleGestionnaire / VueGestionnaire sont exécutés ;
 * seule la connexion PDO est remplacée par une base SQLite en mémoire (injectée dans
 * Connexion::$bdd par réflexion, sans toucher à connexion.php).
 * Chaque scénario tourne dans un sous-processus, car le contrôleur appelle exit().
 *
 * Code de sortie : 0 si tous les scénarios attendus sont respectés, 1 sinon.
 */

const MARQUEUR_PAGE_GESTION = 'Alertes Stock';   // présent uniquement sur la grille de gestion

// id_utilisateur : 10 gestionnaire de A, 11 client de A, 12 gestionnaire de A expiré,
// 13 gestionnaire de B. Buvettes : 1 = A, 2 = B.
$scenarios = [
    'gestionnaire_sur_sa_buvette' => [
        'desc' => 'Gestionnaire de A consulte A (cas légitime)',
        'user' => 10, 'get' => ['id_buvette' => '1'], 'attendu' => 'acces',
    ],
    'client_sans_role_de_gestion' => [
        'desc' => 'Client (sans rôle de gestion) consulte A',
        'user' => 11, 'get' => ['id_buvette' => '1'], 'attendu' => 'refus',
    ],
    'gestionnaire_autre_buvette' => [
        'desc' => 'Gestionnaire de B consulte A (hors appartenance)',
        'user' => 13, 'get' => ['id_buvette' => '1'], 'attendu' => 'refus',
    ],
    'gestionnaire_affectation_expiree' => [
        'desc' => 'Gestionnaire de A dont l\'affectation est expirée consulte A',
        'user' => 12, 'get' => ['id_buvette' => '1'], 'attendu' => 'refus',
    ],
    'id_buvette_non_numerique' => [
        'desc' => 'Gestionnaire de A avec id_buvette = "1abc"',
        'user' => 10, 'get' => ['id_buvette' => '1abc'], 'attendu' => 'refus',
    ],
    'ecriture_par_client' => [
        'desc' => 'Client crée un produit sur A (valider_nouveau) : aucune écriture attendue',
        'user' => 11, 'get' => ['id_buvette' => '1', 'action' => 'valider_nouveau'],
        'post' => ['nom' => 'PIRATE', 'prix' => '1', 'description' => 'x', 'type_produit' => 'Boisson Froide'],
        'attendu' => 'aucune_ecriture',
    ],
];

/* ------------------------------------------------------------------ */
/* Mode « worker » : exécute UN scénario et affiche un JSON            */
/* ------------------------------------------------------------------ */
if (isset($argv[1]) && str_starts_with($argv[1], '--cas=')) {
    $nom = substr($argv[1], 6);
    $sc = $scenarios[$nom] ?? null;
    if ($sc === null) { fwrite(STDERR, "Scénario inconnu\n"); exit(2); }

    $racine = dirname(__DIR__);
    chdir($racine);

    require_once $racine . '/connexion.php';   // définit uniquement la classe Connexion

    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->sqliteCreateFunction('CURDATE', fn() => date('Y-m-d'));
    $pdo->exec("
        CREATE TABLE utilisateur (id_utilisateur INTEGER PRIMARY KEY, nom TEXT, prenom TEXT, email TEXT);
        CREATE TABLE role_utilisateur (id_role INTEGER PRIMARY KEY, nom_role TEXT);
        CREATE TABLE une_buvette (id_buvette INTEGER PRIMARY KEY, nom TEXT);
        CREATE TABLE affecter (id_utilisateur INTEGER, id_buvette INTEGER, id_role INTEGER, date_fin TEXT);
        CREATE TABLE adhesion (id_utilisateur INTEGER, id_buvette INTEGER, date_adhesion TEXT);
        CREATE TABLE produit (id_produit INTEGER PRIMARY KEY AUTOINCREMENT, nom_produit TEXT,
            prix_produit REAL, description TEXT, image_produit TEXT, type_produit TEXT);
        CREATE TABLE contient (id_inventaire INTEGER, id_produit INTEGER, quantite INTEGER, seuil_alerte INTEGER);
        CREATE TABLE concerner (id_inventaire INTEGER, id_buvette INTEGER);

        INSERT INTO role_utilisateur VALUES (1, 'Gestionnaire'), (2, 'Client');
        INSERT INTO une_buvette VALUES (1, 'Buvette A'), (2, 'Buvette B');
        INSERT INTO affecter VALUES (10, 1, 1, NULL);          -- gestionnaire de A
        INSERT INTO affecter VALUES (11, 1, 2, NULL);          -- client de A
        INSERT INTO affecter VALUES (12, 1, 1, '2000-01-01');  -- gestionnaire de A expiré
        INSERT INTO affecter VALUES (13, 2, 1, NULL);          -- gestionnaire de B
        INSERT INTO concerner VALUES (1, 1), (2, 2);
        INSERT INTO produit (nom_produit, prix_produit, description, image_produit, type_produit)
            VALUES ('Cafe', 1.5, 'noir', 'default.jpg', 'Boisson Chaude');
        INSERT INTO contient VALUES (1, 1, 10, 5);
    ");
    $prop = new ReflectionProperty('Connexion', 'bdd');
    $prop->setValue(null, $pdo);

    $token = str_repeat('a', 64);
    $_SESSION = [
        'user' => ['id_utilisateur' => $sc['user'], 'role' => 'Client'],
        'csrf_token' => $token,
        'csrf_expiration' => time() + 1200,
    ];
    $_GET = $sc['get'];
    if (isset($sc['post'])) {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $sc['post'] + ['csrf_token' => $token];
    }

    ob_start();
    register_shutdown_function(function () use ($pdo) {   // exécuté aussi après exit()
        $sortie = ob_get_clean();
        echo json_encode([
            'page_gestion_servie' => str_contains($sortie, MARQUEUR_PAGE_GESTION),
            'nb_produits' => (int)$pdo->query('SELECT COUNT(*) FROM produit')->fetchColumn(),
        ]);
    });

    require_once $racine . '/app/modules/modGestionnaire/cont_gestionnaire.php';
    (new ContGestionnaire())->exec();
    exit(0);
}

/* ------------------------------------------------------------------ */
/* Mode « orchestrateur » : lance chaque scénario et vérifie l'attendu */
/* ------------------------------------------------------------------ */
$echecs = 0;
foreach ($scenarios as $nom => $sc) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --cas=' . escapeshellarg($nom) . ' 2>&1';
    $brut = shell_exec($cmd);
    $res = json_decode((string)$brut, true);

    if (!is_array($res)) {
        $ok = false;
        $detail = 'sortie inexploitable : ' . trim(substr((string)$brut, 0, 300));
    } else {
        switch ($sc['attendu']) {
            case 'acces':
                $ok = $res['page_gestion_servie'] === true;
                $detail = $ok ? 'accès accordé' : 'accès refusé à tort';
                break;
            case 'refus':
                $ok = $res['page_gestion_servie'] === false;
                $detail = $ok ? 'accès refusé' : 'FAILLE : page de gestion servie';
                break;
            default: // aucune_ecriture
                $ok = $res['nb_produits'] === 1;
                $detail = $ok ? 'aucune écriture' : 'FAILLE : ' . ($res['nb_produits'] - 1) . ' produit(s) écrit(s)';
        }
    }
    printf("[%s] %s — %s\n", $ok ? 'OK  ' : 'ECHEC', $sc['desc'], $detail);
    if (!$ok) { $echecs++; }
}

echo $echecs === 0 ? "\nTous les scénarios passent.\n" : "\n$echecs scénario(s) en échec.\n";
exit($echecs === 0 ? 0 : 1);
