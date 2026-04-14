<?php
// ============================================================
//  app/models/ParticipantModel.php
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class ParticipantModel {

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findAll() {
        $stmt = $this->db->query(
            "SELECT p.*, e.titre AS ev_titre FROM participants p
             LEFT JOIN evenements e ON p.evenement_id = e.id
             ORDER BY p.date_inscription DESC"
        );
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare(
            "SELECT p.*, e.titre AS ev_titre FROM participants p
             LEFT JOIN evenements e ON p.evenement_id = e.id
             WHERE p.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    public function findByEvent($eventId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM participants WHERE evenement_id = :id ORDER BY date_inscription DESC"
        );
        $stmt->execute([':id' => (int)$eventId]);
        return $stmt->fetchAll();
    }

    public function search($keyword) {
        $stmt = $this->db->prepare(
            "SELECT p.*, e.titre AS ev_titre FROM participants p
             LEFT JOIN evenements e ON p.evenement_id = e.id
             WHERE p.nom LIKE :kw OR p.prenom LIKE :kw OR p.email LIKE :kw
             ORDER BY p.date_inscription DESC"
        );
        $stmt->execute([':kw' => '%'.$keyword.'%']);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $sql = "INSERT INTO participants (nom,prenom,email,telephone,evenement_id,statut)
                VALUES (:nom,:prenom,:email,:telephone,:evenement_id,:statut)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nom'          => $data['nom'],
            ':prenom'       => $data['prenom'],
            ':email'        => $data['email'],
            ':telephone'    => $data['telephone'] ?? '',
            ':evenement_id' => (int)$data['evenement_id'],
            ':statut'       => $data['statut'] ?? 'pending',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE participants SET
                nom=:nom, prenom=:prenom, email=:email,
                telephone=:telephone, evenement_id=:evenement_id, statut=:statut
                WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'           => (int)$id,
            ':nom'          => $data['nom'],
            ':prenom'       => $data['prenom'],
            ':email'        => $data['email'],
            ':telephone'    => $data['telephone'] ?? '',
            ':evenement_id' => (int)$data['evenement_id'],
            ':statut'       => $data['statut'] ?? 'pending',
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM participants WHERE id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }

    public function emailExists($email, $eventId, $excludeId = 0) {
        if ($excludeId > 0) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM participants WHERE email=:e AND evenement_id=:ev AND id!=:ex"
            );
            $stmt->execute([':e'=>$email,':ev'=>(int)$eventId,':ex'=>(int)$excludeId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM participants WHERE email=:e AND evenement_id=:ev"
            );
            $stmt->execute([':e'=>$email,':ev'=>(int)$eventId]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getStats() {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total,
             SUM(statut='confirmed') AS confirmed,
             SUM(statut='pending')   AS pending,
             SUM(statut='cancelled') AS cancelled
             FROM participants"
        )->fetch();
        return [
            'total'     => (int)$row['total'],
            'confirmed' => (int)$row['confirmed'],
            'pending'   => (int)$row['pending'],
            'cancelled' => (int)$row['cancelled'],
        ];
    }

    // Validation PHP — PAS HTML5
    public static function validate($data) {
        $errors = [];
        $nom = trim($data['nom'] ?? '');
        if ($nom === '')
            $errors['nom'] = 'Le nom est obligatoire.';
        elseif (!preg_match('/^[a-zA-Z\x{00C0}-\x{024F}\s\-]{2,100}$/u', $nom))
            $errors['nom'] = 'Lettres uniquement (2-100 car.).';

        $prenom = trim($data['prenom'] ?? '');
        if ($prenom === '')
            $errors['prenom'] = 'Le prenom est obligatoire.';
        elseif (!preg_match('/^[a-zA-Z\x{00C0}-\x{024F}\s\-]{2,100}$/u', $prenom))
            $errors['prenom'] = 'Lettres uniquement (2-100 car.).';

        $email = trim($data['email'] ?? '');
        if ($email === '')
            $errors['email'] = "L'email est obligatoire.";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors['email'] = "Format email invalide.";

        $tel = trim($data['telephone'] ?? '');
        if ($tel !== '' && !preg_match('/^[\d\s\+\-\(\)]{8,20}$/', $tel))
            $errors['telephone'] = 'Telephone invalide (8-20 chiffres).';

        $evId = $data['evenement_id'] ?? '';
        if ($evId === '' || (int)$evId <= 0)
            $errors['evenement_id'] = 'Selectionnez un evenement.';

        if (!in_array($data['statut'] ?? '', ['confirmed','pending','cancelled']))
            $errors['statut'] = 'Statut invalide.';

        return $errors;
    }
}
