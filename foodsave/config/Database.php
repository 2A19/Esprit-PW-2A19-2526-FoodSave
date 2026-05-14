<?php
/**
 * FoodSave — Connexion PDO unifiée
 * Supporte : new Database()->connect()  (ancienne API wf)
 *        et : Database::getConnection() (nouvelle API modules Fares)
 */
class Database {

    // ---- Paramètres de connexion ----
    private static string $host    = 'localhost';
    private static string $dbname  = 'foodsave_db';
    private static string $user    = 'root';
    private static string $pass    = '';
    private static string $charset = 'utf8mb4';

    private static ?PDO $instance = null;

    /**
     * Singleton statique — utilisé par les nouveaux modules (Dechet, Collecte, Category, Metier)
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . self::$host
                 . ';dbname=' . self::$dbname
                 . ';charset=' . self::$charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, self::$user, self::$pass, $options);
            } catch (PDOException $e) {
                die('Erreur de connexion à la base de données : ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    /**
     * Méthode instance — rétrocompatibilité avec les anciens modules wf
     */
    public function connect(): PDO {
        return self::getConnection();
    }
}
