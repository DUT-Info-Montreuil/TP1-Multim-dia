<?php
require_once 'cont_panier.php';

class ModPanier
{
    public function __construct()
    {
        $controleur = new ContPanier();
        $controleur->exec();
    }
}
?>