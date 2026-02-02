<?php
require_once 'cont_gestionnaire.php';

class ModGestionnaire {
    public function __construct() {
        $controleur = new ContGestionnaire();
        $controleur->exec();
    }
}
?>