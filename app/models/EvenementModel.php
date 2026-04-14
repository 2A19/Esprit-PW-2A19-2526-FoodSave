<?php
// ============================================================
//  app/models/EvenementModel.php — Modele PDO
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class EvenementModel {

    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findAll() {
        $stmt = $this->db->query("SELECT * FROM evenements ORDER BY date_event ASC");
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM evenements WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    public function findUpcoming($limit = 6) {
        $stmt = $this->db->prepare(
            "SELECT * FROM evenements WHERE statut IN ('upcoming','ongoing')
             ORDER BY date_event ASC LIMIT :lim"
        );
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByStatut($statut) {
        $stmt = $this->db->prepare("SELECT * FROM evenements WHERE statut = :s ORDER BY date_event ASC");
        $stmt->execute([':s' => $statut]);
        return $stmt->fetchAll();
    }

    public function search($keyword) {
        $stmt = $this->db->prepare(
            "SELECT * FROM evenements
             WHERE titre LIKE :kw OR lieu LIKE :kw OR organisateur LIKE :kw OR categorie LIKE :kw
             ORDER BY date_event ASC"
        );
        $stmt->execute([':kw' => '%'.$keyword.'%']);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $sql = "INSERT INTO evenements (titre,categorie,statut,date_event,heure,lieu,organisateur,capacite,description)
                VALUES (:titre,:categorie,:statut,:date_event,:heure,:lieu,:organisateur,:capacite,:description)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':titre'        => $data['titre'],
            ':categorie'    => $data['categorie'],
            ':statut'       => $data['statut'],
            ':date_event'   => $data['date_event'],
            ':heure'        => $data['heure'],
            ':lieu'         => $data['lieu'],
            ':organisateur' => $data['organisateur'],
            ':capacite'     => (int)$data['capacite'],
            ':description'  => $data['description'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE evenements SET
                titre=:titre, categorie=:categorie, statut=:statut,
                date_event=:date_event, heure=:heure, lieu=:lieu,
                organisateur=:organisateur, capacite=:capacite, description=:description
                WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'           => (int)$id,
            ':titre'        => $data['titre'],
            ':categorie'    => $data['categorie'],
            ':statut'       => $data['statut'],
            ':date_event'   => $data['date_event'],
            ':heure'        => $data['heure'],
            ':lieu'         => $data['lieu'],
            ':organisateur' => $data['organisateur'],
            ':capacite'     => (int)$data['capacite'],
            ':description'  => $data['description'],
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM evenements WHERE id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }

    public function countParticipants($id) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM participants WHERE evenement_id=:id AND statut!='cancelled'"
        );
        $stmt->execute([':id' => (int)$id]);
        return (int)$stmt->fetchColumn();
    }

    public function getStats() {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total,
             SUM(statut='upcoming') AS upcoming,
             SUM(statut='ongoing')  AS ongoing,
             SUM(statut='past')     AS past,
             COALESCE(SUM(capacite),0) AS total_cap
             FROM evenements"
        )->fetch();
        return [
            'total'    => (int)$row['total'],
            'upcoming' => (int)$row['upcoming'],
            'ongoing'  => (int)$row['ongoing'],
            'past'     => (int)$row['past'],
            'total_cap'=> (int)$row['total_cap'],
        ];
    }

    // Validation PHP — PAS HTML5
    public static function validate($data) {
        $errors = [];
        $titre = trim($data['titre'] ?? '');
        if ($titre === '')              $errors['titre'] = 'Le titre est obligatoire.';
        elseif (strlen($titre) < 3)    $errors['titre'] = 'Minimum 3 caracteres.';
        elseif (strlen($titre) > 150)  $errors['titre'] = 'Maximum 150 caracteres.';

        if (trim($data['categorie'] ?? '') === '')
            $errors['categorie'] = 'La categorie est obligatoire.';

        if (!in_array($data['statut'] ?? '', ['upcoming','ongoing','past']))
            $errors['statut'] = 'Statut invalide.';

        $date = $data['date_event'] ?? '';
        if ($date === '')
            $errors['date_event'] = 'La date est obligatoire.';
        elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
            $errors['date_event'] = 'Format: YYYY-MM-DD.';

        $heure = $data['heure'] ?? '';
        if ($heure === '')
            $errors['heure'] = "L'heure est obligatoire.";
        elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $heure))
            $errors['heure'] = 'Format: HH:MM.';

        if (trim($data['lieu'] ?? '') === '')
            $errors['lieu'] = 'Le lieu est obligatoire.';

        if (trim($data['organisateur'] ?? '') === '')
            $errors['organisateur'] = "L'organisateur est obligatoire.";

        $cap = $data['capacite'] ?? '';
        if ($cap === '' || !is_numeric($cap) || (int)$cap < 1)
            $errors['capacite'] = 'Capacite: nombre > 0.';

        return $errors;
    }
}
