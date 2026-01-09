<?php

session_start();
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
    case 'menu':
        require_once 'app/modules/modMenu/mod_menu.php';
        $mod = new ModMenu();
        break;
    case 'panier':
        require_once 'app/modules/modPanier/mod_panier.php';
        $mod = new ModPanier();
        break;
    case 'compte':
        require_once 'app/modules/modCompte/mod_compte.php';
        $mod = new ModCompte();
        break;
}

$tampon = ob_get_clean();

require_once 'template.php';
?>