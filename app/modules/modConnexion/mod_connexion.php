<?php
require_once 'cont_connexion.php';

class ModConnexion {
    public function __construct() {
        $cont = new ContConnexion();
        $cont->exec();
    }
}
?>