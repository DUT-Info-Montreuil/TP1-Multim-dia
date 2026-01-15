<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleCompte
{
    private $pdo;

    public function __construct() {
        $this->pdo = Connexion::getBdd();
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
            LEFT JOIN solde s ON b.id_buvette = s.id_buvette AND s.id_utilisateur = a.id_utilisateur
            WHERE a.id_utilisateur = ?");
        $stmt->execute([$idUser]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getHistorique($idUser)
    {
        $stmt = $this->pdo->prepare("SELECT c.*, b.nom as nom_buvette, 
            (SELECT SUM(quantite) FROM ligne_commande lc WHERE lc.id_commande = c.id_commande) as nb_articles
            FROM commande c
            JOIN une_buvette b ON c.id_buvette = b.id_buvette
            WHERE c.id_utilisateur = ? 
            AND (c.statut != 'En cours' OR c.est_paye = 1) 
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