<?php

require_once 'vue_buvettes.php';

class ContBuvettes
{
    private $vue;

    public function __construct()
    {
        $this->vue = new VueBuvettes();
    }

    public function exec()
    {
        $this->vue->afficherBuvettes();
    }
}
