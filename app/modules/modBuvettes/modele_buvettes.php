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
        if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id_utilisateur'])) {
            $pdo = Connexion::getBdd();
            $stmt = $pdo->prepare("SELECT * FROM affecter WHERE id_utilisateur = :id_user AND date_fin IS NULL");
            $stmt->bindParam(':id_user', $_SESSION['user']['id_utilisateur']);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

}
