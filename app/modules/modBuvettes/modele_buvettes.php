<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleBuvettes {
    // Récupère les buvettes séparées en deux groupes pour un utilisateur
    public function getBuvettesParAcces($idUser) {
        $pdo = Connexion::getBdd();

        // 1. Buvettes où l'utilisateur a un rôle (Staff)
        $sqlAcces = "SELECT b.* FROM une_buvette b 
                     JOIN affecter a ON b.id_buvette = a.id_buvette 
                     WHERE a.id_utilisateur = ?";
        $stmt = $pdo->prepare($sqlAcces);
        $stmt->execute([$idUser]);
        $avecAcces = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Buvettes où il n'a aucun rôle (Client uniquement)
        $sqlSans = "SELECT * FROM une_buvette WHERE id_buvette NOT IN 
                    (SELECT id_buvette FROM affecter WHERE id_utilisateur = ?)";
        $stmt = $pdo->prepare($sqlSans);
        $stmt->execute([$idUser]);
        $sansAcces = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['autorisees' => $avecAcces, 'autres' => $sansAcces];
    }
    public function rejoindreEquipe($idUser, $idBuvette) {
        $pdo = Connexion::getBdd();
        // On vérifie si déjà membre pour éviter les doublons
        $check = $pdo->prepare("SELECT * FROM affecter WHERE id_utilisateur = ? AND id_buvette = ?");
        $check->execute([$idUser, $idBuvette]);

        if ($check->rowCount() == 0) {
            // On l'ajoute avec le rôle Barman (id_role 3 d'après ton script précédent)
            $sql = "INSERT INTO affecter (id_role, id_utilisateur, id_buvette, date_debut) VALUES (3, ?, ?, CURDATE())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idUser, $idBuvette]);
        }
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
    }

}
