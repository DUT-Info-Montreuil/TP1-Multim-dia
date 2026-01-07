<?php
require_once 'vue_super_admin.php';
require_once 'modele_super_admin.php';

class ContSuperAdmin {
    private $vue;
    private $modele;

    public function __construct() {
        $this->vue = new VueSuperAdmin();
        $this->modele = new ModeleSuperAdmin();
    }

    public function exec() {
        // Initialiser les rôles de base si nécessaire
        $action = isset($_GET['action']) ? $_GET['action'] : 'afficher_tableau_bord';

        switch($action) {
            case 'afficher_tableau_bord':
                $this->afficherTableauBord();
                break;
            case 'gestion_buvettes':
                $this->gestionBuvettes();
                break;
//            case 'gestion_gestionnaires':
//                $this->gestionGestionnaires();
//                break;
//            case 'modifier_buvette':
//                $this->modifierBuvette();
//                break;
//            case 'supprimer_buvette':
//                $this->supprimerBuvette();
//                break;
//            case 'attribuer_gestionnaire':
//                $this->attribuerGestionnaire();
//                break;
//            case 'modifier_gestionnaire':
//                $this->modifierGestionnaire();
//                break;
//            case 'retirer_gestionnaire':
//                $this->retirerGestionnaire();
//                break;
//            case 'journal_activite':
//                $this->journalActivite();
//                break;
            default:
                $this->afficherTableauBord();
        }
    }


    private function afficherTableauBord() {
        $stats = $this->modele->getStatistiques();
        $this->vue->afficherTableauBord($stats);
    }

    private function gestionBuvettes() {
        $buvettes = $this->modele->getBuvettes();
        $message = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'modifier' && isset($_POST['id_buvette'])) {
                $result = $this->modele->modifierBuvette(
                    $_POST['id_buvette'],
                    $_POST['nom'],
                    $_POST['description']
                );
                if ($result) {
                    $this->modele->ajouterJournalActivite(
                        'Modification buvette',
                        'Buvette ID: ' . $_POST['id_buvette'],
                        'Nom: ' . $_POST['nom']
                    );
                    $message = "Buvette modifiée avec succès";
                    $buvettes = $this->modele->getBuvettes(); // Recharger les données
                } else {
                    $message = "Erreur lors de la modification";
                }
            }
        }

        // Gestion des messages GET
        if (isset($_GET['message'])) {
            switch($_GET['message']) {
                case 'archived':
                    $message = "Buvette archivée avec succès";
                    break;
                case 'error':
                    $message = "Erreur lors de l'opération";
                    break;
            }
        }

        $this->vue->afficherGestionBuvettes($buvettes, $message);
    }
}
?>