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

    public function exec() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'liste';
        $idUser = isset($_SESSION['user']['id_utilisateur']) ? $_SESSION['user']['id_utilisateur'] : 0;

        switch ($action) {
            case 'rejoindre':
                if (isset($_GET['id_buvette']) && $idUser > 0) {
                    $this->modele->rejoindreEquipe($idUser, (int)$_GET['id_buvette']);
                }
                header("Location: index.php?module=buvettes");
                exit();
                break;

            case 'liste':
            default:
                $groupes = $this->modele->getBuvettesParAcces($idUser);
                $this->vue->afficherBuvettesGroupées($groupes);
                break;
        }
    }
}
