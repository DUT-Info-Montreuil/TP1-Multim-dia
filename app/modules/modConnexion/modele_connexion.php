<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleConnexion {
    private $pdo;

    public function __construct() {
        $this->pdo = Connexion::getBdd();
    }
    public function verifierConnexion($email, $password) {

        $req = $this->pdo->prepare("
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
        $req = $this->pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
        $req->execute([$email]);
        return $req->fetchColumn() > 0;
    }

    public function inscrireUtilisateur($nom, $prenom, $email, $password) {
        $check = $this->pdo->prepare("SELECT email FROM utilisateur WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $req = $this->pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, motdepasse, solde) VALUES (?, ?, ?, ?, ?)");
            return $req->execute([$nom, $prenom, $email, $passwordHash, 0]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
