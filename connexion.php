<?php
class Connexion {
    private static $bdd;

    public static function initBdd() {
        try {
            $host = '127.0.0.1';
            $port = '3307';
            $dbname = 'alacool';
            $user = 'root';
            $password = '';

            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            self::$bdd = new PDO($dsn, $user, $password);
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