<?php
require_once 'modele_compte.php';
require_once 'vue_compte.php';

class ContCompte
{
    private $modele;
    private $vue;

    public function __construct()
    {
        $this->modele = new ModeleCompte();
        $this->vue = new VueCompte();
    }

    public function exec()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?module=connexion&action=form_connexion');
            exit;
        }

        $action = isset($_GET['action']) ? $_GET['action'] : 'historique';

        switch ($action) {

            case 'historique':
            default:
                $idUser = $_SESSION['user']['id_utilisateur'];

                $commandes = $this->modele->getHistorique($idUser);

                foreach ($commandes as &$uneCommande) {
                    $uneCommande['liste_produits'] = $this->modele->getDetailsCommande($uneCommande['id_commande']);
                }
                unset($uneCommande);

                $this->vue->afficherHistorique($commandes);
                break;
        }
    }
}
