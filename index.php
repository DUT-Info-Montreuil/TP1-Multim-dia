<?php
session_start();
// require_once 'connexion.php'; // Si tu en as besoin

// 1. On ouvre la mémoire tampon
ob_start();

// ... TON CODE DE ROUTAGE (Switch case pour les modules) ...
// ex: $mod = new ModAccueil();

// 2. On récupère tout ce qui a été affiché par le module
$tampon = ob_get_clean();

// 3. On inclut le template (qui contient Bootstrap et affiche $tampon)
require_once 'template.php';
?>