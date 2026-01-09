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
        $action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
        $idBuvette = isset($_SESSION['id_buvette']) ? $_SESSION['id_buvette'] : 1;

        switch ($action) {
            // --- GESTION AJAX ---
            case 'rechercher_user':
                if (isset($_GET['query'])) {
                    // Désactive l'affichage du template pour renvoyer du JSON pur
                    ob_clean();
                    echo json_encode($this->modele->rechercherUtilisateur($_GET['query']));
                    exit();
                }
                break;

            case 'valider_vente':
                $data = json_decode(file_get_contents('php://input'), true);
                if ($data) {
                    ob_clean();
                    $res = $this->modele->enregistrerVenteComptoir($data['user_id'], $idBuvette, $data['panier'], $data['total']);
                    echo json_encode(['success' => ($res === true), 'message' => ($res === true ? '' : $res)]);
                    exit();
                }
                break;

            // --- GESTION STATUTS ---

            // AVANCER : Réservé -> Préparation -> Arrivé -> Parti
            case 'cycle_statut':
                if (isset($_GET['id']) && isset($_GET['actuel'])) {
                    $actuel = $_GET['actuel'];
                    $next = $actuel;

                    if ($actuel == 'Réservé' || $actuel == 'reserve') {
                        $next = 'Préparation';
                    } elseif ($actuel == 'Préparation') {
                        $next = 'Arrivé';
                    } elseif ($actuel == 'Arrivé') {
                        $next = 'Parti';
                    }

                    if ($next !== $actuel) {
                        $this->modele->changerStatut($_GET['id'], $next);
                    }
                }
                header("Location: index.php?module=serveur");
                break;

            // RECULER : Arrivé -> Préparation -> Réservé
            // INTERDIT si "Parti" ou "Annulé"
            case 'revert_statut':
                if (isset($_GET['id']) && isset($_GET['actuel'])) {
                    $actuel = $_GET['actuel'];
                    $prev = $actuel;

                    // Modification demandée : Bloquer retour pour 'Parti' et 'Annulé'
                    if ($actuel == 'Annulé' || $actuel == 'Parti') {
                        $prev = $actuel; // Ne change rien
                    } elseif ($actuel == 'Arrivé') {
                        $prev = 'Préparation';
                    } elseif ($actuel == 'Préparation') {
                        $prev = 'Réservé';
                    }

                    if ($prev !== $actuel) {
                        $this->modele->changerStatut($_GET['id'], $prev);
                    }
                }
                header("Location: index.php?module=serveur");
                break;

            case 'annuler':
                if (isset($_GET['id'])) $this->modele->annulerCommande($_GET['id']);
                header("Location: index.php?module=serveur");
                break;

            case 'dashboard':
            default:
                $reservations = $this->modele->getListeReservations($idBuvette);
                $produits = $this->modele->getTousLesProduits(); // Pour le panneau de vente

                foreach ($reservations as &$resa) {
                    $resa['details'] = $this->modele->getDetailsCommande($resa['id_commande']);
                }
                $this->vue->afficherDashboard($reservations, $produits);
                break;
        }
    }
}
?>