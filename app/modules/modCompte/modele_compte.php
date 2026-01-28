<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleCompte
{
    private $pdo;

    public function __construct() {
        $this->pdo = Connexion::getBdd();
    }

    public function payerCommandeNotification($idUser, $idNotif)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT n.id_commande, n.montant, c.id_buvette, s.solde 
                        FROM notification_validation n
                        JOIN commande c ON n.id_commande = c.id_commande
                        LEFT JOIN solde s ON c.id_buvette = s.id_buvette AND s.id_utilisateur = n.id_utilisateur
                        WHERE n.id_notification = ? AND n.id_utilisateur = ?");
            $stmt->execute([$idNotif, $idUser]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$info) {
                $this->pdo->rollBack();
                return 3;
            }

            $montantAPayer = $info['montant'];
            $soldeActuel = $info['solde'] ? $info['solde'] : 0;
            $idBuvette = $info['id_buvette'];
            $idCommande = $info['id_commande'];

            if ($soldeActuel < $montantAPayer) {
                $this->pdo->rollBack();
                return 2;
            }

            $sqlDebit = "UPDATE solde SET solde = solde - ? WHERE id_utilisateur = ? AND id_buvette = ?";
            $stmtDebit = $this->pdo->prepare($sqlDebit);
            $stmtDebit->execute([$montantAPayer, $idUser, $idBuvette]);

            $sqlUpdateCmd = "UPDATE commande SET statut = 'En attente', est_paye = 1 WHERE id_commande = ?";
            $stmtCmd = $this->pdo->prepare($sqlUpdateCmd);
            $stmtCmd->execute([$idCommande]);

            $sqlDel = "DELETE FROM notification_validation WHERE id_notification = ?";
            $stmtDel = $this->pdo->prepare($sqlDel);
            $stmtDel->execute([$idNotif]);
            $descriptionCommande = "Paiement commande #" . $idCommande;
            $sqlMvmt = "INSERT INTO mouvement_tresorerie (id_buvette, type_mouvement, montant, categorie, description, id_utilisateur, id_commande) 
                        VALUES (?, 'Entrée', ?, 'Vente', ?, ?, ?)";
            $stmtMvmt = $this->pdo->prepare($sqlMvmt);
            $stmtMvmt->execute([$idBuvette, $montantAPayer,$descriptionCommande, $idUser, $idCommande]);

            $this->pdo->commit();
            return 1;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return 3;
        }
    }

    public function refuserCommandeNotification($idUser, $idNotif)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT id_commande FROM notification_validation WHERE id_notification = ? AND id_utilisateur = ?");
            $stmt->execute([$idNotif, $idUser]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$res) {
                $this->pdo->rollBack();
                return false;
            }

            $idCommande = $res['id_commande'];

            $stmtUpd = $this->pdo->prepare("UPDATE commande SET statut = 'Annulée' WHERE id_commande = ?");
            $stmtUpd->execute([$idCommande]);

            $stmtDel = $this->pdo->prepare("DELETE FROM notification_validation WHERE id_notification = ?");
            $stmtDel->execute([$idNotif]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getNotifications($idUser)
    {
        $stmt = $this->pdo->prepare("SELECT n.*, b.nom as nom_buvette 
                FROM notification_validation n
                JOIN commande c ON n.id_commande = c.id_commande
                JOIN une_buvette b ON c.id_buvette = b.id_buvette
                WHERE n.id_utilisateur = ?
                ORDER BY n.date_creation DESC");
        $stmt->execute([$idUser]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marquerNotificationsVues($idUser)
    {
        // Met à jour le champ 'est_vue' à 1 pour l'utilisateur
        $sql = "UPDATE notification_validation SET est_vue = 1 WHERE id_utilisateur = ? AND est_vue = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idUser]);
    }

    public function ajouterSolde($idUser, $idBuvette, $montant) {
        $stmtVerif = $this->pdo->prepare("SELECT COUNT(*) FROM solde WHERE id_utilisateur = ? AND id_buvette = ?");
        $stmtVerif->execute([$idUser, $idBuvette]);
        $existe = $stmtVerif->fetchColumn();

        if ($existe) {
            $stmt = $this->pdo->prepare("UPDATE solde SET solde = solde + ? WHERE id_utilisateur = ? AND id_buvette = ?");
            $stmt->execute([$montant, $idUser, $idBuvette]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO solde (id_utilisateur, id_buvette, solde) VALUES (?, ?, ?)");
            $stmt->execute([$idUser, $idBuvette, $montant]);
        }
    }
    public function getBuvettesAdherent() {
        if (!isset($_SESSION['user']['id_utilisateur'])) return [];

        $idUser = $_SESSION['user']['id_utilisateur'];
        $stmt = $this->pdo->prepare("SELECT DISTINCT b.*, COALESCE(s.solde, 0) as solde 
            FROM une_buvette b 
            JOIN affecter a ON b.id_buvette = a.id_buvette 
            JOIN role_utilisateur r ON a.id_role = r.id_role
            LEFT JOIN solde s ON b.id_buvette = s.id_buvette AND s.id_utilisateur = a.id_utilisateur
            WHERE a.id_utilisateur = ?
            AND r.nom_role = 'Client'
            AND (a.date_fin IS NULL OR a.date_fin >= CURDATE())");
        $stmt->execute([$idUser]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function purgerHistoriqueCommandes($idUser)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT id_commande FROM commande
                WHERE id_utilisateur = ?
                AND statut != 'En cours'
                AND date_commande < DATE_SUB(NOW(), INTERVAL 1 MONTH)");
            $stmt->execute([$idUser]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $delNotif = $this->pdo->prepare("DELETE FROM notification_validation WHERE id_commande IN ($placeholders)");
                $delNotif->execute($ids);

                $delLignes = $this->pdo->prepare("DELETE FROM ligne_commande WHERE id_commande IN ($placeholders)");
                $delLignes->execute($ids);

                $delCmd = $this->pdo->prepare("DELETE FROM commande WHERE id_commande IN ($placeholders)");
                $delCmd->execute($ids);
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
        }
    }
    public function getHistorique($idUser)
    {
        $this->purgerHistoriqueCommandes($idUser);
        $stmt = $this->pdo->prepare("SELECT c.*, b.nom as nom_buvette, 
            (SELECT SUM(quantite) FROM ligne_commande lc WHERE lc.id_commande = c.id_commande) as nb_articles
            FROM commande c
            JOIN une_buvette b ON c.id_buvette = b.id_buvette
            WHERE c.id_utilisateur = ? 
            AND (c.statut != 'En cours' OR c.est_paye = 1)
            AND c.date_commande >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ORDER BY c.date_commande DESC");
        $stmt->execute([$idUser]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetailsCommande($idCommande)
    {
        $stmt = $this->pdo->prepare("SELECT p.nom_produit, p.image_produit, lc.quantite, lc.prix_unitaire_moment_vente
            FROM ligne_commande lc
            JOIN produit p ON lc.id_produit = p.id_produit
            WHERE lc.id_commande = ?");
        $stmt->execute([$idCommande]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateEmail($idUser, $newEmail)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE utilisateur SET email = ? WHERE id_utilisateur = ?");
            return $stmt->execute([$newEmail, $idUser]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getHashMdp($idUser)
    {
        $stmt = $this->pdo->prepare("SELECT motdepasse FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$idUser]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return $res ? $res['motdepasse'] : null;
    }

    public function updateMdp($idUser, $newHash)
    {

        $stmt = $this->pdo->prepare("UPDATE utilisateur SET motdepasse = ? WHERE id_utilisateur = ?");
        return $stmt->execute([$newHash, $idUser]);
    }
}
?>
