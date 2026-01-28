<?php
require_once 'modele_solde.php';
require_once 'vue_solde.php';

class ContSolde
{
    private $modele;
    private $vue;

    public function __construct()
    {
        $this->modele = new ModeleSolde();
        $this->vue = new VueSolde();
    }

    public function exec()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?module=connexion');
            exit;
        }
        $action = isset($_GET['action']) ? $_GET['action'] : 'afficher';
        $idUser = $_SESSION['user']['id_utilisateur'];

        switch ($action) {
            case 'validerRechargement':
                $this->traiterAjoutSolde($idUser);
                break;

            case 'afficher':
            default:
                $this->afficherPageSolde($idUser);
                break;
        }
    }


    private function afficherPageSolde($idUser)
    {
        $mesBuvettes = $this->modele->getBuvettesAdherent($idUser);
        $this->vue->afficherFormulaire($mesBuvettes);
    }

    private function traiterAjoutSolde($idUser)
    {
        if (isset($_POST['id_buvette'], $_POST['montant'])) {
            $idBuvette = (int) $_POST['id_buvette'];
            $montant = (float) $_POST['montant'];

            if ($montant > 0) {
                $this->modele->ajouterSolde($idUser, $idBuvette, $montant);
                header('Location: index.php?module=solde&message=Solde rechargé avec succès !');
                exit;
            }
        }
        $this->afficherPageSolde($idUser);
    }
}
?>