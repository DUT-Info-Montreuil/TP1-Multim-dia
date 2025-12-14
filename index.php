<?php
ob_start();

$module = isset($_GET['module']) ? $_GET['module'] : 'accueil';

switch ($module) {
    case 'accueil':
        require_once 'app/modules/modAccueil/mod_accueil.php';
        $mod = new ModAccueil();
        break;
    case 'connexion':
        require_once 'app/modules/modConnexion/mod_connexion.php';
        $mod = new ModConnexion();
        break;
    case 'buvettes':
        require_once 'app/modules/modBuvettes/mod_buvettes.php';
        $mod = new ModBuvettes();
        break;
}

$tampon = ob_get_clean();

require_once 'template.php';
?>