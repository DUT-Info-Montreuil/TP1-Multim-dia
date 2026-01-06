<?php

require_once 'vue_menu.php';
require_once 'modele_menu.php';

class ContMenu
{
    private $vue;
    private $modele;

    public function __construct()
    {
        $this->vue = new VueMenu();
        $this->modele = new ModeleMenu();
    }

    public function exec()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : 'afficher';

        switch ($action) {
            case 'afficher':
                $this->afficherMenu();
                break;

            default:
                $this->afficherMenu();
                break;
        }
    }

    public function afficherMenu()
    {
        if (isset($_GET['id_buvette'])) {
            $_SESSION['id_buvette'] = (int)$_GET['id_buvette'];
        }

        if (isset($_SESSION['id_buvette'])) {
            $idBuvette = $_SESSION['id_buvette'];
            $listeProduits = $this->modele->getProduitsParBuvette($idBuvette);
            $this->vue->afficherProduits($listeProduits);
        } else {
            header('Location: index.php?module=buvettes');
            exit();
        }
    }
}



