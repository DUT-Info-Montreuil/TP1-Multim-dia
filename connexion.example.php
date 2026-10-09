<?php
// Modèle de connexion.php : copier en connexion.php et renseigner les vraies valeurs.
// Ne jamais committer d'identifiants réels.
class Connexion {
    private static $bdd;

    public static function initBdd() {
        try {
            $host = 'mysql:host=HOTE_MYSQL;dbname=NOM_BDD;charset=utf8';
            $user = 'UTILISATEUR';
            $password = 'MOT_DE_PASSE';

            self::$bdd = new PDO($host, $user, $password);
            self::$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage() . "<br>");
        }
    }

    public static function getBdd() {
        if (self::$bdd === null) {
            self::initBdd();
        }
        return self::$bdd;
    }
}
?>
