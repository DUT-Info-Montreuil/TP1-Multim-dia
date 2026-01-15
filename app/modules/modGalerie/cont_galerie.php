<?php
require_once 'vue_galerie.php';

class ContGalerie
{
    private $vue;

    public function __construct()
    {
        $this->vue = new VueGalerie();
    }

    public function exec()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : 'afficher';

        switch ($action) {
            case 'afficher':
            default:
                $this->vue->afficherGalerie();
                break;
        }
    }
}
?>