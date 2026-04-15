<?php
/**
 * FoodSave – Model : Dechet
 * Architecture MVC | PDO obligatoire (conforme contrainte prof)
 * Fichier : models/Dechet.php
 */

require_once __DIR__ . '/../config/Database.php';

class Dechet {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /* =========================================================
       CREATE — Ajouter un déchet
    ========================================================= */
    public function create(array $data): bool {
        $sql = "INSERT INTO dechets (user_id, type_aliment, quantite, unite, date_dechet, raison, notes)
                VALUES (:user_id, :type_aliment, :quantite, :unite, :date_dechet, :raison, :notes)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id'      => $data['user_id'],
            ':type_aliment' => $data['type_aliment'],
            ':quantite'     => $data['quantite'],
            ':unite'        => $data['unite'],
            ':date_dechet'  => $data['date_dechet'],
            ':raison'       => $data['raison'],
            ':notes'        => $data['notes'] ?? '',
        ]);
    }

    /* =========================================================
       READ ALL — Lister tous les déchets (avec pagination)
    ========================================================= */
    public function getAll(int $limit = 50, int $offset = 0): array {
        $sql = "SELECT d.*, u.nom, u.prenom
                FROM dechets d
                JOIN users u ON u.id = d.user_id
                ORDER BY d.date_dechet DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       READ BY USER — Déchets d'un utilisateur
    ========================================================= */
    public function getByUser(int $userId): array {
        $sql = "SELECT * FROM dechets WHERE user_id = :user_id ORDER BY date_dechet DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       READ ONE — Un déchet par ID
    ========================================================= */
    public function getById(int $id): array|false {
        $sql = "SELECT * FROM dechets WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       UPDATE — Modifier un déchet
    ========================================================= */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE dechets SET
                    type_aliment = :type_aliment,
                    quantite     = :quantite,
                    unite        = :unite,
                    date_dechet  = :date_dechet,
                    raison       = :raison,
                    notes        = :notes
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':type_aliment' => $data['type_aliment'],
            ':quantite'     => $data['quantite'],
            ':unite'        => $data['unite'],
            ':date_dechet'  => $data['date_dechet'],
            ':raison'       => $data['raison'],
            ':notes'        => $data['notes'] ?? '',
            ':id'           => $id,
        ]);
    }

    /* =========================================================
       DELETE — Supprimer un déchet
    ========================================================= */
    public function delete(int $id): bool {
        $sql = "DELETE FROM dechets WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /* =========================================================
       STATS — Statistiques pour le dashboard
    ========================================================= */
    public function getStats(int $userId): array {
        $sql = "SELECT
                    COUNT(*)        AS total_entrees,
                    SUM(quantite)   AS total_quantite,
                    AVG(quantite)   AS moyenne_quantite,
                    MAX(date_dechet) AS derniere_date
                FROM dechets
                WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       SEARCH — Filtrage avancé
    ========================================================= */
    public function search(array $filters): array {
        $conditions = ['1=1'];
        $params = [];

        if (!empty($filters['type'])) {
            $conditions[] = 'type_aliment = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['raison'])) {
            $conditions[] = 'raison = :raison';
            $params[':raison'] = $filters['raison'];
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = 'date_dechet >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = 'date_dechet <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }
        if (!empty($filters['user_id'])) {
            $conditions[] = 'user_id = :user_id';
            $params[':user_id'] = $filters['user_id'];
        }

        $where = implode(' AND ', $conditions);
        $sql = "SELECT * FROM dechets WHERE {$where} ORDER BY date_dechet DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
