<?php
require_once 'vue_creation_buvette.php';
require_once 'modele_creation_buvette.php';

class ContCreationBuvette
{
    private $vue;
    private $modele;

    public function __construct()
    {
        $this->vue = new VueCreationBuvette();
        $this->modele = new ModeleCreationBuvette();
    }

    public function exec()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?module=connexion');
            exit();
        }

        $action = isset($_GET['action']) ? $_GET['action'] : 'formulaire';

        switch ($action) {
            case 'creer':
                $this->traiterCreation();
                break;

            case 'formulaire':
            default:
                $this->vue->afficherFormulaire();
                break;
        }
    }

    private function traiterCreation()
    {
        $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $idUser = $_SESSION['user']['id_utilisateur'];

        if (empty($nom) || empty($description)) {
            $this->vue->afficherFormulaire("Tous les champs sont obligatoires.");
            return;
        }

        if ($this->modele->aDemandeEnCours($idUser)) {
            $this->vue->afficherFormulaire("Vous avez déjà une demande en cours de traitement.");
            return;
        }

        $succes = $this->modele->creerDemande($idUser, $nom, $description);

        if ($succes) {
            $this->vue->afficherSucces();
        } else {
            $this->vue->afficherFormulaire("Une erreur technique est survenue. Veuillez réessayer.");
        }
    }
}
?>