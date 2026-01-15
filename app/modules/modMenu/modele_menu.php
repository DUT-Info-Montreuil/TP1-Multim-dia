<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleMenu {
    private $pdo;

    public function __construct() {
        $this->pdo = Connexion::getBdd();
    }
    public function getProduitsParBuvette($idBuvette) {
        $stmt = $this->pdo->prepare("SELECT * FROM produit INNER JOIN contient ON produit.id_produit = contient.id_produit INNER JOIN concerner ON contient.id_inventaire = concerner.id_inventaire WHERE concerner.id_buvette = :id_buvette");
        $stmt->bindParam(':id_buvette', $idBuvette);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNomBuvette($idBuvette) {
        $stmt = $this->pdo->prepare("SELECT nom FROM une_buvette WHERE id_buvette = :id_buvette");
        $stmt->bindParam(':id_buvette', $idBuvette);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['nom'] : null;
    }
}
