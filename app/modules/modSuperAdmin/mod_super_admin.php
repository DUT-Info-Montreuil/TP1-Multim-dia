<?php
require_once 'cont_super_admin.php';

class ModSuperAdmin {
    public function __construct() {
        // verifier si l'utilisateur a le role administrateur pour donner acces au module
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?module=connexion');
            exit();
        }

        if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'Administrateur') {
            $_SESSION['erreur'] = "Accès non autorisé. Vous devez être administrateur.";
            header('Location: index.php');
            exit();
        }

        $controleur = new ContSuperAdmin();
        $controleur->exec();
    }
}
?>
