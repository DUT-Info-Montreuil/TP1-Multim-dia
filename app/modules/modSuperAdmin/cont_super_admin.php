<?php
require_once 'vue_super_admin.php';
require_once 'modele_super_admin.php';
require_once __DIR__ . '/../../../csrf.php'; // Ajout de cette ligne

class ContSuperAdmin {
    private $vue;
    private $modele;

    private $csrf;
    public function __construct() {
        $this->vue = new VueSuperAdmin();
        $this->modele = new ModeleSuperAdmin();
        $this->csrf = new csrf();
    }

    public function exec() {
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
            case 'supprimer_buvette':
                $this->supprimerBuvette();
                break;
//            case 'attribuer_gestionnaire':
//                $this->attribuerGestionnaire();
//                break;
//            case 'modifier_gestionnaire':
//                $this->modifierGestionnaire();
//                break;
//            case 'retirer_gestionnaire':
//                $this->retirerGestionnaire();
//                break;
            case 'journal_activite':
                $this->journalActivite();
                break;
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
            //  CSRF
            if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                die("Erreur de sécurité CSRF");
            }

            if ($_POST['action'] === 'modifier' && isset($_POST['id_buvette'])) {
                $result = $this->modele->modifierBuvette(
                    $_POST['id_buvette'],
                    $_POST['nom'],
                    $_POST['description']
                );
                if ($result) {
                    if (method_exists($this->modele, 'ajouterJournalActivite')) {
                        $this->modele->ajouterJournalActivite(
                            'Modification buvette',
                            'Buvette ID: ' . $_POST['id_buvette'],
                            'Nom modifié en: ' . $_POST['nom']
                        );
                    }
                    $message = "Buvette modifiée avec succès";
                    $buvettes = $this->modele->getBuvettes();
                } else {
                    $message = "Erreur lors de la modification";
                }
            }
        }

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

        // passer CSRF a la vue
        $token = $this->csrf->getToken();
        $this->vue->afficherGestionBuvettes($buvettes, $token, $message);
    }


    private function supprimerBuvette() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_buvette'])) {
            if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                die("Erreur de sécurité CSRF");
            }

            $id_buvette = $_POST['id_buvette'];
            $result = $this->modele->archiverBuvette($id_buvette);

            if ($result) {
                header('Location: index.php?module=superadmin&action=gestion_buvettes&message=archived');
                exit();
            } else {
                header('Location: index.php?module=superadmin&action=gestion_buvettes&message=error');
                exit();
            }
        } else {
            header('Location: index.php?module=superadmin&action=gestion_buvettes');
            exit();
        }
    }


    private function journalActivite() {
        $activites = $this->modele->getJournalActivite();
        $this->vue->afficherJournalActivite($activites);
    }
}
?>