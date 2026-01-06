<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleMenu {
    public function getProduitsParBuvette($idBuvette) {
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("SELECT * FROM produit INNER JOIN contient ON produit.id_produit = contient.id_produit INNER JOIN concerner ON contient.id_inventaire = concerner.id_inventaire WHERE concerner.id_buvette = :id_buvette");
        $stmt->bindParam(':id_buvette', $idBuvette);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
