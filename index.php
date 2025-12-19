<?php
ob_start();

$module = isset($_GET['module']) ? $_GET['module'] : 'accueil';

switch ($module) {
    case 'accueil':
        require_once 'modules/mod_accueil/mod_accueil.php';
        $mod = new ModAccueil();
        break;

}

$tampon = ob_get_clean();

require_once 'template.php';
?>