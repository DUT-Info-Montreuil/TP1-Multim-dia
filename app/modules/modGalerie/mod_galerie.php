<?php
require_once 'cont_galerie.php';

class ModGalerie
{
    public function __construct()
    {
        $controleur = new ContGalerie();
        $controleur->exec();
    }
}
?>