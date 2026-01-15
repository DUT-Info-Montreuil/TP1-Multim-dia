<?php
require_once 'vue_histoire.php';

class ContHistoire {
    private $vue;

    public function __construct() {
        $this->vue = new VueHistoire();
    }

    public function exec() {
        $this->vue->afficherHistoire();
    }
}
?>