<?php
require_once 'vue_connexion.php';
require_once 'modele_connexion.php';
require_once __DIR__ . '/../../../csrf.php';

class ContConnexion {
    private $vue;
    private $modele;
    private $csrf;

    public function __construct() {
        $this->vue = new VueConnexion();
        $this->modele = new ModeleConnexion();
        $this->csrf = new csrf();
    }

    public function exec() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'afficher_connexion';
        $token = $this->csrf->getToken();

        switch ($action) {
            case 'verifie_connexion':
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';

                $user = $this->modele->verifierConnexion($email, $password);

                if ($user) {
                    $_SESSION['user'] = [
                        'id_utilisateur' => $user['id_utilisateur'],
                        'prenom'         => $user['prenom'],
                        'nom'            => $user['nom'],
                        'email'          => $user['email'],
                        'role'           => $user['nom_role'] ?? 'Client'
                    ];

                    $_SESSION['bienvenue'] = "Bienvenue, " . htmlspecialchars($user['prenom']) . " !";
                    header('Location: index.php?module=accueil');
                    exit();
                } else {
                    $this->vue->afficherFormulaireConnexion("Email ou mot de passe incorrect.");
                }
                break;

            case 'valider_inscription':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    $this->vue->afficherFormulaireInscription("Erreur de sécurité.", $token);
                    return;
                }

                $succes = $this->modele->inscrireUtilisateur($_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['password']);
                if ($succes) {
                    $this->vue->afficherFormulaireConnexion(null, "Inscription réussie ! Connectez-vous.", $token);
                } else {
                    $this->vue->afficherFormulaireInscription("Cet email est déjà utilisé.", $token);
                }
                break;

            case 'afficher_inscription':
                $this->vue->afficherFormulaireInscription(null, $token);
                break;

            case 'deconnexion':
                session_unset();
                session_destroy();
                header('Location: index.php?module=accueil');
                exit();

            default:
                $this->vue->afficherFormulaireConnexion(null, null, $token);
                break;
        }
    }
}