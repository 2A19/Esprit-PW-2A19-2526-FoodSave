<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/EvenementController.php';
require_once __DIR__ . '/../models/ParticipantModel.php';

class ParticipantController
{
    private PDO $db;
    private EvenementController $evenementController;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->evenementController = new EvenementController();
    }

    // ─── SELECT de base avec toutes les infos evenement via JOIN ─────────────

    private function baseSelect(): string
    {
        return "SELECT p.*,
                       e.titre       AS ev_titre,
                       e.date_event  AS ev_date,
                       e.heure       AS ev_heure,
                       e.lieu        AS ev_lieu,
                       e.categorie   AS ev_categorie,
                       e.organisateur AS ev_organisateur,
                       e.capacite    AS ev_capacite,
                       e.statut      AS ev_statut
                FROM participants p
                LEFT JOIN evenements e ON p.evenement_id = e.id";
    }

    // ─── Lecture ─────────────────────────────────────────────────────────────

    public function findAll(): array
    {
        $stmt = $this->db->query(
            $this->baseSelect() . " ORDER BY p.date_inscription DESC"
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            $this->baseSelect() . " WHERE p.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findByEvent(int $eventId): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect() .
            " WHERE p.evenement_id = :id ORDER BY p.date_inscription DESC"
        );
        $stmt->execute([':id' => $eventId]);
        return $stmt->fetchAll();
    }

    public function search(string $keyword): array
    {
        $kw = '%' . $keyword . '%';
        $stmt = $this->db->prepare(
            $this->baseSelect() .
            " WHERE p.nom LIKE :kw1 OR p.prenom LIKE :kw2
               OR p.email LIKE :kw3 OR e.titre LIKE :kw4
              ORDER BY p.date_inscription DESC"
        );
        $stmt->execute([':kw1' => $kw, ':kw2' => $kw, ':kw3' => $kw, ':kw4' => $kw]);
        return $stmt->fetchAll();
    }

    /**
     * Méthode principale : tous les filtres combinés en une seule requête JOIN
     */
    public function listParticipants(string $search = '', string $statut = '', int $evenementId = 0): array
    {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $kw = '%' . $search . '%';
            $conditions[] = "(p.nom LIKE :kw1 OR p.prenom LIKE :kw2 OR p.email LIKE :kw3 OR e.titre LIKE :kw4)";
            $params[':kw1'] = $kw; $params[':kw2'] = $kw;
            $params[':kw3'] = $kw; $params[':kw4'] = $kw;
        }
        if ($statut !== '') {
            $conditions[] = "p.statut = :statut";
            $params[':statut'] = $statut;
        }
        if ($evenementId > 0) {
            $conditions[] = "p.evenement_id = :ev_id";
            $params[':ev_id'] = $evenementId;
        }

        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $sql   = $this->baseSelect() . $where . " ORDER BY p.date_inscription DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─── Ecriture ────────────────────────────────────────────────────────────

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO participants (nom, prenom, email, telephone, evenement_id, statut)
             VALUES (:nom, :prenom, :email, :telephone, :evenement_id, :statut)"
        );
        $stmt->execute([
            ':nom'          => trim($data['nom']),
            ':prenom'       => trim($data['prenom']),
            ':email'        => strtolower(trim($data['email'])),
            ':telephone'    => trim($data['telephone'] ?? ''),
            ':evenement_id' => (int) $data['evenement_id'],
            ':statut'       => trim($data['statut'] ?? 'pending'),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE participants SET
             nom = :nom, prenom = :prenom, email = :email,
             telephone = :telephone, evenement_id = :evenement_id, statut = :statut
             WHERE id = :id"
        );
        return $stmt->execute([
            ':id'           => $id,
            ':nom'          => trim($data['nom']),
            ':prenom'       => trim($data['prenom']),
            ':email'        => strtolower(trim($data['email'])),
            ':telephone'    => trim($data['telephone'] ?? ''),
            ':evenement_id' => (int) $data['evenement_id'],
            ':statut'       => trim($data['statut'] ?? 'pending'),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM participants WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ─── Stats & utilitaires ─────────────────────────────────────────────────

    public function emailExists(string $email, int $eventId, int $excludeId = 0): bool
    {
        if ($excludeId > 0) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM participants
                 WHERE email = :email AND evenement_id = :eventId AND id != :excludeId"
            );
            $stmt->execute([':email' => $email, ':eventId' => $eventId, ':excludeId' => $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM participants
                 WHERE email = :email AND evenement_id = :eventId"
            );
            $stmt->execute([':email' => $email, ':eventId' => $eventId]);
        }
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getStats(): array
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total,
             SUM(statut = 'confirmed') AS confirmed,
             SUM(statut = 'pending')   AS pending,
             SUM(statut = 'cancelled') AS cancelled
             FROM participants"
        )->fetch();

        return [
            'total'     => (int) $row['total'],
            'confirmed' => (int) $row['confirmed'],
            'pending'   => (int) $row['pending'],
            'cancelled' => (int) $row['cancelled'],
        ];
    }

    public function getEventList(): array
    {
        return $this->evenementController->findAll();
    }

    public function findEvent(int $id): array|false
    {
        return $this->evenementController->findById($id);
    }

    // ─── Validation ──────────────────────────────────────────────────────────

    public function validate(array $data): array
    {
        $errors = [];

        $nom = trim($data['nom'] ?? '');
        if ($nom === '')
            { $errors['nom'] = 'Le nom est obligatoire.'; }
        elseif (!preg_match('/^[a-zA-Z\x{00C0}-\x{024F}\s\-]{2,100}$/u', $nom))
            { $errors['nom'] = 'Lettres uniquement (2-100 car.).'; }

        $prenom = trim($data['prenom'] ?? '');
        if ($prenom === '')
            { $errors['prenom'] = 'Le prenom est obligatoire.'; }
        elseif (!preg_match('/^[a-zA-Z\x{00C0}-\x{024F}\s\-]{2,100}$/u', $prenom))
            { $errors['prenom'] = 'Lettres uniquement (2-100 car.).'; }

        $email = trim($data['email'] ?? '');
        if ($email === '')
            { $errors['email'] = "L'email est obligatoire."; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            { $errors['email'] = 'Format email invalide.'; }

        $telephone = trim($data['telephone'] ?? '');
        if ($telephone !== '' && !preg_match('/^[\d\s\+\-\(\)]{8,20}$/', $telephone))
            { $errors['telephone'] = 'Telephone invalide (8-20 chiffres).'; }

        $eventId = $data['evenement_id'] ?? '';
        if ($eventId === '' || (int) $eventId <= 0)
            { $errors['evenement_id'] = 'Selectionnez un evenement.'; }

        if (!in_array($data['statut'] ?? '', ['confirmed', 'pending', 'cancelled'], true))
            { $errors['statut'] = 'Statut invalide.'; }

        return $errors;
    }
}
