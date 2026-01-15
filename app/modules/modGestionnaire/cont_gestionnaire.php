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
            // ==================== GESTION STOCK ====================
            case 'liste':
            default:
                $filtrerAlertes = isset($_GET['alerte']);
                $produits = $this->modele->getStocksParBuvette($id_buvette, $filtrerAlertes);
                $stats = $this->modele->getStatsBuvette($id_buvette);
                $this->vue->afficherGrilleGlobale($produits, $id_buvette, $stats);
                break;

            case 'details':
                $produit = $this->modele->getDetailsProduit($_GET['id']);
                $this->vue->afficherDetailsArticle($produit, $id_buvette, $token, $this->types_produits);
                break;

            case 'form_nouveau':
                $this->vue->afficherFormulaireNouveauProduit($id_buvette, $token, $this->types_produits);
                break;

            case 'valider_nouveau':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    die("CSRF Error");
                }

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

            case 'modifier_article':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    die("CSRF Error");
                }
                $id_produit = $_GET['id'];

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

            // ==================== GESTION ADHÉSIONS ====================
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

            // ==================== FOURNISSEURS ====================
            case 'fournisseurs':
                $fournisseurs = $this->modele->getAllFournisseurs();
                $this->vue->afficherListeFournisseurs($fournisseurs, $id_buvette, $token);
                break;

            case 'details_fournisseur':
                $id_fournisseur = $_GET['id_fournisseur'] ?? null;
                if ($id_fournisseur) {
                    $fournisseur = $this->modele->getFournisseurById($id_fournisseur);
                    $produits = $this->modele->getProduitsParFournisseur($id_fournisseur);
                    $this->vue->afficherDetailsFournisseur($fournisseur, $produits, $id_buvette);
                }
                break;

            case 'form_nouveau_fournisseur':
                $this->vue->afficherFormulaireNouveauFournisseur($id_buvette, $token);
                break;

            case 'creer_fournisseur':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    die("CSRF Error");
                }

                $success = $this->modele->creerFournisseur(
                    $_POST['nom_fournisseur'],
                    $_POST['email'],
                    $_POST['telephone'],
                    $_POST['adresse'],
                    $_POST['siret'],
                    $_POST['delai_livraison']
                );

                if ($success) {
                    $_SESSION['notif'] = "Fournisseur créé avec succès !";
                }
                header("Location: index.php?module=gestionnaire&action=fournisseurs&id_buvette=$id_buvette");
                exit();

            // ==================== COMMANDES FOURNISSEURS ====================
            case 'commander':
                $id_fournisseur = $_GET['id_fournisseur'] ?? null;
                if ($id_fournisseur) {
                    $fournisseur = $this->modele->getFournisseurById($id_fournisseur);
                    $produits = $this->modele->getProduitsParFournisseur($id_fournisseur);
                    $this->vue->afficherFormulaireCommande($fournisseur, $produits, $id_buvette, $token);
                }
                break;

            case 'valider_commande':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    die("CSRF Error");
                }

                $id_fournisseur = $_POST['id_fournisseur'];
                $notes = $_POST['notes'] ?? '';

                // Construction des lignes de commande
                $lignes = [];
                foreach ($_POST['produits'] as $id_produit => $data) {
                    if (isset($data['commander']) && $data['quantite'] > 0) {
                        $lignes[] = [
                            'id_produit' => $id_produit,
                            'quantite' => $data['quantite'],
                            'prix_unitaire' => $data['prix_achat']
                        ];
                    }
                }

                if (!empty($lignes)) {
                    try {
                        $this->modele->creerCommandeFournisseur($id_fournisseur, $id_buvette, $id_user, $lignes, $notes);
                        $_SESSION['notif'] = "Commande passée avec succès !";
                    } catch (Exception $e) {
                        $_SESSION['notif'] = "Erreur : " . $e->getMessage();
                    }
                } else {
                    $_SESSION['notif'] = "Aucun produit sélectionné !";
                }

                header("Location: index.php?module=gestionnaire&action=commandes_fournisseurs&id_buvette=$id_buvette");
                exit();

            case 'commandes_fournisseurs':
                $statut_filtre = $_GET['statut'] ?? null;
                $commandes = $this->modele->getCommandesFournisseur($id_buvette, $statut_filtre);
                $this->vue->afficherListeCommandesFournisseurs($commandes, $id_buvette, $statut_filtre);
                break;

            case 'details_commande_fournisseur':
                $id_commande = $_GET['id_commande'] ?? null;
                if ($id_commande) {
                    $commande = $this->modele->getDetailsCommandeFournisseur($id_commande);
                    $lignes = $this->modele->getLignesCommandeFournisseur($id_commande);
                    $this->vue->afficherDetailsCommandeFournisseur($commande, $lignes, $id_buvette, $token);
                }
                break;

            case 'valider_livraison':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    die("CSRF Error");
                }

                $id_commande = $_POST['id_commande'];
                try {
                    $this->modele->validerLivraisonCommande($id_commande, $id_buvette);
                    $_SESSION['notif'] = "Livraison validée ! Les stocks ont été mis à jour.";
                } catch (Exception $e) {
                    $_SESSION['notif'] = "Erreur : " . $e->getMessage();
                }

                header("Location: index.php?module=gestionnaire&action=details_commande_fournisseur&id_commande=$id_commande&id_buvette=$id_buvette");
                exit();

            case 'changer_statut_commande':
                $id_commande = $_GET['id_commande'];
                $nouveau_statut = $_GET['statut'];

                if ($this->modele->changerStatutCommande($id_commande, $nouveau_statut)) {
                    $_SESSION['notif'] = "Statut modifié !";
                }

                header("Location: index.php?module=gestionnaire&action=details_commande_fournisseur&id_commande=$id_commande&id_buvette=$id_buvette");
                exit();

            // ==================== TRÉSORERIE ====================
            case 'tresorerie':
                $tresorerie = $this->modele->getTresorerie($id_buvette);
                $mouvements = $this->modele->getMouvementsTresorerie($id_buvette);
                $stats = $this->modele->getStatsTresorerie($id_buvette);
                $this->vue->afficherTresorerie($tresorerie, $mouvements, $stats, $id_buvette, $token);
                break;

            case 'ajouter_mouvement':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    die("CSRF Error");
                }

                try {
                    $this->modele->ajouterMouvementTresorerie(
                        $id_buvette,
                        $_POST['type_mouvement'],
                        $_POST['montant'],
                        $_POST['categorie'],
                        $_POST['description'],
                        $id_user
                    );
                    $_SESSION['notif'] = "Mouvement enregistré !";
                } catch (Exception $e) {
                    $_SESSION['notif'] = "Erreur : " . $e->getMessage();
                }

                header("Location: index.php?module=gestionnaire&action=tresorerie&id_buvette=$id_buvette");
                exit();
        }
    }
}