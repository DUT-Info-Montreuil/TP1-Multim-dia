<?php
require_once 'vue_connexion.php';

class ContConnexion {
    private $vue;

    public function __construct() {
        $this->vue = new VueConnexion();
    }

    public function exec() {
        // Logique de routage interne au module de connexion
        $action = isset($_GET['action']) ? $_GET['action'] : 'afficher_inscription';

        switch ($action) {
            case 'afficher_inscription':
                $this->vue->afficherFormulaireInscription();
                break;
            case 'afficher_connexion':
                $this->vue->afficherFormulaireConnexion();
                break;
            default:
                $this->vue->afficherFormulaireInscription();
                break;
        }
    }
}
?>