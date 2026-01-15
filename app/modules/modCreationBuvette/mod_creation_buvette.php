<?php
require_once 'cont_creation_buvette.php';

class ModCreationBuvette
{
    public function __construct()
    {
        $controleur = new ContCreationBuvette();
        $controleur->exec();
    }
}
?>