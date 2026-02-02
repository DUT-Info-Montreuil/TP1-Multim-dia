<?php
class Connexion {
    private static $bdd;

    public static function initBdd() {
        try {
            self::$bdd = new PDO("mysql:host=localhost;dbname=alacool;charset=utf8", "root", "");
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