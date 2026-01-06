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
        $token = $this->csrf->getToken();

        if (!$id_buvette) {
            $buvettes = $this->modele->getBuvettesAutorisees($id_user);
            $this->vue->afficherSelectionBuvette($buvettes);
        } else {
            $produits = $this->modele->getStocksParBuvette($id_buvette);
            $this->vue->afficherGrilleGlobale($produits, $token);
        }
    }
}