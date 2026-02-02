<?php
require_once 'vue_panier.php';
require_once 'modele_panier.php';

class ContPanier
{
    private $vue;
    private $modele;

    public function __construct() {
        $this->vue = new VuePanier();
        $this->modele = new ModelePanier();
    }

    public function exec() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'afficher';

        if (!isset($_SESSION['user']['id_utilisateur']) || !isset($_SESSION['id_buvette'])) {
            header('Location: index.php?module=connexion&action=form_connexion');
            exit;
        }

        $idUser = $_SESSION['user']['id_utilisateur'];
        $idBuvette = $_SESSION['id_buvette'];

        $idProduit = isset($_REQUEST['id_produit']) ? $_REQUEST['id_produit'] : null;

        switch ($action) {

            case 'ajouter':
            case 'augmenter':
                if ($idProduit) {
                    $idCommande = $this->modele->getCommandeEnCours($idUser, $idBuvette);

                    if (!$idCommande) {
                        $idCommande = $this->modele->creerCommande($idUser, $idBuvette);
                    }

                    $this->modele->ajouterArticle($idCommande, $idProduit);
                }

                if ($action == 'augmenter') {
                    header('Location: index.php?module=panier&action=afficher');
                } else {
                    $_SESSION['flash'] = "Article ajouté au panier !";
                    header('Location: index.php?module=panier&action=afficher');
                }
                exit;
                break;

            case 'diminuer':
                if ($idProduit) {
                    $idCommande = $this->modele->getCommandeEnCours($idUser, $idBuvette);
                    if ($idCommande) {
                        $this->modele->diminuerQuantite($idCommande, $idProduit);
                    }
                }
                header('Location: index.php?module=panier&action=afficher');
                exit;
                break;

            case 'supprimer':
                if ($idProduit) {
                    $idCommande = $this->modele->getCommandeEnCours($idUser, $idBuvette);
                    if ($idCommande) {
                        $this->modele->supprimerProduit($idCommande, $idProduit);
                    }
                }
                header('Location: index.php?module=panier&action=afficher');
                exit;
                break;

            case 'afficher':
                $idCommande = $this->modele->getCommandeEnCours($idUser, $idBuvette);

                if ($idCommande) {
                    $produits = $this->modele->getProduitsCommande($idCommande);
                    $total = $this->modele->getTotalCommande($idCommande);
                } else {
                    $produits = [];
                    $total = 0;
                }

                $aDesHistoriques = $this->modele->aDesCommandesHistorique($idUser);

                $this->vue->afficherPanier($produits, $total, $aDesHistoriques);
                break;
            case 'valider':
                $idCommande = $this->modele->getCommandeEnCours($idUser, $idBuvette);

                if ($idCommande) {
                    $total = $this->modele->getTotalCommande($idCommande);
                    $solde = $this->modele->getSoldeUtilisateur($idUser, $idBuvette);

                    if ($solde < $total) {
                        $_SESSION['modal_error'] = "Votre solde est insuffisant pour valider cette commande. Il vous manque " . number_format($total - $solde, 2) . " €.";
                        header('Location: index.php?module=panier&action=afficher');
                    } else {
                        $this->modele->validerCommande($idUser, $idCommande, $total);
                        $_SESSION['flash'] = "Commande envoyée pour confirmation.";
                        header('Location: index.php?module=detailsCommande&action=afficher&id_commande=' . $idCommande);
                    }
                } else {
                    header('Location: index.php?module=panier&action=afficher');
                }
                exit;
                break;
        }
    }
}
