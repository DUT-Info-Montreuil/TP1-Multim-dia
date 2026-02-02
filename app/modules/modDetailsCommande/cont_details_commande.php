<?php

require_once 'vue_details_commande.php';
require_once 'modele_details_commande.php';

class ContDetailsCommande
{
    private $vue;
    private $modele;

    public function __construct()
    {
        $this->vue = new VueDetailsCommande();
        $this->modele = new ModeleDetailsCommande();
    }

    public function exec()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : 'afficher';

        switch ($action) {

            case 'afficher':
            default:
                $this->afficherDetails();
                break;
        }
    }

    public function afficherDetails()
    {

        if (!isset($_SESSION['user'])) {
            header('Location: index.php?module=connexion');
            exit();
        }

        if (isset($_GET['id_commande'])) {
            $idCommande = (int)$_GET['id_commande'];
            $idUser = $_SESSION['user']['id_utilisateur'];

            $infosCommande = $this->modele->getInfosCommande($idCommande, $idUser);

            if ($infosCommande) {
                $produits = $this->modele->getProduitsCommande($idCommande);
                $infosCommande['produits'] = $produits;
                $this->vue->afficherDetails($infosCommande);
            } else {
                echo "<div class='alert alert-danger container mt-5'>Commande introuvable ou accès refusé.</div>";
            }
        } else {
            header('Location: index.php?module=compte&action=historique');
            exit();
        }
    }


}