<?php
require_once 'cont_details_commande.php';

class ModDetailsCommande {
    public function __construct() {
        $controleur = new ContDetailsCommande();
        $controleur->exec();
    }
}
?>