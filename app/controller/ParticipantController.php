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

    public function findAll(): array
    {
        $stmt = $this->db->query(
            "SELECT p.*, e.titre AS ev_titre
             FROM participants p
             LEFT JOIN evenements e ON p.evenement_id = e.id
             ORDER BY p.date_inscription DESC"
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, e.titre AS ev_titre
             FROM participants p
             LEFT JOIN evenements e ON p.evenement_id = e.id
             WHERE p.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findByEvent(int $eventId): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, e.titre AS ev_titre
             FROM participants p
             LEFT JOIN evenements e ON p.evenement_id = e.id
             WHERE p.evenement_id = :id
             ORDER BY p.date_inscription DESC"
        );
        $stmt->execute([':id' => $eventId]);
        return $stmt->fetchAll();
    }

    public function search(string $keyword): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, e.titre AS ev_titre
             FROM participants p
             LEFT JOIN evenements e ON p.evenement_id = e.id
             WHERE p.nom LIKE :kw OR p.prenom LIKE :kw OR p.email LIKE :kw
             ORDER BY p.date_inscription DESC"
        );
        $stmt->execute([':kw' => '%' . $keyword . '%']);
        return $stmt->fetchAll();
    }

    public function listParticipants(string $search = '', string $statut = '', int $evenementId = 0): array
    {
        if ($search !== '') {
            $rows = $this->search($search);
        } elseif ($evenementId > 0) {
            $rows = $this->findByEvent($evenementId);
        } else {
            $rows = $this->findAll();
        }

        if ($statut !== '') {
            $rows = array_filter($rows, fn ($row) => $row['statut'] === $statut);
        }

        return array_values($rows);
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO participants (nom, prenom, email, telephone, evenement_id, statut)
             VALUES (:nom, :prenom, :email, :telephone, :evenement_id, :statut)"
        );

        $stmt->execute([
            ':nom' => trim($data['nom']),
            ':prenom' => trim($data['prenom']),
            ':email' => strtolower(trim($data['email'])),
            ':telephone' => trim($data['telephone'] ?? ''),
            ':evenement_id' => (int) $data['evenement_id'],
            ':statut' => trim($data['statut'] ?? 'pending'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE participants SET
             nom = :nom,
             prenom = :prenom,
             email = :email,
             telephone = :telephone,
             evenement_id = :evenement_id,
             statut = :statut
             WHERE id = :id"
        );

        return $stmt->execute([
            ':id' => $id,
            ':nom' => trim($data['nom']),
            ':prenom' => trim($data['prenom']),
            ':email' => strtolower(trim($data['email'])),
            ':telephone' => trim($data['telephone'] ?? ''),
            ':evenement_id' => (int) $data['evenement_id'],
            ':statut' => trim($data['statut'] ?? 'pending'),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM participants WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function emailExists(string $email, int $eventId, int $excludeId = 0): bool
    {
        if ($excludeId > 0) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM participants
                 WHERE email = :email AND evenement_id = :eventId AND id != :excludeId"
            );
            $stmt->execute([
                ':email' => $email,
                ':eventId' => $eventId,
                ':excludeId' => $excludeId,
            ]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM participants
                 WHERE email = :email AND evenement_id = :eventId"
            );
            $stmt->execute([
                ':email' => $email,
                ':eventId' => $eventId,
            ]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    public function getStats(): array
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total,
             SUM(statut = 'confirmed') AS confirmed,
             SUM(statut = 'pending') AS pending,
             SUM(statut = 'cancelled') AS cancelled
             FROM participants"
        )->fetch();

        return [
            'total' => (int) $row['total'],
            'confirmed' => (int) $row['confirmed'],
            'pending' => (int) $row['pending'],
            'cancelled' => (int) $row['cancelled'],
        ];
    }

    public function validate(array $data): array
    {
        $errors = [];

        $nom = trim($data['nom'] ?? '');
        if ($nom === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        } elseif (!preg_match('/^[a-zA-Z\x{00C0}-\x{024F}\s\-]{2,100}$/u', $nom)) {
            $errors['nom'] = 'Lettres uniquement (2-100 car.).';
        }

        $prenom = trim($data['prenom'] ?? '');
        if ($prenom === '') {
            $errors['prenom'] = 'Le prenom est obligatoire.';
        } elseif (!preg_match('/^[a-zA-Z\x{00C0}-\x{024F}\s\-]{2,100}$/u', $prenom)) {
            $errors['prenom'] = 'Lettres uniquement (2-100 car.).';
        }

        $email = trim($data['email'] ?? '');
        if ($email === '') {
            $errors['email'] = "L'email est obligatoire.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email invalide.';
        }

        $telephone = trim($data['telephone'] ?? '');
        if ($telephone !== '' && !preg_match('/^[\d\s\+\-\(\)]{8,20}$/', $telephone)) {
            $errors['telephone'] = 'Telephone invalide (8-20 chiffres).';
        }

        $eventId = $data['evenement_id'] ?? '';
        if ($eventId === '' || (int) $eventId <= 0) {
            $errors['evenement_id'] = 'Selectionnez un evenement.';
        }

        if (!in_array($data['statut'] ?? '', ['confirmed', 'pending', 'cancelled'], true)) {
            $errors['statut'] = 'Statut invalide.';
        }

        return $errors;
    }

    public function getEventList(): array
    {
        return $this->evenementController->findAll();
    }

    public function findEvent(int $id): array|false
    {
        return $this->evenementController->findById($id);
    }
}
