<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleServeur {

    public function getListeReservations($idBuvette) {
        $bdd = Connexion::getBdd();
        // On récupère tout
        $sql = "SELECT c.*, u.nom, u.prenom 
                FROM commande c
                JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
                WHERE c.id_buvette = ?
                ORDER BY c.date_commande ASC";

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

        if ($nouveauStatut === 'Arrivé') {
            $sql = "UPDATE commande SET statut = ?, heure_arrivee = NOW() WHERE id_commande = ?";
        } else {
            $sql = "UPDATE commande SET statut = ? WHERE id_commande = ?";
        }

        $stmt = $bdd->prepare($sql);
        $stmt->execute([$nouveauStatut, $idCommande]);
    }

    // Bascule en "Annulé" (Suppression logique)
    public function annulerCommande($idCommande) {
        $bdd = Connexion::getBdd();
        $sql = "UPDATE commande SET statut = 'Annulé' WHERE id_commande = ?";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$idCommande]);
    }
}
?>