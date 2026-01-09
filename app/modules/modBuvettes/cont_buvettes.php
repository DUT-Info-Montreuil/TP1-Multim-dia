<?php
require_once 'vue_buvettes.php';
require_once 'modele_buvettes.php';

class ContBuvettes
{
    private $vue;
    private $modele;

    public function __construct()
    {
        $this->vue = new VueBuvettes();
        $this->modele = new ModeleBuvettes();
    }

    public function exec()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : 'afficher';

        switch ($action) {
            case 'adherer':
                $idBuvette = isset($_GET['id_buvette']) ? $_GET['id_buvette'] : null;
                $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
                if ($idBuvette && isset($user['id_utilisateur'])) {
                    $this->modele->adhererBuvette($idBuvette, $user['id_utilisateur']);
                }
                $this->vue->afficherBuvettes(
                    $this->modele->getBuvettes(),
                    $this->modele->getBuvettesAdherent(),
                    $this->modele->getMembreBuvetteEnAdhesion()
                );
                break;
            case 'afficher':
                $adhesions = $this->modele->getBuvettesAdherent();
                $idsMembres = array_column($adhesions, 'id_buvette');
                $toutesLesBuvettes = $this->modele->getBuvettes();

                $buvettesTriees = $this->ordonnerBuvettes($toutesLesBuvettes, $idsMembres);

                $this->vue->afficherBuvettes($buvettesTriees, $adhesions, $this->modele->getMembreBuvetteEnAdhesion());
                break;
        }
    }
    public function ordonnerBuvettes($buvettes, $idsMembres) {
        usort($buvettes, function($a, $b) use ($idsMembres) {
            $scoreA = 1;
            if (in_array($a['id_buvette'], $idsMembres)) {
                $scoreA = 3;
            } elseif ($a['est_ouverte']) {
                $scoreA = 2;
            }

            $scoreB = 1;
            if (in_array($b['id_buvette'], $idsMembres)) {
                $scoreB = 3;
            } elseif ($b['est_ouverte']) {
                $scoreB = 2;
            }

            return ($scoreA < $scoreB) ? 1 : -1;
        });

        return $buvettes;
    }
}