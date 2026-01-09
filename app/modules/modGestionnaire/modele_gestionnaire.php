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
            FROM produit p
            INNER JOIN contient c ON p.id_produit = c.id_produit
            WHERE p.id_produit = ?
        ");
        $req->execute([$id_produit]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    public function creerEtAjouterProduit($id_buvette, $nom, $prix, $description, $nom_image, $type) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();
            $req1 = $bdd->prepare("INSERT INTO produit (nom_produit, prix_produit, description, image_produit, type_produit) VALUES (?, ?, ?, ?, ?)");
            $req1->execute([$nom, $prix, $description, $nom_image, $type]);
            $id_nouveau = $bdd->lastInsertId();

            $reqInv = $bdd->prepare("SELECT id_inventaire FROM concerner WHERE id_buvette = ? LIMIT 1");
            $reqInv->execute([$id_buvette]);
            $inv = $reqInv->fetch();

            if ($inv) {
                $req2 = $bdd->prepare("INSERT INTO contient (id_inventaire, id_produit, quantite, seuil_alerte) VALUES (?, ?, 0, 5)");
                $req2->execute([$inv['id_inventaire'], $id_nouveau]);
            }
            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            return false;
        }
    }

    public function modifierProduitEtStock($id_produit, $id_buvette, $nouveau_prix, $nouvelle_description, $nouvelle_quantite, $nouveau_type) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();
            $req1 = $bdd->prepare("UPDATE produit SET prix_produit = ?, description = ?, type_produit = ? WHERE id_produit = ?");
            $req1->execute([$nouveau_prix, $nouvelle_description, $nouveau_type, $id_produit]);

            $req2 = $bdd->prepare("
                UPDATE contient c
                INNER JOIN concerner co ON c.id_inventaire = co.id_inventaire
                SET c.quantite = ?
                WHERE c.id_produit = ? AND co.id_buvette = ?
            ");
            $req2->execute([$nouvelle_quantite, $id_produit, $id_buvette]);
            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            return false;
        }
    }

    public function getStocksParBuvette($id_buvette, $seulementAlertes = false) {
        $bdd = Connexion::getBdd();
        $sql = "SELECT p.*, c.quantite, c.seuil_alerte 
            FROM produit p
            INNER JOIN contient c ON p.id_produit = c.id_produit
            INNER JOIN concerner co ON c.id_inventaire = co.id_inventaire
            WHERE co.id_buvette = ?";

        if ($seulementAlertes) {
            $sql .= " AND c.quantite <= c.seuil_alerte";
        }

        $sql .= " ORDER BY p.type_produit ASC, p.nom_produit ASC";

        $req = $bdd->prepare($sql);
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }
}