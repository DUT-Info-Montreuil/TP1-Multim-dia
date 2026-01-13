<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleGestionnaire {

    public function getBuvettesAutorisees($id_utilisateur) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT b.* FROM une_buvette b
            INNER JOIN affecter a ON b.id_buvette = a.id_buvette
            INNER JOIN role_utilisateur r ON a.id_role = r.id_role
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
    public function getDemandesEnAttente($id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
        SELECT u.id_utilisateur, u.nom, u.prenom, u.email, a.date_adhesion as date_demande
        FROM utilisateur u
        INNER JOIN adhesion a ON u.id_utilisateur = a.id_utilisateur
        WHERE a.id_buvette = ?
        ORDER BY a.date_adhesion ASC
    ");
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMembresAcceptes($id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
        SELECT u.id_utilisateur, u.nom, u.prenom, u.email, aff.date_debut
        FROM utilisateur u
        INNER JOIN affecter aff ON u.id_utilisateur = aff.id_utilisateur
        INNER JOIN role_utilisateur r ON aff.id_role = r.id_role
        WHERE aff.id_buvette = ? AND r.nom_role = 'Client'
        AND (aff.date_fin IS NULL OR aff.date_fin >= CURDATE())
        ORDER BY u.nom ASC
    ");
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function accepterDemande($id_utilisateur, $id_buvette) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();

            $reqRole = $bdd->prepare("SELECT id_role FROM role_utilisateur WHERE nom_role = 'Client' LIMIT 1");
            $reqRole->execute();
            $id_role_client = $reqRole->fetchColumn();

            $reqIns = $bdd->prepare("INSERT INTO affecter (id_role, id_utilisateur, id_buvette, date_debut) VALUES (?, ?, ?, CURDATE())");
            $reqIns->execute([$id_role_client, $id_utilisateur, $id_buvette]);

            $reqDel = $bdd->prepare("DELETE FROM adhesion WHERE id_utilisateur = ? AND id_buvette = ?");
            $reqDel->execute([$id_utilisateur, $id_buvette]);

            $bdd->commit();
        } catch (Exception $e) {
            $bdd->rollBack();
            throw $e;
        }
    }

    public function supprimerDemandeOuMembre($id_utilisateur, $id_buvette, $estDemande = true) {
        $bdd = Connexion::getBdd();
        if ($estDemande) {
            $req = $bdd->prepare("DELETE FROM adhesion WHERE id_utilisateur = ? AND id_buvette = ?");
        } else {
            $req = $bdd->prepare("DELETE FROM affecter WHERE id_utilisateur = ? AND id_buvette = ? AND id_role = (SELECT id_role FROM role_utilisateur WHERE nom_role = 'Client')");
        }
        return $req->execute([$id_utilisateur, $id_buvette]);
    }

    public function getStatsBuvette($id_buvette) {
        $bdd = Connexion::getBdd();
        $sql = "SELECT 
        -- Valeur du stock pour cette buvette
        (SELECT SUM(c.quantite * p.prix_produit) 
         FROM contient c 
         JOIN produit p ON c.id_produit = p.id_produit 
         JOIN concerner co ON c.id_inventaire = co.id_inventaire 
         WHERE co.id_buvette = ?) as valeur_stock,
         
        -- NOMBRE D'ALERTES : Correction ici, on joint 'concerner' pour filtrer par id_buvette
        (SELECT COUNT(*) 
         FROM contient c 
         JOIN concerner co ON c.id_inventaire = co.id_inventaire 
         WHERE co.id_buvette = ? 
         AND c.quantite <= c.seuil_alerte) as alertes_count,
         
        -- Demandes d'adhésion pour cette buvette
        (SELECT COUNT(*) 
         FROM adhesion 
         WHERE id_buvette = ?) as demandes_count,
         
        -- Membres actifs pour cette buvette
        (SELECT COUNT(*) 
         FROM affecter aff 
         JOIN role_utilisateur r ON aff.id_role = r.id_role 
         WHERE aff.id_buvette = ? 
         AND r.nom_role = 'Client' 
         AND (aff.date_fin IS NULL OR aff.date_fin >= CURDATE())) as membres_count";

        $req = $bdd->prepare($sql);
        $req->execute([$id_buvette, $id_buvette, $id_buvette, $id_buvette]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }
}