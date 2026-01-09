<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleBuvettes {
    public function getBuvettes() {
        $pdo = Connexion::getBdd();
        $stmt = $pdo->prepare("SELECT * FROM une_buvette");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBuvettesAdherent() {
            $pdo = Connexion::getBdd();
            $stmt = $pdo->prepare("SELECT * FROM affecter WHERE id_utilisateur = :id_user");
            $stmt->bindParam(':id_user', $_SESSION['user']['id_utilisateur']);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function adhererBuvette($idBuvette, $idUtilisateur) {
        $pdo = Connexion::getBdd();
        try {
            $stmt = $pdo->prepare("INSERT INTO adhesion (id_utilisateur, id_buvette, date_adhesion) VALUES (:id_user, :id_buvette, NOW())");
            $stmt->bindParam(':id_user', $idUtilisateur);
            $stmt->bindParam(':id_buvette', $idBuvette);
            $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return;
            } else {
                throw $e;
            }
        }
    }

    public function getMembreBuvetteEnAdhesion(){
            $pdo = Connexion::getBdd();
            $stmt = $pdo->prepare("SELECT * FROM adhesion WHERE id_utilisateur = :id_user");
            $stmt->bindParam(':id_user', $_SESSION['user']['id_utilisateur']);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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