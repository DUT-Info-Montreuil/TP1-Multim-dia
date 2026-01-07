<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleGestionnaire {

    public function getBuvettesAutorisees($id_utilisateur) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT b.* FROM Une_Buvette b
            INNER JOIN Affecter a ON b.id_buvette = a.id_buvette
            INNER JOIN Role_Utilisateur r ON a.id_role = r.id_role
            WHERE a.id_utilisateur = ? 
            AND r.nom_role = 'Gestionnaire'
            AND (a.date_fin IS NULL OR a.date_fin >= CURDATE())
        ");
        $req->execute([$id_utilisateur]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getDetailsProduit($id_produit) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT p.*, c.quantite, c.seuil_alerte 
            FROM Produit p
            INNER JOIN Contient c ON p.id_produit = c.id_produit
            WHERE p.id_produit = ?
        ");
        $req->execute([$id_produit]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }
    public function getStocksParBuvette($id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
        SELECT p.*, c.quantite, c.seuil_alerte 
        FROM Produit p
        INNER JOIN Contient c ON p.id_produit = c.id_produit
        INNER JOIN Concerner co ON c.id_inventaire = co.id_inventaire
        WHERE co.id_buvette = ?
        ORDER BY p.nom_produit ASC 
    ");
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProduitsHorsBuvette($id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
        SELECT * FROM Produit 
        WHERE id_produit NOT IN (
            SELECT c.id_produit FROM Contient c 
            INNER JOIN Concerner co ON c.id_inventaire = co.id_inventaire 
            WHERE co.id_buvette = ?
        )
        ORDER BY nom_produit ASC
    ");
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }
    public function creerEtAjouterProduit($id_buvette, $nom, $prix, $nom_image) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();

            $req1 = $bdd->prepare("INSERT INTO Produit (nom_produit, prix_produit, image_produit) VALUES (?, ?, ?)");
            $req1->execute([$nom, $prix, $nom_image]);
            $id_nouveau = $bdd->lastInsertId();

            $reqInv = $bdd->prepare("SELECT id_inventaire FROM Concerner WHERE id_buvette = ? LIMIT 1");
            $reqInv->execute([$id_buvette]);
            $inv = $reqInv->fetch();

            if ($inv) {
                $req2 = $bdd->prepare("INSERT INTO Contient (id_inventaire, id_produit, quantite, seuil_alerte) VALUES (?, ?, 0, 5)");
                $req2->execute([$inv['id_inventaire'], $id_nouveau]);
            }

            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            return false;
        }
    }
}