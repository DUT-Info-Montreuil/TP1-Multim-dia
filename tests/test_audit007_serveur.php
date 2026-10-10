<?php
/**
 * Test d'intégration AUDIT-007 : module « serveur » dont le périmètre (id_buvette) est choisi
 * par l'URL, sans contrôle de rôle ni d'appartenance.
 *
 * Exécution (depuis la racine du dépôt) :
 *     php tests/test_audit007_serveur.php
 *
 * Aucune dépendance : PHP CLI + pdo_sqlite. Aucune base MySQL requise.
 * Le vrai ContServeur / ModeleServeur / VueServeur sont exécutés ; seule la connexion PDO est
 * remplacée par une base SQLite en mémoire (injectée dans Connexion::$bdd par réflexion, sans
 * toucher à connexion.php). Chaque scénario tourne dans un sous-processus (le contrôleur
 * peut appeler exit()).
 *
 * Pour chaque scénario on observe trois choses :
 *   - donnees  : les commandes de la buvette 1 apparaissent-elles dans la page servie ?
 *   - session  : que vaut $_SESSION['id_buvette'] après l'exécution ?
 *   - statut   : statut final de la commande 100 (buvette 1, initialement « Payé »).
 *
 * Limite : l'action valider_vente lit php://input, vide en CLI ; elle n'est donc pas couverte
 * ici. Elle consomme le même $idBuvette que les scénarios ci-dessous.
 *
 * Code de sortie : 0 si tous les scénarios attendus sont respectés, 1 sinon.
 */

const MARQUEUR_DASHBOARD = 'Suivi Commandes';   // titre de la page de suivi
const MARQUEUR_DONNEES_A = 'ESPIONDATA';          // nom du client titulaire de la commande 100 (buvette 1)

// id_utilisateur : 10 serveur de A, 11 client de A, 12 serveur de A expiré, 13 serveur de B.
// Buvettes : 1 = A, 2 = B. Commande 100 : buvette 1, statut « Payé ».
$scenarios = [
    'serveur_sur_sa_buvette' => [
        'desc' => 'Serveur de A consulte A (cas légitime)',
        'user' => 10, 'get' => ['id_buvette' => '1'],
        'attendu' => ['donnees' => true, 'session' => 1, 'statut' => 'Payé'],
    ],
    'serveur_fait_avancer_commande_de_sa_buvette' => [
        'desc' => 'Serveur de A fait avancer la commande 100 de A (cas légitime)',
        'user' => 10, 'get' => ['id_buvette' => '1', 'action' => 'cycle_statut', 'id' => '100', 'actuel' => 'Payé'],
        'attendu' => ['donnees' => false, 'session' => 1, 'statut' => 'Préparation'],
    ],
    'client_sans_role_serveur' => [
        'desc' => 'Client force ?id_buvette=1 : ne doit ni stocker la buvette ni voir ses commandes',
        'user' => 11, 'get' => ['id_buvette' => '1'],
        'attendu' => ['donnees' => false, 'session' => null, 'statut' => 'Payé'],
    ],
    'serveur_autre_buvette' => [
        'desc' => 'Serveur de B force ?id_buvette=1 (hors appartenance)',
        'user' => 13, 'get' => ['id_buvette' => '1'],
        'attendu' => ['donnees' => false, 'session' => null, 'statut' => 'Payé'],
    ],
    'serveur_affectation_expiree' => [
        'desc' => 'Serveur de A dont l\'affectation est expirée force ?id_buvette=1',
        'user' => 12, 'get' => ['id_buvette' => '1'],
        'attendu' => ['donnees' => false, 'session' => null, 'statut' => 'Payé'],
    ],
    'id_buvette_non_numerique' => [
        'desc' => 'Serveur de A avec id_buvette = "1abc" (aucun cast permissif)',
        'user' => 10, 'get' => ['id_buvette' => '1abc'],
        'attendu' => ['donnees' => false, 'session' => null, 'statut' => 'Payé'],
    ],
    'session_pre_empoisonnee' => [
        'desc' => 'Client avec $_SESSION[id_buvette]=1 posé ailleurs (sans paramètre URL)',
        'user' => 11, 'get' => [], 'session_init' => ['id_buvette' => 1],
        'attendu' => ['donnees' => false, 'session' => null, 'statut' => 'Payé'],
    ],
    'client_change_statut_commande' => [
        'desc' => 'Client appelle cycle_statut sur la commande 100 : statut inchangé attendu',
        'user' => 11, 'get' => ['id_buvette' => '1', 'action' => 'cycle_statut', 'id' => '100', 'actuel' => 'Payé'],
        'attendu' => ['donnees' => false, 'session' => null, 'statut' => 'Payé'],
    ],
    'serveur_b_change_commande_de_a' => [
        'desc' => 'Serveur de B (sur B) appelle cycle_statut sur la commande 100 de A : statut inchangé attendu',
        'user' => 13, 'get' => ['id_buvette' => '2', 'action' => 'cycle_statut', 'id' => '100', 'actuel' => 'Payé'],
        'attendu' => ['donnees' => false, 'session' => 2, 'statut' => 'Payé'],
    ],
];

