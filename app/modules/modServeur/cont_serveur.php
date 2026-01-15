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

        // Gestion ID Buvette
        if (isset($_GET['id_buvette'])) {
            $_SESSION['id_buvette'] = (int)$_GET['id_buvette'];
        }
        $idBuvette = isset($_SESSION['id_buvette']) ? $_SESSION['id_buvette'] : 0;

        switch ($action) {
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
                    $res = $this->modele->enregistrerVenteComptoir($data['user_id'], $idBuvette, $data['panier'], $data['total']);
                    echo json_encode(['success' => ($res === true), 'message' => ($res === true ? '' : $res)]);
                    exit();
                }
                break;

            // AVANCER DANS LE CYCLE
            case 'cycle_statut':
                if (isset($_GET['id']) && isset($_GET['actuel'])) {
                    $actuel = $_GET['actuel'];
                    $next = $actuel;

                    // Ajout de la gestion "Attente Validation"
                    if ($actuel == 'En attente') { $next = 'Payé'; }
                    elseif ($actuel == 'Attente Validation') { $next = 'Payé'; } // Forçage manuel possible par le serveur
                    elseif ($actuel == 'Payé') { $next = 'Préparation'; }
                    elseif ($actuel == 'Réservé') { $next = 'Préparation'; }
                    elseif ($actuel == 'Préparation') { $next = 'Prêt'; }
                    elseif ($actuel == 'Prêt' || $actuel == 'Arrivé') { $next = 'Parti'; }

                    if ($next !== $actuel) {
                        $this->modele->changerStatut($_GET['id'], $next);
                    }
                }
                header("Location: index.php?module=serveur");
                break;

            // RECULER DANS LE CYCLE
            case 'revert_statut':
                if (isset($_GET['id']) && isset($_GET['actuel'])) {
                    $actuel = $_GET['actuel'];
                    $prev = $actuel;

                    if ($actuel == 'Annulé' || $actuel == 'Parti') { $prev = $actuel; }
                    elseif ($actuel == 'Prêt' || $actuel == 'Arrivé') { $prev = 'Préparation'; }
                    elseif ($actuel == 'Préparation') { $prev = 'Payé'; }
                    elseif ($actuel == 'Payé') { $prev = 'En attente'; }
                    // Si on est en Attente Validation, on ne peut pas vraiment reculer, sauf vers Annulé

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

            case 'changer_statut': // Pour le select box
                if (isset($_POST['id_commande']) && isset($_POST['nouveau_statut'])) {
                    $this->modele->changerStatut($_POST['id_commande'], $_POST['nouveau_statut']);
                }
                header("Location: index.php?module=serveur");
                break;

            case 'dashboard':
            default:
                if ($idBuvette == 0) {
                    echo "<div class='container mt-5 pt-5 alert alert-warning'>Veuillez sélectionner une buvette via le menu.</div>";
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