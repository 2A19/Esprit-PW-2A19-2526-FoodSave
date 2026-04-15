<?php
/**
 * FoodSave - Model: User
 */

require_once __DIR__ . '/../config/Database.php';

class User {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getAllBasic(): array {
        $sql = "SELECT id, nom, prenom, email, role FROM users ORDER BY prenom ASC, nom ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existsById(int $id): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }
}
