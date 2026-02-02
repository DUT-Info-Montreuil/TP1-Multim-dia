<?php
require_once 'cont_histoire.php';

class ModHistoire {
    public function __construct() {
        $controleur = new ContHistoire();
        $controleur->exec();
    }
}
?>