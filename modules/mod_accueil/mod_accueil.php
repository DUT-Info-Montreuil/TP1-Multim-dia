<?php
require_once 'cont_accueil.php';
class ModAccueil {
    public function __construct() {
        $c = new ContAccueil();
        $c->home();
    }
}
?>