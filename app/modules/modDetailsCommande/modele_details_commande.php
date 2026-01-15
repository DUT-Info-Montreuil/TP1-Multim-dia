<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleDetailsCommande
{
    private $pdo;

    public function __construct() {
        $this->pdo = Connexion::getBdd();
    }
    public function getInfosCommande($idCommande, $idUser)
    {
        $stmt = $this->pdo->prepare("SELECT c.*, b.nom as nom_buvette 
                FROM commande c
                JOIN une_buvette b ON c.id_buvette = b.id_buvette
                WHERE c.id_commande = ? AND c.id_utilisateur = ?");
        $stmt->execute([$idCommande, $idUser]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProduitsCommande($idCommande)
    {;
        $stmt = $this->pdo->prepare("SELECT p.nom_produit, p.image_produit, lc.quantite, lc.prix_unitaire_moment_vente 
                FROM ligne_commande lc
                JOIN produit p ON lc.id_produit = p.id_produit
                WHERE lc.id_commande = ?");
        $stmt->execute([$idCommande]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}