<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleSolde
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Connexion::getBdd();

    }

    public function ajouterSolde($idUser, $idBuvette, $montant)
    {
        try {
            $this->pdo->beginTransaction();
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

            $checkTres = $this->pdo->prepare("SELECT id_tresorerie FROM tresorerie WHERE id_buvette = ?");
            $checkTres->execute([$idBuvette]);

            if ($checkTres->fetch()) {
                $updTres = $this->pdo->prepare("UPDATE tresorerie SET solde_actuel = solde_actuel + ?, derniere_maj = NOW() WHERE id_buvette = ?");
                $updTres->execute([$montant, $idBuvette]);
            } else {
                $insTres = $this->pdo->prepare("INSERT INTO tresorerie (id_buvette, solde_actuel) VALUES (?, ?)");
                $insTres->execute([$idBuvette, $montant]);
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
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
}