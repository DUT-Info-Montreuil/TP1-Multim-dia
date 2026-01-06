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
        $action = $_GET['action'] ?? 'liste';
        $token = $this->csrf->getToken();

        switch ($action) {
            case 'details':
                $id = $_GET['id'] ?? null;
                $produit = $this->modele->getProduit($id);
                $this->vue->afficherDetailsArticle($produit, $token);
                break;

            case 'liste':
            default:
                $produits = $this->modele->getListeProduits();
                $this->vue->afficherGrilleGlobale($produits, $token);
                break;
        }
    }
}