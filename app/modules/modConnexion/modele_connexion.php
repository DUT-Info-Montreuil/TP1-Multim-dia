<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleConnexion {

//    public function verifierConnexion($email, $password) {
//        $bdd = Connexion::getBdd();
//        $req = $bdd->prepare("SELECT * FROM utilisateur WHERE email = ?");
//        $req->execute([$email]);
//        $user = $req->fetch(PDO::FETCH_ASSOC);
//
//        if ($user && password_verify($password, $user['motdepasse'])) {
//            return $user;
//        }
//        return false;
//    }

    public function verifierConnexion($email, $password) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
        SELECT u.*, r.nom_role 
        FROM utilisateur u
        LEFT JOIN affecter a ON u.id_utilisateur = a.id_utilisateur
        LEFT JOIN role_utilisateur r ON a.id_role = r.id_role
        WHERE u.email = ?
        LIMIT 1
    ");
        $req->execute([$email]);
        $user = $req->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['motdepasse'])) {
            return $user;
        }
        return false;
    }




    public function emailExiste($email) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
        $req->execute([$email]);
        return $req->fetchColumn() > 0;
    }

    public function inscrireUtilisateur($nom, $prenom, $email, $password) {
        $bdd = Connexion::getBdd();

        $check = $bdd->prepare("SELECT email FROM utilisateur WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $req = $bdd->prepare("INSERT INTO utilisateur (nom, prenom, email, motdepasse, solde) VALUES (?, ?, ?, ?, ?)");
            return $req->execute([$nom, $prenom, $email, $passwordHash, 0]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
