<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleBuvettes {
    public function getBuvettes() {
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("SELECT * FROM une_buvette");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}