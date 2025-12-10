<?php
// 1. Est-ce que ob_start() est bien au tout début ?
ob_start();

// ... Ton code de connexion ...

$module = isset($_GET['module']) ? $_GET['module'] : 'accueil'; // Si pas de module, on va sur 'accueil'

switch ($module) {
    case 'accueil':
        // 2. Est-ce que tu inclus bien le fichier ?
        require_once 'modules/mod_accueil/mod_accueil.php';
        // 3. IMPORTANT : Est-ce que tu crées l'objet ? (Le new Mod...)
        $mod = new ModAccueil();
        break;

    // ... autres cases ...
}

// 4. Est-ce que tu récupères le contenu ?
$tampon = ob_get_clean();

// 5. Est-ce que tu appelles le template ?
require_once 'template.php';
?>