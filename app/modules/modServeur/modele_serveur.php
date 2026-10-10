<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleServeur {

    // AUDIT-007 : l'utilisateur doit être serveur (ou gestionnaire) affecté à cette buvette
    public function estAffecteABuvette($idUtilisateur, $idBuvette) {
        $bdd = Connexion::getBdd();
        $stmt = $bdd->prepare("
            SELECT COUNT(*) FROM affecter a
            INNER JOIN role_utilisateur r ON a.id_role = r.id_role
            WHERE a.id_utilisateur = ? AND a.id_buvette = ?
            AND r.nom_role IN ('Serveur', 'Gestionnaire')
            AND (a.date_fin IS NULL OR a.date_fin >= CURDATE())");
        $stmt->execute([$idUtilisateur, $idBuvette]);
        return $stmt->fetchColumn() > 0;
    }

    // AUDIT-007 : la commande doit appartenir à la buvette de l'utilisateur
    public function commandeAppartientABuvette($idCommande, $idBuvette) {
        $bdd = Connexion::getBdd();
        $stmt = $bdd->prepare("SELECT COUNT(*) FROM commande WHERE id_commande = ? AND id_buvette = ?");
        $stmt->execute([$idCommande, $idBuvette]);
        return $stmt->fetchColumn() > 0;
    }

    public function getListeReservations($idBuvette) {
        $bdd = Connexion::getBdd();
        // On récupère aussi les commandes en "Attente Validation" pour que le serveur sache qu'il a envoyé une demande
        $sql = "SELECT c.*, u.nom, u.prenom 
                FROM commande c
                JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
                WHERE c.id_buvette = ? 
                AND (c.statut != 'En cours' OR c.est_paye = 1)
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

    public function getStatutCommande($idCommande) {
        $bdd = Connexion::getBdd();
        $stmt = $bdd->prepare("SELECT statut FROM commande WHERE id_commande = ?");
        $stmt->execute([$idCommande]);
        return $stmt->fetchColumn();
    }

    public function annulerCommande($idCommande) {
        $bdd = Connexion::getBdd();
        $sql = "UPDATE commande SET statut = 'Annulée' WHERE id_commande = ?";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$idCommande]);

        // Si c'était une demande de validation, on la marque comme vue/annulée
        $sqlNotif = "UPDATE notification_validation SET est_vue = 1 WHERE id_commande = ?";
        $stmtNotif = $bdd->prepare($sqlNotif);
        $stmtNotif->execute([$idCommande]);
    }

    public function debiterCommandeSiNonPayee($idCommande) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();

            $stmt = $bdd->prepare("SELECT id_utilisateur, id_buvette, prix_total, est_paye 
                    FROM commande WHERE id_commande = ?");
            $stmt->execute([$idCommande]);
            $commande = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$commande || (int)$commande['est_paye'] === 1) {
                $bdd->commit();
                return true;
            }

            $stmtDebit = $bdd->prepare("UPDATE solde SET solde = solde - ? WHERE id_utilisateur = ? AND id_buvette = ?");
            $stmtDebit->execute([$commande['prix_total'], $commande['id_utilisateur'], $commande['id_buvette']]);

            $stmtCmd = $bdd->prepare("UPDATE commande SET est_paye = 1 WHERE id_commande = ?");
            $stmtCmd->execute([$idCommande]);

            $descriptionCommande = "Paiement commande #" . $idCommande;
            $stmtMvmt = $bdd->prepare("INSERT INTO mouvement_tresorerie (id_buvette, type_mouvement, montant, categorie, description, id_utilisateur, id_commande)
                    VALUES (?, 'Entrée', ?, 'Vente', ?, ?, ?)");
            $stmtMvmt->execute([$commande['id_buvette'], $commande['prix_total'], $descriptionCommande, $commande['id_utilisateur'], $idCommande]);

            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            return false;
        }
    }

    public function getTousLesProduits() {
        $bdd = Connexion::getBdd();
        return $bdd->query("SELECT * FROM produit")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rechercherUtilisateur($query) {
        $bdd = Connexion::getBdd();
        $sql = "SELECT u.id_utilisateur, u.nom, u.prenom, s.solde FROM utilisateur u
                LEFT JOIN solde s ON u.id_utilisateur = s.id_utilisateur
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

            // 1. Vérifier le solde dans la table SOLDE
            $stmt = $bdd->prepare("SELECT solde FROM solde WHERE id_utilisateur = ? AND id_buvette = ?");
            $stmt->execute([$idUtilisateur, $idBuvette]);
            $soldeActuel = $stmt->fetchColumn();

            if ($soldeActuel === false || $soldeActuel < $total) {
                // Pour affichage propre même si solde null
                $soldeStr = ($soldeActuel === false) ? 0 : $soldeActuel;
                throw new Exception("Solde insuffisant (Solde : " . $soldeStr . "€).");
            }

            // 2. Créer la commande en statut "Attente Validation"
            $stmt = $bdd->prepare("INSERT INTO commande (statut, date_commande, prix_total, id_utilisateur, id_buvette, est_paye) VALUES ('Attente Validation', NOW(), ?, ?, ?, 0)");
            $stmt->execute([$total, $idUtilisateur, $idBuvette]);
            $idCommande = $bdd->lastInsertId();

            // 3. Ajouter les lignes
            foreach ($panier as $item) {
                $stmt = $bdd->prepare("INSERT INTO ligne_commande (id_commande, id_produit, quantite, prix_unitaire_moment_vente) VALUES (?, ?, ?, ?)");
                $stmt->execute([$idCommande, $item['id'], $item['quantite'], $item['prix']]);
            }

            // 4. Créer la notification pour le client
            $stmtNotif = $bdd->prepare("INSERT INTO notification_validation (id_utilisateur, id_commande, montant, date_creation) VALUES (?, ?, ?, NOW())");
            $stmtNotif->execute([$idUtilisateur, $idCommande, $total]);

            // ON NE DEBITE PAS ENCORE
            // Le débit se fera via validerPaiementNotification dans ModeleCompte

            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            return $e->getMessage();
        }
    }
}
?>
