<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleServeur {

    public function getListeReservations($idBuvette) {
        $bdd = Connexion::getBdd();
        // Le WHERE c.id_buvette = ? assure qu'on ne voit que les commandes de CETTE buvette
        $sql = "SELECT c.*, u.nom, u.prenom 
                FROM commande c
                JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
                WHERE c.id_buvette = ? 
                ORDER BY c.date_commande DESC";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$idBuvette]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetailsCommande($idCommande) {
        $bdd = Connexion::getBdd();
        $sql = "SELECT lc.*, p.nom_produit 
                FROM ligne_commande lc
                JOIN produit p ON lc.id_produit = p.id_produit
                WHERE lc.id_commande = ?";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$idCommande]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function changerStatut($idCommande, $nouveauStatut) {
        $bdd = Connexion::getBdd();
        $sql = "UPDATE commande SET statut = ? WHERE id_commande = ?";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$nouveauStatut, $idCommande]);
    }

    public function annulerCommande($idCommande) {
        $bdd = Connexion::getBdd();
        $sql = "UPDATE commande SET statut = 'Annulé' WHERE id_commande = ?";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$idCommande]);
    }

    // --- VENTE AU COMPTOIR ---
    public function getTousLesProduits() {
        $bdd = Connexion::getBdd();
        return $bdd->query("SELECT * FROM produit")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rechercherUtilisateur($query) {
        $bdd = Connexion::getBdd();
        $sql = "SELECT id_utilisateur, nom, prenom, solde FROM utilisateur 
                WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? LIMIT 10";
        $stmt = $bdd->prepare($sql);
        $q = "%$query%";
        $stmt->execute([$q, $q, $q]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function enregistrerVenteComptoir($idUtilisateur, $idBuvette, $panier, $total) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();

            // 1. Vérifier le solde
            $stmt = $bdd->prepare("SELECT solde FROM utilisateur WHERE id_utilisateur = ?");
            $stmt->execute([$idUtilisateur]);
            $user = $stmt->fetch();

            if (!$user || $user['solde'] < $total) {
                throw new Exception("Solde insuffisant.");
            }

            // 2. Créer la commande
            // MODIFICATION ICI : Statut 'Payé' (En attente) pour qu'elle apparaisse dans "En cours"
            $stmt = $bdd->prepare("INSERT INTO commande (statut, date_commande, prix_total, id_utilisateur, id_buvette) VALUES ('Payé', NOW(), ?, ?, ?)");
            $stmt->execute([$total, $idUtilisateur, $idBuvette]);
            $idCommande = $bdd->lastInsertId();

            // 3. Ajouter les lignes
            foreach ($panier as $item) {
                $stmt = $bdd->prepare("INSERT INTO ligne_commande (id_commande, id_produit, quantite, prix_unitaire_moment_vente) VALUES (?, ?, ?, ?)");
                $stmt->execute([$idCommande, $item['id'], $item['quantite'], $item['prix']]);
            }

            // 4. Débiter l'utilisateur
            $stmt = $bdd->prepare("UPDATE utilisateur SET solde = solde - ? WHERE id_utilisateur = ?");
            $stmt->execute([$total, $idUtilisateur]);

            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            return $e->getMessage();
        }
    }
}
?>