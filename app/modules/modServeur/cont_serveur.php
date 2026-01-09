<?php
require_once 'modele_serveur.php';
require_once 'vue_serveur.php';

class ContServeur {
    private $modele;
    private $vue;

    public function __construct() {
        $this->modele = new ModeleServeur();
        $this->vue = new VueServeur();
    }

    public function exec() {
        // GESTION DU CONTEXTE BUVETTE
        // Si un ID est passé dans l'URL (via le bouton Gestion), on met à jour la session
        if (isset($_GET['id_buvette'])) {
            $_SESSION['id_buvette'] = (int)$_GET['id_buvette'];
        }

        // On récupère l'ID de la buvette active (ou 0 par défaut si rien)
        $idBuvette = isset($_SESSION['id_buvette']) ? $_SESSION['id_buvette'] : 0;

        $action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

        switch ($action) {
            // --- AJAX (Vente) ---
            case 'rechercher_user':
                if (isset($_GET['query'])) {
                    ob_clean();
                    echo json_encode($this->modele->rechercherUtilisateur($_GET['query']));
                    exit();
                }
                break;

            case 'valider_vente':
                $data = json_decode(file_get_contents('php://input'), true);
                if ($data) {
                    ob_clean();
                    // On passe bien $idBuvette pour lier la commande à cette buvette
                    $res = $this->modele->enregistrerVenteComptoir($data['user_id'], $idBuvette, $data['panier'], $data['total']);
                    echo json_encode(['success' => ($res === true), 'message' => ($res === true ? '' : $res)]);
                    exit();
                }
                break;

            // --- CHANGEMENT STATUT ---
            case 'changer_statut':
                if (isset($_POST['id_commande']) && isset($_POST['nouveau_statut'])) {
                    $this->modele->changerStatut($_POST['id_commande'], $_POST['nouveau_statut']);
                }
                header("Location: index.php?module=serveur");
                exit();
                break;

            case 'dashboard':
            default:
                if ($idBuvette == 0) {
                    echo "Erreur : Aucune buvette sélectionnée.";
                } else {
                    $reservations = $this->modele->getListeReservations($idBuvette);
                    $produits = $this->modele->getTousLesProduits();

                    foreach ($reservations as &$resa) {
                        $resa['details'] = $this->modele->getDetailsCommande($resa['id_commande']);
                    }
                    $this->vue->afficherDashboard($reservations, $produits);
                }
                break;
        }
    }
}
?>