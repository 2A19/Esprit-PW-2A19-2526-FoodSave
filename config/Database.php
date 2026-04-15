<?php
/**
 * FoodSave – Connexion PDO
 * PDO OBLIGATOIRE (conforme contrainte prof)
 * Fichier : config/Database.php
 */

class Database {

    private static ?PDO $instance = null;

    // ---- Paramètres de connexion ----
    private static string $host   = 'localhost';
    private static string $dbname = 'foodsave_db';
    private static string $user   = 'root';
    private static string $pass   = '';
    private static string $charset = 'utf8mb4';

    /**
     * Singleton PDO — une seule connexion par requête
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::$host
                 . ";dbname=" . self::$dbname
                 . ";charset=" . self::$charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, self::$user, self::$pass, $options);
            } catch (PDOException $e) {
                // En production : logger l'erreur, ne pas l'afficher
                die('Erreur de connexion à la base de données : ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    // Empêcher l'instanciation directe
    private function __construct() {}
    private function __clone() {}
}
