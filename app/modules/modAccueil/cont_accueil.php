<?php
require_once 'vue_accueil.php';
class ContAccueil {
    private $vue;
    public function __construct() { $this->vue = new VueAccueil(); }
    public function home() { $this->vue->afficherAccueil(); }
}
?>