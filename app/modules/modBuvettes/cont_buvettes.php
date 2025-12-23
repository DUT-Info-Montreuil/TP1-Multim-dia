<?php

require_once 'vue_buvettes.php';
require_once 'modele_buvettes.php';

class ContBuvettes
{
    private $vue;
    private $modele;

    public function __construct()
    {
        $this->vue = new VueBuvettes();
        $this->modele = new ModeleBuvettes();
    }

    public function exec()
    {
        $this->vue->afficherBuvettes($this->modele->getBuvettes());
    }
}
