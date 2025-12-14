<?php
require_once 'cont_buvettes.php';

class ModBuvettes {
    public function __construct() {
        $cont = new ContBuvettes();
        $cont->exec();
    }
}
?>