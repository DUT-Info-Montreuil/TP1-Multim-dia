<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleConnexion {
    public function verifierConnexion($login, $password) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("SELECT * FROM Utilisateur WHERE nom = ?");
        $req->execute([$login]);
        $user = $req->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['motdepasse']) {
            return $user;
        }
        return false;
    }

    public function inscrireUtilisateur($ine, $nom, $password, $statut) {
        $bdd = Connexion::getBdd();
        try {
            $req = $bdd->prepare("INSERT INTO Utilisateur (INE, nom, prenom, email, motdepasse, statut_universitaire, solde) VALUES (?, ?, ?, ?, ?, ?, ?)");
            return $req->execute([
                $ine,
                $nom,
                $nom,
                $nom."@edu.fr",
                $password,
                $statut,
                0
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}