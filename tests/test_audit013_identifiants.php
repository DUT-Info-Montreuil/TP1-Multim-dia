<?php
/**
 * Test statique AUDIT-013 : identifiants de base de données committés dans le dépôt.
 *
 * Exécution (depuis la racine du dépôt) :
 *     php tests/test_audit013_identifiants.php
 *
 * Aucune dépendance : PHP CLI + git. Aucune base de données requise.
 * Le test n'affiche jamais le contenu des fichiers sensibles, seulement leur nom.
 *
 * Code de sortie : 0 si tous les contrôles passent, 1 sinon.
 */

chdir(dirname(__DIR__));

/** Exécute une commande git et renvoie [code de sortie, lignes de sortie]. */
function git(string $args): array {
    exec('git ' . $args . ' 2>&1', $lignes, $code);
    return [$code, $lignes];
}

$controles = [];

// 1. Le fichier réel ne doit plus être suivi par Git
[$code] = git('ls-files --error-unmatch -- connexion.php');
$controles[] = [
    'connexion.php n\'est pas suivi par Git',
    $code !== 0,
    'FAILLE : connexion.php est suivi (donc committé avec ses identifiants)',
];

// 2. Le fichier réel doit être ignoré par .gitignore
[$code] = git('check-ignore -q -- connexion.php');
$controles[] = [
    'connexion.php est ignoré par .gitignore',
    $code === 0,
    'FAILLE : aucune règle .gitignore ne protège connexion.php',
];

// 3. Le modèle doit rester versionné pour les futurs développeurs
[$code] = git('ls-files --error-unmatch -- connexion.example.php');
$controles[] = [
    'connexion.example.php est versionné',
    $code === 0,
    'le modèle connexion.example.php est absent du suivi Git',
];

// 4. Le modèle ne doit contenir que des valeurs fictives
$modele = is_file('connexion.example.php') ? file_get_contents('connexion.example.php') : '';
$manquants = array_filter(
    ['HOTE_MYSQL', 'NOM_BDD', 'UTILISATEUR', 'MOT_DE_PASSE'],
    fn($p) => !str_contains($modele, $p)
);
$controles[] = [
    'connexion.example.php ne contient que des valeurs fictives',
    $modele !== '' && $manquants === [],
    'placeholders manquants dans le modèle : ' . implode(', ', $manquants),
];

// 5. Aucun autre fichier suivi ne doit embarquer d'identifiants (même en commentaire)
[$code, $fichiers] = git(
    'grep -lEI "mysql:host=|\\$(password|user|host|port|dbname)\\s*=\\s*[\'\\"]" '
    . '-- . ":(exclude)connexion.example.php" ":(exclude)tests/test_audit013_identifiants.php"'
);
$fichiers = $code === 0 ? $fichiers : [];
$controles[] = [
    'aucun fichier suivi (hors modèle) ne contient d\'identifiants',
    $fichiers === [],
    'FAILLE : identifiants détectés dans : ' . implode(', ', $fichiers),
];

$echecs = 0;
foreach ($controles as [$desc, $ok, $detailEchec]) {
    printf("[%s] %s%s\n", $ok ? 'OK  ' : 'ECHEC', $desc, $ok ? '' : ' — ' . $detailEchec);
    if (!$ok) { $echecs++; }
}

echo $echecs === 0 ? "\nTous les contrôles passent.\n" : "\n$echecs contrôle(s) en échec.\n";
exit($echecs === 0 ? 0 : 1);
