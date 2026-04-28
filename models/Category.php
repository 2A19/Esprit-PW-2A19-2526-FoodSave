<?php
/**
 * FoodSave – Model : Category
 * Architecture MVC | PDO obligatoire (conforme contrainte prof)
 * Fichier : models/Category.php
 */

require_once __DIR__ . '/../config/Database.php';

class Category {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /* =========================================================
       CREATE — Ajouter une catégorie
    ========================================================= */
    public function create(array $data): bool {
        $sql = "INSERT INTO categories (nom, description, couleur, icone)
                VALUES (:nom, :description, :couleur, :icone)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom'         => $data['nom'],
            ':description' => $data['description'] ?? '',
            ':couleur'     => $data['couleur']     ?? '#28a745',
            ':icone'       => $data['icone']       ?? 'tag',
        ]);
    }

    /* =========================================================
       READ ALL — Toutes les catégories
    ========================================================= */
    public function getAll(): array {
        $sql = "SELECT c.*,
                       COUNT(d.id) AS nombre_dechets
                FROM categories c
                LEFT JOIN dechets d ON d.categorie_id = c.id
                GROUP BY c.id
                ORDER BY c.nom ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       READ ONE — Catégorie par ID
    ========================================================= */
    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       READ — Liste simplifiée pour les <select>
    ========================================================= */
    public function getAllSimple(): array {
        $sql = "SELECT id, nom, couleur, icone FROM categories ORDER BY nom ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       UPDATE — Modifier une catégorie
    ========================================================= */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE categories SET
                    nom         = :nom,
                    description = :description,
                    couleur     = :couleur,
                    icone       = :icone
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom'         => $data['nom'],
            ':description' => $data['description'] ?? '',
            ':couleur'     => $data['couleur']     ?? '#28a745',
            ':icone'       => $data['icone']       ?? 'tag',
            ':id'          => $id,
        ]);
    }

    /* =========================================================
       DELETE — Supprimer une catégorie
       (les déchets liés auront categorie_id = NULL via ON DELETE SET NULL)
    ========================================================= */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /* =========================================================
       EXISTS — Vérifier l'existence
    ========================================================= */
    public function existsById(int $id): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    /* =========================================================
       STATS — Déchets par catégorie
    ========================================================= */
    public function getStats(): array {
        $sql = "SELECT c.nom,
                       c.couleur,
                       COUNT(d.id)       AS total_entrees,
                       SUM(d.quantite)   AS total_quantite
                FROM categories c
                LEFT JOIN dechets d ON d.categorie_id = c.id
                GROUP BY c.id
                ORDER BY total_quantite DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
