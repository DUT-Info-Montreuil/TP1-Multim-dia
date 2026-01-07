<?php
require_once 'vue_gestionnaire.php';
require_once 'modele_gestionnaire.php';
require_once __DIR__ . '/../../../csrf.php';

class ContGestionnaire {
    private $vue;
    private $modele;
    private $csrf;

    public function __construct() {
        $this->vue = new VueGestionnaire();
        $this->modele = new ModeleGestionnaire();
        $this->csrf = new csrf();
    }

    public function exec() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?module=connexion');
            exit();
        }

        $id_user = $_SESSION['user']['id_utilisateur'];
        $id_buvette = $_GET['id_buvette'] ?? null;
        $action = $_GET['action'] ?? 'liste';

        if (!$id_buvette) {
            $buvettes = $this->modele->getBuvettesAutorisees($id_user);
            $this->vue->afficherSelectionBuvette($buvettes);
            return;
        }

        switch($action) {
            case 'form_nouveau':
                $this->vue->afficherFormulaireNouveauProduit($id_buvette);
                break;

            case 'valider_nouveau':
                $id_buvette = $_GET['id_buvette'] ?? null;
                $nom_image = "default.jpg";

                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {

                    $extension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                    $extensions_autorisees = ['jpg', 'jpeg', 'png', 'webp'];

                    if (in_array($extension, $extensions_autorisees)) {
                        $nom_image = uniqid('prod_') . "." . $extension;
                        move_uploaded_file($_FILES['image_file']['tmp_name'], 'public/img/' . $nom_image);
                    }
                }

                $this->modele->creerEtAjouterProduit($id_buvette, $_POST['nom'], $_POST['prix'], $nom_image);
                header("Location: index.php?module=gestionnaire&id_buvette=$id_buvette");
                exit();
            case 'details':
                $produit = $this->modele->getDetailsProduit($_GET['id']);
                $this->vue->afficherDetailsArticle($produit, $id_buvette);
                break;

            case 'liste':
            default:
                $produits = $this->modele->getStocksParBuvette($id_buvette);
                $this->vue->afficherGrilleGlobale($produits, $id_buvette);
                break;
        }
    }
}