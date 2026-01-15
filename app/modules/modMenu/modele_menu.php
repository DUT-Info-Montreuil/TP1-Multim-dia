<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleMenu {
    private $pdo;

    public function __construct() {
        $this->pdo = Connexion::getBdd();
    }

    public function getProduitsParBuvette($idBuvette, $filtre = 'all') {

        $sql = "SELECT produit.*, contient.quantite 
                FROM produit 
                INNER JOIN contient ON produit.id_produit = contient.id_produit 
                INNER JOIN concerner ON contient.id_inventaire = concerner.id_inventaire 
                WHERE concerner.id_buvette = :id_buvette";

        if ($filtre !== 'all') {
            $sql .= " AND produit.type_produit = :filtre";
        }

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':id_buvette', $idBuvette, PDO::PARAM_INT);

        if ($filtre !== 'all') {
            $stmt->bindValue(':filtre', urldecode($filtre), PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNomBuvette($idBuvette) {
        $stmt = $this->pdo->prepare("SELECT nom FROM une_buvette WHERE id_buvette = :id_buvette");
        $stmt->bindValue(':id_buvette', $idBuvette, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['nom'] : null;
    }
}
?>