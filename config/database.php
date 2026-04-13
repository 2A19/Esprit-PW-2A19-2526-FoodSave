<?php
// ============================================================
//  config/database.php — Connexion PDO (obligatoire)
// ============================================================

define('DB_HOST',    'localhost');
define('DB_NAME',    'foodsave_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die('<div style="padding:30px;font-family:sans-serif;background:#ffebee;color:#c62828;border-left:5px solid #ef5350;border-radius:8px;margin:20px">
                    <strong>Erreur PDO :</strong> '.htmlspecialchars($e->getMessage()).'<br>
                    <small>Verifiez config/database.php</small></div>');
            }
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}
