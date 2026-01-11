<?php
require_once 'vue_super_admin.php';
require_once 'modele_super_admin.php';
require_once __DIR__ . '/../../../csrf.php';

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
            case 'gestion_gestionnaires':
                $this->gestionGestionnaires();
                break;
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
            case 'retirer_gestionnaire':
                $this->retirerGestionnaire();
                break;
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
                    $buvette = $this->modele->getBuvetteById($_POST['id_buvette']);
                    $this->modele->ajouterJournalActivite(
                        'Modification buvette',
                        'ID: ' . $_POST['id_buvette'] .' ' . $buvette['nom'],
                        'Nom modifié en: ' . $_POST['nom']
                    );
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

        $token = $this->csrf->getToken();
        $this->vue->afficherGestionBuvettes($buvettes, $token, $message);
    }


    private function gestionGestionnaires() {
        $token = $this->csrf->getToken();

        $gestionnaires = $this->modele->getGestionnaires();
        $utilisateursDisponibles = $this->modele->getUtilisateursSansRole();
        $buvettesDisponibles = $this->modele->getBuvettesSansGestionnaire();

        $message = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                die("Erreur de sécurité CSRF");
            }

            switch ($_POST['action']) {
                case 'attribuer':
                    if (isset($_POST['id_utilisateur'], $_POST['id_buvette'])) {
                        $utilisateur = $this->modele->getUtilisateurById($_POST['id_utilisateur']);

                        $result = $this->modele->attribuerRoleGestionnaire(
                            $_POST['id_utilisateur'],
                            $_POST['id_buvette']
                        );
                        if ($result) {
                            $buvette = $this->modele->getBuvetteById($_POST['id_buvette']);

                            $this->modele->ajouterJournalActivite(
                                'Attribution gestionnaire',
                                $utilisateur['email'],
                                'ID: ' . $_POST['id_buvette'] . ' Nom:' . $buvette['nom']
                            );
                            $message = "Gestionnaire attribué avec succès";
                            $gestionnaires = $this->modele->getGestionnaires();
                            $utilisateursDisponibles = $this->modele->getUtilisateursSansRole();
                            $buvettesDisponibles = $this->modele->getBuvettesSansGestionnaire();
                        } else {
                            $message = "Erreur : Cette buvette a déjà un gestionnaire ou l'utilisateur est déjà gestionnaire d'une autre buvette";
                        }
                    }
                    break;

//                case 'modifier':
//                    if (isset($_POST['id_utilisateur'], $_POST['id_buvette'])) {
//                        $utilisateur = $this->modele->getUtilisateurById($_POST['id_utilisateur']);
//
//                        $result = $this->modele->modifierAffectationGestionnaire(
//                            $_POST['id_utilisateur'],
//                            $_POST['id_buvette']
//                        );
//                        if ($result) {
//                            $buvette = $this->modele->getBuvetteById($_POST['id_buvette']);
//                            $this->modele->ajouterJournalActivite(
//                                'Modification affectation',
//                                $utilisateur['email'],
//                                'Nouvelle buvette ID: ' . $_POST['id_buvette'] . ' Nom: ' . $buvette['nom']
//                            );
//                            $message = "Affectation modifiée avec succès";
//                            $gestionnaires = $this->modele->getGestionnaires();
//                            $buvettesDisponibles = $this->modele->getBuvettesSansGestionnaire();
//                        } else {
//                            $message = "Erreur : Cette buvette a déjà un gestionnaire";
//                        }
//                    }
            }
        }

        if (isset($_GET['message'])) {
            switch ($_GET['message']) {
                case 'retirer':
                    $message = "Rôle de gestionnaire retiré avec succès";
                    break;
                case 'error':
                    $message = "Erreur lors de l'opération";
                    break;
            }
        }

        $this->vue->afficherGestionGestionnaires($gestionnaires, $utilisateursDisponibles, $buvettesDisponibles, $token, $message);
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

    private function retirerGestionnaire() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_utilisateur'])) {
            if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                die("Erreur de sécurité CSRF");
            }

            $id_utilisateur = $_POST['id_utilisateur'];

            $utilisateur = $this->modele->getUtilisateurById($id_utilisateur);

            $result = $this->modele->retirerRoleGestionnaire($id_utilisateur);

            if ($result) {
                if ($utilisateur) {
                    $this->modele->ajouterJournalActivite(
                        'Retrait rôle gestionnaire',
                        $utilisateur['email'],
                        'Rôle retiré'
                    );
                }

                header('Location: index.php?module=superadmin&action=gestion_gestionnaires&message=retired');
                exit();
            } else {
                header('Location: index.php?module=superadmin&action=gestion_gestionnaires&message=error');
                exit();
            }
        } else {
            header('Location: index.php?module=superadmin&action=gestion_gestionnaires');
            exit();
        }
    }

    private function journalActivite() {
        $activites = $this->modele->getJournalActivite();
        $this->vue->afficherJournalActivite($activites);
    }
}
?>