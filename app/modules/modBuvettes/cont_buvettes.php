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
        $action = $_GET['action'] ?? 'afficher';

        switch ($action) {
            case 'adherer':
                $idBuvette = $_GET['id_buvette'] ?? null;
                $user = $_SESSION['user'] ?? null;
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
                $this->vue->afficherBuvettes(
                $this->modele->getBuvettes(),
                $this->modele->getBuvettesAdherent(),
                $this->modele->getMembreBuvetteEnAdhesion()
                );
                break;
        }
    }
}
