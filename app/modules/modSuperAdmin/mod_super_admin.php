<?php
require_once 'cont_super_admin.php';

class ModSuperAdmin {
    public function __construct() {
        $controleur = new ContSuperAdmin();
        $controleur->exec();
    }
}
?>
