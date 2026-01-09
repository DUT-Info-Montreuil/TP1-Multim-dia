<?php
require_once 'vue_gestionnaire.php';
require_once 'modele_gestionnaire.php';
require_once __DIR__ . '/../../../csrf.php';

class ContGestionnaire {
    private $vue;
    private $modele;
    private $csrf;
    private $types_produits;

    public function __construct() {
        $this->vue = new VueGestionnaire();
        $this->modele = new ModeleGestionnaire();
        $this->csrf = new csrf();
        $this->types_produits = ['Boisson Chaude', 'Boisson Froide', 'Nourriture Chaude', 'Nourriture Froide'];
    }

    public function exec() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?module=connexion');
            exit();
        }

        $id_user = $_SESSION['user']['id_utilisateur'];
        $id_buvette = $_GET['id_buvette'] ?? null;
        $action = $_GET['action'] ?? 'liste';
        $token = $this->csrf->getToken();

        if (isset($_SESSION['notif'])) {
            $this->vue->afficherNotification($_SESSION['notif']);
            unset($_SESSION['notif']);
        }
        if (!$id_buvette) {
            $buvettes = $this->modele->getBuvettesAutorisees($id_user);
            $this->vue->afficherSelectionBuvette($buvettes);
            return;
        }

        switch($action) {
            case 'gerer_adhesions':
                $demandes = $this->modele->getDemandesEnAttente($id_buvette);
                $membres = $this->modele->getMembresAcceptes($id_buvette);
                $this->vue->afficherGestionAdhesions($id_buvette, $membres, $demandes, $token);
                break;

            case 'accepter_demande':
                $id_target = $_GET['id_utilisateur'] ?? null;
                if ($id_target && $id_buvette) {
                    $this->modele->accepterDemande($id_target, $id_buvette);
                }
                header("Location: index.php?module=gestionnaire&action=gerer_adhesions&id_buvette=$id_buvette");
                exit();

            case 'refuser_demande':
                $id_target = $_GET['id_utilisateur'] ?? null;
                if ($id_target && $id_buvette) {
                    $this->modele->supprimerDemandeOuMembre($id_target, $id_buvette, true);
                }
                header("Location: index.php?module=gestionnaire&action=gerer_adhesions&id_buvette=$id_buvette");
                exit();

            case 'supprimer_membre':
                $id_target = $_GET['id_utilisateur'] ?? null;
                if ($id_target && $id_buvette) {
                    $this->modele->supprimerDemandeOuMembre($id_target, $id_buvette, false);
                }
                header("Location: index.php?module=gestionnaire&action=gerer_adhesions&id_buvette=$id_buvette");
                exit();
            case 'form_nouveau':
                $this->vue->afficherFormulaireNouveauProduit($id_buvette, $token, $this->types_produits);
                break;

            case 'valider_nouveau':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) { die("CSRF Error"); }

                $nom_image = "default.jpg";
                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
                    $extension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                    $nom_image = uniqid('prod_') . "." . $extension;
                    move_uploaded_file($_FILES['image_file']['tmp_name'], 'public/img/' . $nom_image);
                }

                if ($this->modele->creerEtAjouterProduit($id_buvette, $_POST['nom'], $_POST['prix'], $_POST['description'], $nom_image, $_POST['type_produit'])) {
                    $_SESSION['notif'] = "Produit créé avec succès !";
                }
                header("Location: index.php?module=gestionnaire&id_buvette=$id_buvette");
                exit();

            case 'details':
                $produit = $this->modele->getDetailsProduit($_GET['id']);
                $this->vue->afficherDetailsArticle($produit, $id_buvette, $token, $this->types_produits);
                break;

            case 'modifier_article':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    die("CSRF Error");
                }
                $id_produit = $_GET['id'];
                $id_buvette = $_GET['id_buvette'];

                $success = $this->modele->modifierProduitEtStock(
                    $id_produit,
                    $id_buvette,
                    $_POST['prix_produit'],
                    $_POST['description'],
                    $_POST['quantite'],
                    $_POST['type_produit']
                );

                if ($success) {
                    $_SESSION['notif'] = "Modifications enregistrées !";
                }

                header("Location: index.php?module=gestionnaire&action=details&id=$id_produit&id_buvette=$id_buvette");
                exit();

            case 'liste':
            default:
                $filtrerAlertes = isset($_GET['alerte']);
                $produits = $this->modele->getStocksParBuvette($id_buvette, $filtrerAlertes);
                $stats = $this->modele->getStatsBuvette($id_buvette);
                $this->vue->afficherGrilleGlobale($produits, $id_buvette, $stats);
                break;
        }
    }
}