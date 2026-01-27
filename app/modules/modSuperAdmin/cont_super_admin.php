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
            case 'creer_buvette':
                $this->creerBuvette();
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
            case 'gestion_demandes_creation':
                $this->gestionDemandesCreation();
                break;
            case 'valider_demande_creation':
                $this->validerDemandeCreation();
                break;
            case 'rejeter_demande_creation':
                $this->rejeterDemandeCreation();
                break;
            case 'journal_activite':
                $this->journalActivite();
                break;
            case 'attribuer_gestionnaire_buvette':
                $this->attribuerGestionnaireBuvette();
                break;
            default:
                $this->afficherTableauBord();
        }
    }

    private function attribuerGestionnaireBuvette() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_buvette'], $_POST['id_utilisateur'])) {
            // CSRF validation
            if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                die("Erreur de sécurité CSRF");
            }

            $id_buvette = $_POST['id_buvette'];
            $id_utilisateur = $_POST['id_utilisateur'];

            $result = $this->modele->attribuerRoleGestionnaire($id_utilisateur, $id_buvette);

            if ($result) {
                $utilisateur = $this->modele->getUtilisateurById($id_utilisateur);
                $buvette = $this->modele->getBuvetteById($id_buvette);

                $this->modele->ajouterJournalActivite(
                    'Attribution gestionnaire depuis gestion buvette',
                    'Buvette ID: ' . $id_buvette . ' - ' . $buvette['nom'],
                    'Utilisateur: ' . $utilisateur['email']
                );

                header('Location: index.php?module=superadmin&action=gestion_buvettes&message=gestionnaire_attribue');
                exit();
            } else {
                header('Location: index.php?module=superadmin&action=gestion_buvettes&message=error_attribution');
                exit();
            }
        } else {
            header('Location: index.php?module=superadmin&action=gestion_buvettes');
            exit();
        }
    }
    private function creerBuvette() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'creer') {
            // CSRF validation
            if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                die("Erreur de sécurité CSRF");
            }

            if (isset($_POST['nom'], $_POST['description'])) {
                $result = $this->modele->creerBuvette(
                    $_POST['nom'],
                    $_POST['description']
                );

                if ($result) {
                    $this->modele->ajouterJournalActivite(
                        'Création buvette',
                        'Nom: ' . $_POST['nom'],
                        'Description: ' . ($_POST['description'] ?? 'Non spécifiée')
                    );
                    header('Location: index.php?module=superadmin&action=gestion_buvettes&message=created');
                    exit();
                } else {
                    header('Location: index.php?module=superadmin&action=gestion_buvettes&message=error_creation');
                    exit();
                }
            }
        }
    }

    private function gestionDemandesCreation() {
        $demandes = $this->modele->getDemandesCreation();
        $this->vue->afficherGestionDemandesCreation($demandes);
    }

    private function validerDemandeCreation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_demande'])) {
            // CSRF validation
            if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                die("Erreur de sécurité CSRF");
            }

            $id_demande = $_POST['id_demande'];
            $result = $this->modele->validerDemandeCreation($id_demande);

            if ($result) {
                $this->modele->ajouterJournalActivite(
                    'Validation demande création',
                    'Demande ID: ' . $id_demande,
                    'Demande de création validée et buvette créée'
                );
                header('Location: index.php?module=superadmin&action=gestion_demandes_creation&message=validee');
                exit();
            } else {
                header('Location: index.php?module=superadmin&action=gestion_demandes_creation&message=error');
                exit();
            }
        } else {
            header('Location: index.php?module=superadmin&action=gestion_demandes_creation');
            exit();
        }
    }

    private function rejeterDemandeCreation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_demande'], $_POST['raison_refus'])) {
            // CSRF validation
            if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                die("Erreur de sécurité CSRF");
            }

            $id_demande = $_POST['id_demande'];
            $raison_refus = $_POST['raison_refus'];

            $result = $this->modele->rejeterDemandeCreation($id_demande, $raison_refus);

            if ($result) {
                $this->modele->ajouterJournalActivite(
                    'Rejet demande création',
                    'Demande ID: ' . $id_demande,
                    'Raison: ' . $raison_refus
                );
                header('Location: index.php?module=superadmin&action=gestion_demandes_creation&message=rejetee');
                exit();
            } else {
                header('Location: index.php?module=superadmin&action=gestion_demandes_creation&message=error');
                exit();
            }
        } else {
            header('Location: index.php?module=superadmin&action=gestion_demandes_creation');
            exit();
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
            // CSRF
            if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                die("Erreur de sécurité CSRF");
            }

            if ($_POST['action'] === 'modifier' && isset($_POST['id_buvette'])) {
                $est_ouverte = isset($_POST['est_ouverte']) ? 1 : 0;

                $ancienneBuvette = $this->modele->getBuvetteById($_POST['id_buvette']);

                if (!$ancienneBuvette) {
                    $message = "Erreur : Buvette introuvable";
                } else {
                    $result = $this->modele->modifierBuvette(
                        $_POST['id_buvette'],
                        $_POST['nom'],
                        $_POST['description'],
                        $est_ouverte
                    );

                    if ($result) {
                        $buvette = $this->modele->getBuvetteById($_POST['id_buvette']);

                        if ($buvette) {
                            $details = [];

                            if ($ancienneBuvette['nom'] !== $_POST['nom']) {
                                $details[] = "Nom: " . $ancienneBuvette['nom'] . " => " . $_POST['nom'];
                            }

                            if ($ancienneBuvette['description'] !== $_POST['description']) {
                                $details[] = "Description modifiée";
                            }

                            if ($ancienneBuvette['est_ouverte'] != $est_ouverte) {
                                $statut_ancien = $ancienneBuvette['est_ouverte'] ? 'Ouverte' : 'Fermée';
                                $statut_nouveau = $est_ouverte ? 'Ouverte' : 'Fermée';
                                $details[] = "Statut: " . $statut_ancien . " => " . $statut_nouveau;
                            }

                            if (empty($details)) {
                                $details[] = "Aucun changement détecté (sauvegarde)";
                            }

                            $this->modele->ajouterJournalActivite(
                                'Modification buvette',
                                'Buvette ID: ' . $_POST['id_buvette'] . ' - ' . $buvette['nom'],
                                implode(' | ', $details)
                            );
                            $message = "Buvette modifiée avec succès";
                        } else {
                            $message = "Erreur : Impossible de récupérer les données mises à jour";
                        }

                        $buvettes = $this->modele->getBuvettes();
                    } else {
                        $message = "Erreur lors de la modification";
                    }
                }
            }
        }

        if (isset($_GET['message'])) {
            switch($_GET['message']) {
                case 'created':
                    $message = "Buvette créée avec succès";
                    break;
                case 'archived':
                    $message = "Buvette archivée avec succès";
                    break;
                case 'error':
                    $message = "Erreur lors de l'opération";
                    break;
                case 'error_creation':
                    $message = "Erreur lors de la création de la buvette";
                    break;
                case 'gestionnaire_attribue':
                    $message = "Gestionnaire attribué avec succès";
                    break;
                case 'error_attribution':
                    $message = "Erreur : Cette buvette a déjà un gestionnaire ou l'utilisateur est déjà gestionnaire d'une autre buvette";
                    break;
                case 'statut_updated':
                    $message = "Statut de la buvette modifié avec succès";
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
                                'ID: ' . $_POST['id_buvette'] . ' Nom: ' . $buvette['nom']
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
            $raison_archivage = isset($_POST['raison_archivage']) ? $_POST['raison_archivage'] : null;

            $result = $this->modele->archiverBuvette($id_buvette, $raison_archivage);

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
        $filtres = [];

        if (isset($_GET['filtre_action']) && !empty($_GET['filtre_action'])) {
            $filtres['action'] = $_GET['filtre_action'];
        }

        if (isset($_GET['filtre_administrateur']) && !empty($_GET['filtre_administrateur'])) {
            $filtres['administrateur'] = $_GET['filtre_administrateur'];
        }

        if (isset($_GET['filtre_date']) && !empty($_GET['filtre_date'])) {
            $filtres['date'] = $_GET['filtre_date'];
        }

        if (isset($_GET['filtre_date_debut']) && !empty($_GET['filtre_date_debut'])) {
            $filtres['date_debut'] = $_GET['filtre_date_debut'];
        }

        if (isset($_GET['filtre_date_fin']) && !empty($_GET['filtre_date_fin'])) {
            $filtres['date_fin'] = $_GET['filtre_date_fin'];
        }

        $activites = $this->modele->getJournalActivite($filtres);
        $this->vue->afficherJournalActivite($activites, $filtres);
    }
}
?>