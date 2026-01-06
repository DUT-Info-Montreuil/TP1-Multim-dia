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

            // RECULER : Parti -> Arrivé -> Préparation -> Réservé
            case 'revert_statut':
                if (isset($_GET['id']) && isset($_GET['actuel'])) {
                    $actuel = $_GET['actuel'];
                    $prev = $actuel;

                    if ($actuel == 'Parti') {
                        $prev = 'Arrivé';
                    } elseif ($actuel == 'Arrivé') {
                        $prev = 'Préparation';
                    } elseif ($actuel == 'Préparation') {
                        $prev = 'Réservé';
                    } elseif ($actuel == 'Annulé') {
                        $prev = 'Réservé';
                    }

                    if ($prev !== $actuel) {
                        $this->modele->changerStatut($_GET['id'], $prev);
                    }
                }
                header("Location: index.php?module=serveur");
                break;

            case 'marquer_arrive':
                if (isset($_GET['id'])) $this->modele->changerStatut($_GET['id'], 'Arrivé');
                header("Location: index.php?module=serveur");
                break;

            case 'marquer_parti':
                if (isset($_GET['id'])) $this->modele->changerStatut($_GET['id'], 'Parti');
                header("Location: index.php?module=serveur");
                break;

            case 'annuler':
                if (isset($_GET['id'])) $this->modele->annulerCommande($_GET['id']);
                header("Location: index.php?module=serveur");
                break;

            case 'dashboard':
            default:
                $reservations = $this->modele->getListeReservations($idBuvette);
                foreach ($reservations as &$resa) {
                    $resa['details'] = $this->modele->getDetailsCommande($resa['id_commande']);
                }
                $this->vue->afficherDashboard($reservations);
                break;
        }
    }
}
?>