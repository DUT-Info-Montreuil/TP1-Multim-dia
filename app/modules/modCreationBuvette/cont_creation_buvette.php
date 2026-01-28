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

        if (empty($_FILES['statuts']['name']) || empty($_FILES['pv_ag']['name']) || empty($_FILES['cnid']['name'])) {
            $this->vue->afficherFormulaire("Merci de fournir les 3 documents demandés.");
            return;
        }

        if ($this->modele->aDemandeEnCours($idUser)) {
            $this->vue->afficherFormulaire("Vous avez déjà une demande en cours de traitement.");
            return;
        }

        $dossierUpload = 'public/uploads/justificatifs/';
        if (!is_dir($dossierUpload)) {
            mkdir($dossierUpload, 0755, true);
        }
        $fichierStatuts = $this->uploaderFichier($_FILES['statuts'], $dossierUpload, ['pdf']);
        if (!$fichierStatuts) {
            $this->vue->afficherFormulaire("Erreur sur le fichier Statuts (Format PDF requis).");
            return;
        }

        $fichierPV = $this->uploaderFichier($_FILES['pv_ag'], $dossierUpload, ['pdf']);
        if (!$fichierPV) {
            $this->vue->afficherFormulaire("Erreur sur le fichier PV AG (Format PDF requis).");
            return;
        }

        $fichierCNI = $this->uploaderFichier($_FILES['cnid'], $dossierUpload, ['pdf', 'jpg', 'jpeg', 'png']);
        if (!$fichierCNI) {
            $this->vue->afficherFormulaire("Erreur sur la pièce d'identité (PDF ou Image requis).");
            return;
        }

        $succes = $this->modele->creerDemande($idUser, $nom, $description, $fichierStatuts, $fichierPV, $fichierCNI);


        if ($succes) {
            $this->vue->afficherSucces();
        } else {
            $this->vue->afficherFormulaire("Une erreur technique est survenue. Veuillez réessayer.");
        }
    }
    private function uploaderFichier($file, $dossier, $extensions)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensions)) {
            return false;
        }

        $nouveauNom = uniqid() . '_' . basename($file['name']);
        $cheminFinal = $dossier . $nouveauNom;

        if (move_uploaded_file($file['tmp_name'], $cheminFinal)) {
            return $nouveauNom;
        }

        return false;
    }
}
?>