/* ------------------------------------------------------------------ */
/* Mode « worker » : exécute UN scénario et affiche un JSON            */
/* ------------------------------------------------------------------ */
if (isset($argv[1]) && str_starts_with($argv[1], '--cas=')) {
    $nom = substr($argv[1], 6);
    $sc = $scenarios[$nom] ?? null;
    if ($sc === null) { fwrite(STDERR, "Scénario inconnu\n"); exit(2); }

    ini_set('display_errors', '0');
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
        CREATE TABLE affecter (id_utilisateur INTEGER, id_buvette INTEGER, id_role INTEGER, date_fin TEXT);
        CREATE TABLE produit (id_produit INTEGER PRIMARY KEY, nom_produit TEXT, prix_produit REAL,
            description TEXT, image_produit TEXT, type_produit TEXT);
        CREATE TABLE commande (id_commande INTEGER PRIMARY KEY, statut TEXT, date_commande TEXT,
            prix_total REAL, id_utilisateur INTEGER, id_buvette INTEGER, est_paye INTEGER);
        CREATE TABLE ligne_commande (id_commande INTEGER, id_produit INTEGER, quantite INTEGER,
            prix_unitaire_moment_vente REAL);
        CREATE TABLE notification_validation (id_utilisateur INTEGER, id_commande INTEGER,
            montant REAL, est_vue INTEGER DEFAULT 0, date_creation TEXT);

        INSERT INTO role_utilisateur VALUES (1, 'Serveur'), (2, 'Gestionnaire'), (3, 'Client');
        INSERT INTO utilisateur VALUES (10, 'Serveur', 'A', 'sa@x.fr'), (11, 'ESPIONDATA', 'Client', 'c@x.fr'),
            (12, 'Serveur', 'Expire', 'se@x.fr'), (13, 'Serveur', 'B', 'sb@x.fr');
        INSERT INTO affecter VALUES (10, 1, 1, NULL);          -- serveur de A
        INSERT INTO affecter VALUES (11, 1, 3, NULL);          -- client de A
        INSERT INTO affecter VALUES (12, 1, 1, '2000-01-01');  -- serveur de A expiré
        INSERT INTO affecter VALUES (13, 2, 1, NULL);          -- serveur de B
        INSERT INTO produit VALUES (1, 'Cafe', 1.5, 'noir', 'default.jpg', 'Boisson Chaude');
        INSERT INTO commande VALUES (100, 'Payé', '2026-01-01 12:00:00', 1.5, 11, 1, 1);
        INSERT INTO ligne_commande VALUES (100, 1, 1, 1.5);
    ");
    $prop = new ReflectionProperty('Connexion', 'bdd');
    $prop->setValue(null, $pdo);

    $_SESSION = ['user' => ['id_utilisateur' => $sc['user'], 'role' => 'Client']] + ($sc['session_init'] ?? []);
    $_GET = $sc['get'];

    ob_start();
    register_shutdown_function(function () use ($pdo) {   // exécuté aussi après exit()
        $sortie = ob_get_clean();
        echo json_encode([
            'donnees' => str_contains($sortie, MARQUEUR_DONNEES_A),
            'dashboard' => str_contains($sortie, MARQUEUR_DASHBOARD),
            'session' => isset($_SESSION['id_buvette']) ? (int)$_SESSION['id_buvette'] : null,
            'statut' => $pdo->query('SELECT statut FROM commande WHERE id_commande = 100')->fetchColumn(),
        ]);
    });

    require_once $racine . '/app/modules/modServeur/cont_serveur.php';
    (new ContServeur())->exec();
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
        $ecarts = [];
        $att = $sc['attendu'];
        if ($res['donnees'] !== $att['donnees']) {
            $ecarts[] = $res['donnees'] ? 'FAILLE : commandes de la buvette 1 affichées' : 'commandes attendues absentes';
        }
        if ($res['session'] !== $att['session']) {
            $ecarts[] = 'session[id_buvette] = ' . var_export($res['session'], true) . ' (attendu ' . var_export($att['session'], true) . ')';
        }
        if ($res['statut'] !== $att['statut']) {
            $ecarts[] = 'statut commande 100 = « ' . $res['statut'] . ' » (attendu « ' . $att['statut'] . ' »)';
        }
        $ok = $ecarts === [];
        $detail = $ok ? 'conforme' : implode(' ; ', $ecarts);
    }
    printf("[%s] %s — %s\n", $ok ? 'OK  ' : 'ECHEC', $sc['desc'], $detail);
    if (!$ok) { $echecs++; }
}

echo $echecs === 0 ? "\nTous les scénarios passent.\n" : "\n$echecs scénario(s) en échec.\n";
exit($echecs === 0 ? 0 : 1);
