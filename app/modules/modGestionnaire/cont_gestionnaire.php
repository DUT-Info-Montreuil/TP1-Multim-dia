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

        // AUDIT-006 : l'utilisateur doit gérer la buvette demandée
        $idsAutorises = array_map('intval', array_column(
            $this->modele->getBuvettesAutorisees($id_user), 'id_buvette'
        ));
        if (!ctype_digit((string)$id_buvette) || !in_array((int)$id_buvette, $idsAutorises, true)) {
            $_SESSION['notif'] = "Accès refusé : vous ne gérez pas cette buvette.";
            header('Location: index.php?module=gestionnaire');
            exit();
        }

        switch($action) {
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
                    move_uploaded_file($_FILES['image_file']['tmp_name'], 'public/img/produits/' . $nom_image);
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

                $success = $this->modele->modifierProduitSansStock(
                    $id_produit,
                    $_POST['prix_produit'],
                    $_POST['description'],
                    $_POST['type_produit']
                );

                if ($success) {
                    $_SESSION['notif'] = "Modifications enregistrées !";
                }

                header("Location: index.php?module=gestionnaire&action=details&id=$id_produit&id_buvette=$id_buvette");
                exit();

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


            case 'inventaire':
                $stocks = $this->modele->getStocksDetailles($id_buvette);
                $historique = $this->modele->getHistoriqueChangementsStock($id_buvette);
                $stats = $this->modele->getStatsInventaire($id_buvette);
                $this->vue->afficherBilanInventaire($stocks, $historique, $stats, $id_buvette, $token);
                break;

            case 'ajuster_stock':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    die("CSRF Error");
                }

                $id_produit = $_POST['id_produit'];
                $type = $_POST['type_changement'];
                $quantite = (int)$_POST['quantite'];
                $commentaire = $_POST['commentaire'];

                try {
                    $this->modele->ajusterStock(
                        $id_buvette,
                        $id_produit,
                        $type,
                        $quantite,
                        $commentaire,
                        $id_user
                    );
                    $_SESSION['notif'] = "Stock ajusté avec succès !";
                } catch (Exception $e) {
                    $_SESSION['notif'] = "Erreur : " . $e->getMessage();
                }

                header("Location: index.php?module=gestionnaire&action=inventaire&id_buvette=$id_buvette");
                exit();

            case 'fidelite':
                $clients = $this->modele->getAllClientsAvecPoints($id_buvette);
                $this->vue->afficherGestionFidelite($clients, $id_buvette);
                break;

            case 'details_fidelite':
                $id_client = $_GET['id_client'] ?? null;
                if ($id_client) {
                    $points = $this->modele->getPointsFidelite($id_client, $id_buvette);
                    $historique = $this->modele->getHistoriquePoints($id_client, $id_buvette);
                    $client = $this->modele->getUtilisateurById($id_client);

                    $this->vue->afficherDetailsFidelite($client, $points, $historique, $id_buvette, $token);
                }
                break;

            case 'ajuster_points':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    die("CSRF Error");
                }

                $id_client = $_POST['id_client'];
                $points = (int)$_POST['points'];
                $description = $_POST['description'];

                try {
                    if ($points > 0) {
                        $this->modele->ajouterPoints($id_client, $id_buvette, $points, $description);
                    } else {
                        $this->modele->utiliserPoints($id_client, $id_buvette, abs($points), $description);
                    }
                    $_SESSION['notif'] = "Points ajustés avec succès !";
                } catch (Exception $e) {
                    $_SESSION['notif'] = "Erreur : " . $e->getMessage();
                }

                header("Location: index.php?module=gestionnaire&action=details_fidelite&id_client=$id_client&id_buvette=$id_buvette");
                exit();

            case 'profil':
                $buvette = $this->modele->getDetailsBuvette($id_buvette);
                $this->vue->afficherProfilBuvette($buvette, $token);
                break;

            case 'modifier_profil':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    die("CSRF Error");
                }

                $nom = trim($_POST['nom'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $nom_image = null;

                if (empty($nom)) {
                    $_SESSION['notif'] = "Le nom est obligatoire";
                    header("Location: index.php?module=gestionnaire&action=profil&id_buvette=$id_buvette");
                    exit();
                }

                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                    $extensions_autorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $extension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));

                    if (!in_array($extension, $extensions_autorisees)) {
                        $_SESSION['notif'] = "Format d'image non autorisé";
                        header("Location: index.php?module=gestionnaire&action=profil&id_buvette=$id_buvette");
                        exit();
                    }

                    $nom_image = 'buvette_' . $id_buvette . '_' . time() . '.' . $extension;
                    $chemin_destination = __DIR__ . '/../../../public/img/buvettes/' . $nom_image;

                    if (!file_exists(dirname($chemin_destination))) {
                        mkdir(dirname($chemin_destination), 0755, true);
                    }

                    if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $chemin_destination)) {
                        $_SESSION['notif'] = "Erreur lors de l'upload de l'image";
                        header("Location: index.php?module=gestionnaire&action=profil&id_buvette=$id_buvette");
                        exit();
                    }
                }

                if ($this->modele->modifierProfilBuvette($id_buvette, $nom, $description, $nom_image)) {
                    $_SESSION['notif'] = "Profil de la buvette modifié avec succès";
                } else {
                    $_SESSION['notif'] = "Erreur lors de la modification";
                }

                header("Location: index.php?module=gestionnaire&action=profil&id_buvette=$id_buvette");
                exit();

            case 'toggle_ouverture':
                if (!isset($_POST['csrf_token']) || !$this->csrf->validate($_POST['csrf_token'])) {
                    die("CSRF Error");
                }

                if ($this->modele->toggleOuvertureBuvette($id_buvette)) {
                    $_SESSION['notif'] = "Statut d'ouverture modifié avec succès";
                } else {
                    $_SESSION['notif'] = "Erreur lors du changement de statut";
                }

                header("Location: index.php?module=gestionnaire&id_buvette=$id_buvette");
                exit();
        }
    }
}