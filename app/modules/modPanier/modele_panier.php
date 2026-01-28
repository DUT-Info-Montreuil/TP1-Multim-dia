<?php
require_once __DIR__ . '/../../../connexion.php';

class ModelePanier {
    private $pdo;

    public function __construct() {
        $this->pdo = Connexion::getBdd();
    }

    public function getCommandeEnCours($idUser, $idBuvette) {
        $stmt = $this->pdo->prepare("SELECT id_commande FROM commande 
                WHERE id_utilisateur = ? 
                AND id_buvette = ? 
                AND statut = 'En cours' 
                AND est_paye = 0");
        $stmt->execute([$idUser, $idBuvette]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['id_commande'] : null;
    }

    public function creerCommande($idUser, $idBuvette) {
        $stmt = $this->pdo->prepare("INSERT INTO commande (statut, date_commande, prix_total, id_utilisateur, id_buvette, est_paye) VALUES ('En cours', NOW(), 0, ?, ?, 0)");
        $stmt->execute([$idUser, $idBuvette]);
        return $this->pdo->lastInsertId();
    }

    public function ajouterArticle($idCommande, $idProduit) {

        $stmtPrix = $this->pdo->prepare("SELECT prix_produit FROM produit WHERE id_produit = ?");
        $stmtPrix->execute([$idProduit]);
        $produit = $stmtPrix->fetch(PDO::FETCH_ASSOC);
        if (!$produit) return;
        $prix = $produit['prix_produit'];

        $stmtVerif = $this->pdo->prepare("SELECT quantite FROM ligne_commande WHERE id_commande = ? AND id_produit = ?");
        $stmtVerif->execute([$idCommande, $idProduit]);
        $ligne = $stmtVerif->fetch(PDO::FETCH_ASSOC);

        if ($ligne) {
            $stmtUpdate = $this->pdo->prepare("UPDATE ligne_commande SET quantite = quantite + 1 WHERE id_commande = ? AND id_produit = ?");
            $stmtUpdate->execute([$idCommande, $idProduit]);
        } else {
            $stmtInsert = $this->pdo->prepare("INSERT INTO ligne_commande (id_commande, id_produit, quantite, options, prix_unitaire_moment_vente) VALUES (?, ?, 1, NULL, ?)");
            $stmtInsert->execute([$idCommande, $idProduit, $prix]);
        }

        $this->majTotalCommande($idCommande);
    }

    private function majTotalCommande($idCommande) {

        $stmt = $this->pdo->prepare("UPDATE commande SET prix_total = (
                    SELECT COALESCE(SUM(quantite * prix_unitaire_moment_vente), 0) 
                    FROM ligne_commande WHERE id_commande = ?
                ) WHERE id_commande = ?");
        $stmt->execute([$idCommande, $idCommande]);
    }

    public function getProduitsCommande($idCommande) {
        $stmt = $this->pdo->prepare("SELECT p.*, lc.quantite, lc.prix_unitaire_moment_vente 
                FROM ligne_commande lc
                JOIN produit p ON lc.id_produit = p.id_produit
                WHERE lc.id_commande = ?");
        $stmt->execute([$idCommande]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCommande($idCommande) {
        $stmt = $this->pdo->prepare("SELECT prix_total FROM commande WHERE id_commande = ?");
        $stmt->execute([$idCommande]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['prix_total'] : 0;
    }

    public function supprimerProduit($idCommande, $idProduit) {
        $stmt = $this->pdo->prepare("DELETE FROM ligne_commande WHERE id_commande = ? AND id_produit = ?");
        $stmt->execute([$idCommande, $idProduit]);

        $this->majTotalCommande($idCommande);
    }

    public function diminuerQuantite($idCommande, $idProduit) {
        $stmt = $this->pdo->prepare("SELECT quantite FROM ligne_commande WHERE id_commande = ? AND id_produit = ?");
        $stmt->execute([$idCommande, $idProduit]);
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ligne) {
            if ($ligne['quantite'] > 1) {
                $update = $this->pdo->prepare("UPDATE ligne_commande SET quantite = quantite - 1 WHERE id_commande = ? AND id_produit = ?");
                $update->execute([$idCommande, $idProduit]);
            } else {
                $this->supprimerProduit($idCommande, $idProduit);
                return;
            }

            $this->majTotalCommande($idCommande);
        }
    }

    public function getSoldeUtilisateur($idUser, $idBuvette) {
        $stmt = $this->pdo->prepare("SELECT solde FROM solde WHERE id_utilisateur = ? AND id_buvette = ?");
        $stmt->execute([$idUser, $idBuvette]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['solde'] : 0;
    }

    public function validerCommande($idUser, $idCommande, $total) {;

        $stmt = $this->pdo->prepare("UPDATE commande 
                SET statut = 'En attente confirmation', 
                    est_paye = 0, 
                    date_commande = NOW(), 
                    prix_total = ? 
                WHERE id_commande = ? AND id_utilisateur = ?");
        return $stmt->execute([$total, $idCommande, $idUser]);
    }

    public function aDesCommandesHistorique($idUser) {;
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM commande 
                WHERE id_utilisateur = ? 
                AND (statut != 'En cours' OR est_paye = 1)
                AND date_commande >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        $stmt->execute([$idUser]);

        return $stmt->fetchColumn() > 0;
    }
}
