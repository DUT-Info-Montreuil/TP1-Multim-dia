<?php
require_once 'cont_solde.php';

class ModSolde
{
    public function __construct()
    {
        $cont = new ContSolde();
        $cont->exec();
    }
}
