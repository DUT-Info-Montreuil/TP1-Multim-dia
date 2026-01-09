<?php
require_once 'Connexion.php';

class ModeleCompte
{
    public function getHistorique($idUser)
    {
        $pdo = Connexion::getBdd();
        $sql = "SELECT c.*, b.nom as nom_buvette, 
            (SELECT SUM(quantite) FROM ligne_commande lc WHERE lc.id_commande = c.id_commande) as nb_articles
            FROM commande c
            JOIN une_buvette b ON c.id_buvette = b.id_buvette
            WHERE c.id_utilisateur = ? 
            AND (c.statut != 'En cours' OR c.est_paye = 1) 
            ORDER BY c.date_commande DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idUser]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetailsCommande($idCommande)
    {
        $pdo = Connexion::getBdd();
        $sql = "SELECT p.nom_produit, p.image_produit, lc.quantite, lc.prix_unitaire_moment_vente
            FROM ligne_commande lc
            JOIN produit p ON lc.id_produit = p.id_produit
            WHERE lc.id_commande = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idCommande]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
