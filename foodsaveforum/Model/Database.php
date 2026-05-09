<?php
/**
 * Database Connection Class - Unified FoodSave Database
 * Used by both FoodSave and Forum applications
 * Database: foodsave_db
 */
class Database {
    private $host = 'localhost';
    private $db_name = 'foodsave_db';  // UNIFIED DATABASE
    private $user = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4',
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            echo 'Erreur de connexion à la base de données: ' . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
