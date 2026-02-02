<?php
require_once 'cont_compte.php';

class ModCompte
{
    public function __construct()
    {
        $cont = new ContCompte();
        $cont->exec();
    }
}
