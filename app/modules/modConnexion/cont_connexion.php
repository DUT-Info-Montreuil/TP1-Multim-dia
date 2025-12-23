<?php
require_once 'vue_connexion.php';
require_once 'modele_connexion.php';

class ContConnexion {
    private $vue;
    private $modele;

    public function __construct() {
        $this->vue = new VueConnexion();
        $this->modele = new ModeleConnexion();
    }

    public function exec() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'afficher_connexion';

        switch ($action) {
            case 'verifie_connexion':
                $user = $this->modele->verifierConnexion($_POST['email'], $_POST['password']);
                if ($user) {
                    $_SESSION['user'] = $user;
                    header('Location: index.php?module=accueil');
                } else {
                    echo "Erreur d'identifiants";
                    $this->vue->afficherFormulaireConnexion();
                }
                break;

            case 'valider_inscription':
                $succes = $this->modele->inscrireUtilisateur(
                    $_POST['nom'],
                    $_POST['prenom'],
                    $_POST['email'],
                    $_POST['password']
                );
                if ($succes) {
                    header('Location: index.php?module=connexion&action=afficher_connexion');
                } else {
                    echo "Erreur : INE déjà existant";
                    $this->vue->afficherFormulaireInscription();
                }
                break;

            case 'afficher_inscription':
                $this->vue->afficherFormulaireInscription();
                break;

            default:
                $this->vue->afficherFormulaireConnexion();
                break;
        }
    }
}