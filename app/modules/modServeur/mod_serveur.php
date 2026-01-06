<?php
require_once 'cont_serveur.php';

class ModServeur {
    public function __construct() {
        $controleur = new ContServeur();
        $controleur->exec();
    }
}
?>