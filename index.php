<?php

session_start();
ob_start();

$module = isset($_GET['module']) ? $_GET['module'] : 'accueil';

// Seuls l'accueil (contenant Histoire/Galerie) et la connexion sont publics
$modulesPublics = ['accueil', 'connexion'];

if (!isset($_SESSION['user']) && !in_array($module, $modulesPublics)) {
    header("Location: index.php?module=connexion");
    exit();
}

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
    case 'gestionnaire':
        require_once 'app/modules/modGestionnaire/mod_gestionnaire.php';
        $mod = new ModGestionnaire();
        break;
    case 'menu':
        require_once 'app/modules/modMenu/mod_menu.php';
        $mod = new ModMenu();
        break;
    case 'superadmin':
        require_once 'app/modules/modSuperAdmin/mod_super_admin.php';
        $mod = new ModSuperAdmin();
        break;
    case 'panier':
        require_once 'app/modules/modPanier/mod_panier.php';
        $mod = new ModPanier();
        break;
    case 'compte':
        require_once 'app/modules/modCompte/mod_compte.php';
        $mod = new ModCompte();
        break;
    case 'serveur':
        require_once 'app/modules/modServeur/mod_serveur.php';
        $mod = new ModServeur();
        break;
    case 'detailsCommande':
        require_once 'app/modules/modDetailsCommande/mod_details_commande.php';
        $mod = new ModDetailsCommande();
        break;
        case 'creationBuvette':
        require_once 'app/modules/modCreationBuvette/mod_creation_buvette.php';
        $mod = new ModCreationBuvette();
        break;
}

$tampon = ob_get_clean();

require_once 'template.php';
?>