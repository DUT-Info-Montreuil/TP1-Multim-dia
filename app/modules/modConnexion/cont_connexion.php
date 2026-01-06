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
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';

                $user = $this->modele->verifierConnexion($email, $password);

                if ($user) {
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'prenom' => $user['prenom'],
                        'nom' => $user['nom']
                    ];

                    $_SESSION['bienvenue'] = "Bienvenue, " . htmlspecialchars($user['prenom']) . " !";

                    header('Location: index.php?module=accueil');
                    exit();
                } else {
                    $this->vue->afficherFormulaireConnexion("Identifiants incorrects.");
                }
                break;

            case 'valider_inscription':
                $succes = $this->modele->inscrireUtilisateur($_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['password']);
                if ($succes) {
                    $this->vue->afficherFormulaireConnexion(null, "Inscription réussie ! Connectez-vous.");
                } else {
                    $this->vue->afficherFormulaireInscription("Cet email est déjà utilisé.");
                }
                break;

            case 'afficher_inscription':
                $this->vue->afficherFormulaireInscription();
                break;

            case 'deconnexion':
                session_unset();
                session_destroy();
                header('Location: index.php?module=accueil');
                exit();

            default:
                $this->vue->afficherFormulaireConnexion();
                break;
        }
    }
}