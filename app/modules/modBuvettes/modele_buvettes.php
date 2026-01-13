<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleBuvettes {


    public function getBuvettes() {
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("SELECT * FROM une_buvette ORDER BY nom");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBuvettesAdherent() {
        if (!isset($_SESSION['user']['id_utilisateur'])) return [];
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("SELECT * FROM affecter WHERE id_utilisateur = ?");
        $stmt->execute([$_SESSION['user']['id_utilisateur']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMembreBuvetteEnAdhesion() {
        if (!isset($_SESSION['user']['id_utilisateur'])) return [];
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("SELECT * FROM adhesion WHERE id_utilisateur = ?");
        $stmt->execute([$_SESSION['user']['id_utilisateur']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function adhererBuvette($idBuvette, $idUtilisateur) {
        $pdo = Connexion::getBdd();
        try {
            $sql = "INSERT INTO adhesion (id_utilisateur, id_buvette, date_adhesion) VALUES (?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idUtilisateur, $idBuvette]);

        } catch (PDOException $e) {
            if ($e->getCode() != '23000') {
                throw $e;
            }
        }
    }

    public function getBuvettesStaff() {
        if (!isset($_SESSION['user']['id_utilisateur'])) return [];

        $pdo = Connexion::getBdd();
        $sql = "SELECT * FROM affecter 
                WHERE id_utilisateur = ? 
                AND id_role IN (1, 2, 3)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user']['id_utilisateur']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}