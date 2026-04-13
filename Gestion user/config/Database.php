<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'foodsave_db';
    private $user = 'root';
    private $password = '';
    private $connection;

    public function connect() {
        $this->connection = null;

        try {
            $this->connection = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->user,
                $this->password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Erreur de connexion: ' . $e->getMessage();
        }

        return $this->connection;
    }
}
?>
