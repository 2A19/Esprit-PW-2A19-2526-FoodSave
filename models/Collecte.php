<?php
/**
 * FoodSave – Model : Collecte
 * Architecture MVC | PDO obligatoire
 * Fichier : models/Collecte.php
 */

require_once __DIR__ . '/../config/Database.php';

class Collecte {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /* =========================================================
       CREATE — Créer une collecte (sans user_id)
    ========================================================= */
    public function create(array $data): int|false {
        $sql = "INSERT INTO collectes (titre, description, date_collecte, lieu, quantite_totale, unite, statut)
                VALUES (:titre, :description, :date_collecte, :lieu, :quantite_totale, :unite, :statut)";
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute([
            ':titre'           => $data['titre'],
            ':description'     => $data['description']     ?? '',
            ':date_collecte'   => $data['date_collecte'],
            ':lieu'            => $data['lieu'],
            ':quantite_totale' => $data['quantite_totale'] ?? 0,
            ':unite'           => $data['unite']           ?? 'kg',
            ':statut'          => $data['statut']          ?? 'planifiee',
        ]);
        return $ok ? (int) $this->pdo->lastInsertId() : false;
    }

    /* =========================================================
       READ ALL
    ========================================================= */
    public function getAll(int $limit = 500, int $offset = 0): array {
        $sql = "SELECT * FROM collectes ORDER BY date_collecte DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       READ ONE
    ========================================================= */
    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM collectes WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $collecte = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($collecte) {
            $collecte['dechets'] = $this->getDechetsById($id);
        }
        return $collecte;
    }

    /* =========================================================
       READ — Déchets rattachés
    ========================================================= */
    public function getDechetsById(int $collecteId): array {
        $sql = "SELECT d.* FROM collecte_dechets cd
                JOIN dechets d ON d.id = cd.dechet_id
                WHERE cd.collecte_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $collecteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       UPDATE
    ========================================================= */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE collectes SET
                    titre           = :titre,
                    description     = :description,
                    date_collecte   = :date_collecte,
                    lieu            = :lieu,
                    quantite_totale = :quantite_totale,
                    unite           = :unite,
                    statut          = :statut
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':titre'           => $data['titre'],
            ':description'     => $data['description']     ?? '',
            ':date_collecte'   => $data['date_collecte'],
            ':lieu'            => $data['lieu'],
            ':quantite_totale' => $data['quantite_totale'] ?? 0,
            ':unite'           => $data['unite']           ?? 'kg',
            ':statut'          => $data['statut']          ?? 'planifiee',
            ':id'              => $id,
        ]);
    }

    /* =========================================================
       UPDATE STATUT
    ========================================================= */
    public function updateStatut(int $id, string $statut): bool {
        $allowed = ['planifiee', 'en_cours', 'terminee', 'annulee'];
        if (!in_array($statut, $allowed, true)) return false;
        $stmt = $this->pdo->prepare("UPDATE collectes SET statut = :statut WHERE id = :id");
        return $stmt->execute([':statut' => $statut, ':id' => $id]);
    }

    /* =========================================================
       DELETE
    ========================================================= */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM collectes WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /* =========================================================
       PIVOT — Rattacher / retirer un déchet
    ========================================================= */
    public function addDechet(int $collecteId, int $dechetId): bool {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO collecte_dechets (collecte_id, dechet_id) VALUES (:c, :d)");
        return $stmt->execute([':c' => $collecteId, ':d' => $dechetId]);
    }

    public function removeDechet(int $collecteId, int $dechetId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM collecte_dechets WHERE collecte_id = :c AND dechet_id = :d");
        return $stmt->execute([':c' => $collecteId, ':d' => $dechetId]);
    }

    /* =========================================================
       SEARCH — Filtrage avancé
    ========================================================= */
    public function search(array $filters): array {
        $conditions = ['1=1'];
        $params = [];

        if (!empty($filters['statut'])) {
            $conditions[] = 'statut = :statut';
            $params[':statut'] = $filters['statut'];
        }
        if (!empty($filters['lieu'])) {
            $conditions[] = 'lieu LIKE :lieu';
            $params[':lieu'] = '%' . $filters['lieu'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = 'date_collecte >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = 'date_collecte <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }

        $where = implode(' AND ', $conditions);
        $sql = "SELECT * FROM collectes WHERE {$where} ORDER BY date_collecte DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================================
       STATS
    ========================================================= */
    public function getStats(): array {
        $sql = "SELECT
                    COUNT(*)                   AS total_collectes,
                    SUM(quantite_totale)       AS total_quantite,
                    SUM(statut='terminee')     AS terminees,
                    SUM(statut='en_cours')     AS en_cours,
                    SUM(statut='planifiee')    AS planifiees,
                    SUM(statut='annulee')      AS annulees
                FROM collectes";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    }
}
