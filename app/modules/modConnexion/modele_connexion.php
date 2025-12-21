<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleConnexion {
    public function verifierConnexion($login, $password) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("SELECT * FROM utilisateur WHERE nom = ?");
        $req->execute([$login]);
        $user = $req->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['motdepasse']) {
            return $user;
        }
        return false;
    }

    public function inscrireUtilisateur($nom, $password) {
        $bdd = Connexion::getBdd();
        try {

            $req = $bdd->prepare("INSERT INTO Utilisateur ( nom, prenom, email, motdepasse, solde) VALUES ( ?, ?, ?, ?, ?)");

            return $req->execute([
                $nom,
                $nom,
                $nom . "@edu.fr",
                $password,
                0
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}