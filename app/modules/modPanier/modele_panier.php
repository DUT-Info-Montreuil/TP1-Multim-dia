<?php
require_once __DIR__ . '/../../../connexion.php';

class ModelePanier {
    public function getCommandeEnCours($idUser, $idBuvette) {
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("SELECT id_commande FROM commande WHERE id_utilisateur = ? AND id_buvette = ? AND statut = 'En cours'");
        $stmt->execute([$idUser, $idBuvette]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['id_commande'] : null;
    }

    public function creerCommande($idUser, $idBuvette) {
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("INSERT INTO commande (statut, date_commande, prix_total, id_utilisateur, id_buvette) VALUES ('En cours', NOW(), 0, ?, ?)");
        $stmt->execute([$idUser, $idBuvette]);
        return $pdo->lastInsertId();
    }

    public function ajouterArticle($idCommande, $idProduit) {
        $pdo = Connexion::getBdd();

        $stmtPrix = $pdo->prepare("SELECT prix_produit FROM produit WHERE id_produit = ?");
        $stmtPrix->execute([$idProduit]);
        $produit = $stmtPrix->fetch(PDO::FETCH_ASSOC);
        if (!$produit) return;
        $prix = $produit['prix_produit'];

        $stmtVerif = $pdo->prepare("SELECT quantite FROM ligne_commande WHERE id_commande = ? AND id_produit = ?");
        $stmtVerif->execute([$idCommande, $idProduit]);
        $ligne = $stmtVerif->fetch(PDO::FETCH_ASSOC);

        if ($ligne) {
            $stmtUpdate = $pdo->prepare("UPDATE ligne_commande SET quantite = quantite + 1 WHERE id_commande = ? AND id_produit = ?");
            $stmtUpdate->execute([$idCommande, $idProduit]);
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO ligne_commande (id_commande, id_produit, quantite, options, prix_unitaire_moment_vente) VALUES (?, ?, 1, NULL, ?)");
            $stmtInsert->execute([$idCommande, $idProduit, $prix]);
        }

        $this->majTotalCommande($idCommande);
    }

    private function majTotalCommande($idCommande) {
        $pdo = Connexion::getBdd();
        $sql = "UPDATE commande SET prix_total = (
                    SELECT COALESCE(SUM(quantite * prix_unitaire_moment_vente), 0) 
                    FROM ligne_commande WHERE id_commande = ?
                ) WHERE id_commande = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idCommande, $idCommande]);
    }

    public function getProduitsCommande($idCommande) {
        $pdo = Connexion::getBdd();
        $sql = "SELECT p.*, lc.quantite, lc.prix_unitaire_moment_vente 
                FROM ligne_commande lc
                JOIN produit p ON lc.id_produit = p.id_produit
                WHERE lc.id_commande = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idCommande]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCommande($idCommande) {
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("SELECT prix_total FROM commande WHERE id_commande = ?");
        $stmt->execute([$idCommande]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['prix_total'] : 0;
    }

    public function supprimerProduit($idCommande, $idProduit) {
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("DELETE FROM ligne_commande WHERE id_commande = ? AND id_produit = ?");
        $stmt->execute([$idCommande, $idProduit]);

        $this->majTotalCommande($idCommande);
    }

    public function diminuerQuantite($idCommande, $idProduit) {
        $pdo = Connexion::getBdd();

        $stmt = $pdo->prepare("SELECT quantite FROM ligne_commande WHERE id_commande = ? AND id_produit = ?");
        $stmt->execute([$idCommande, $idProduit]);
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ligne) {
            if ($ligne['quantite'] > 1) {
                $update = $pdo->prepare("UPDATE ligne_commande SET quantite = quantite - 1 WHERE id_commande = ? AND id_produit = ?");
                $update->execute([$idCommande, $idProduit]);
            } else {
                $this->supprimerProduit($idCommande, $idProduit);
                return;
            }

            $this->majTotalCommande($idCommande);
        }
    }

    public function getSoldeUtilisateur($idUser) {
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("SELECT solde FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$idUser]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['solde'] : 0;
    }

    public function payerCommande($idUser, $idCommande, $total) {
        $pdo = Connexion::getBdd();
        try {
            $pdo->beginTransaction();

            $stmtDebit = $pdo->prepare("UPDATE utilisateur SET solde = solde - ? WHERE id_utilisateur = ?");
            $stmtDebit->execute([$total, $idUser]);

            $stmtUpdate = $pdo->prepare("UPDATE commande SET statut = 'Payée', date_commande = NOW() WHERE id_commande = ?");
            $stmtUpdate->execute([$idCommande]);

            if(isset($_SESSION['user'])) {
                $_SESSION['user']['solde'] -= $total;
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
    public function aDesCommandesHistorique($idUser) {
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM commande WHERE id_utilisateur = ? AND statut != 'En cours'");
        $stmt->execute([$idUser]);

        return $stmt->fetchColumn() > 0;
    }
}
