<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/EvenementModel.php';

class EvenementController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ─── SELECT de base avec nb_participants via JOIN ───────────────────────

    private function baseSelect(): string
    {
        return "SELECT e.*,
                       COUNT(CASE WHEN p.statut != 'cancelled' THEN 1 END) AS nb_p
                FROM evenements e
                LEFT JOIN participants p ON p.evenement_id = e.id";
    }

    // ─── Lecture ────────────────────────────────────────────────────────────

    public function findAll(): array
    {
        $stmt = $this->db->query(
            $this->baseSelect() .
            " GROUP BY e.id ORDER BY e.date_event ASC"
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            $this->baseSelect() .
            " WHERE e.id = :id GROUP BY e.id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findUpcoming(int $limit = 6): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect() .
            " WHERE e.statut IN ('upcoming', 'ongoing')
              GROUP BY e.id
              ORDER BY e.date_event ASC
              LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByStatut(string $statut): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect() .
            " WHERE e.statut = :statut GROUP BY e.id ORDER BY e.date_event ASC"
        );
        $stmt->execute([':statut' => $statut]);
        return $stmt->fetchAll();
    }

    public function search(string $keyword): array
    {
        $kw = '%' . $keyword . '%';
        $stmt = $this->db->prepare(
            $this->baseSelect() .
            " WHERE e.titre LIKE :kw1
               OR e.lieu LIKE :kw2
               OR e.organisateur LIKE :kw3
               OR e.categorie LIKE :kw4
              GROUP BY e.id ORDER BY e.date_event ASC"
        );
        $stmt->execute([':kw1' => $kw, ':kw2' => $kw, ':kw3' => $kw, ':kw4' => $kw]);
        return $stmt->fetchAll();
    }

    /**
     * Méthode principale : recherche + filtre statut + filtre catégorie
     * Une seule requête SQL avec jointure, plus de N+1
     */
    public function listEvents(string $search = '', string $statut = '', string $categorie = ''): array
    {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $kw = '%' . $search . '%';
            $conditions[] = "(e.titre LIKE :kw1 OR e.lieu LIKE :kw2 OR e.organisateur LIKE :kw3 OR e.categorie LIKE :kw4)";
            $params[':kw1'] = $kw; $params[':kw2'] = $kw;
            $params[':kw3'] = $kw; $params[':kw4'] = $kw;
        }
        if ($statut !== '') {
            $conditions[] = "e.statut = :statut";
            $params[':statut'] = $statut;
        }
        if ($categorie !== '') {
            $conditions[] = "e.categorie = :categorie";
            $params[':categorie'] = $categorie;
        }

        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $sql   = $this->baseSelect() . $where . " GROUP BY e.id ORDER BY e.date_event ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─── Ecriture ────────────────────────────────────────────────────────────

    public function create(array $data): int
    {
        $data = $this->normalizeEventData($data);

        $stmt = $this->db->prepare(
            "INSERT INTO evenements (titre, categorie, statut, date_event, heure, lieu, organisateur, capacite, description)
             VALUES (:titre, :categorie, :statut, :date_event, :heure, :lieu, :organisateur, :capacite, :description)"
        );
        $stmt->execute([
            ':titre'        => $data['titre'],
            ':categorie'    => $data['categorie'],
            ':statut'       => $data['statut'],
            ':date_event'   => $data['date_event'],
            ':heure'        => $data['heure'],
            ':lieu'         => $data['lieu'],
            ':organisateur' => $data['organisateur'],
            ':capacite'     => (int) $data['capacite'],
            ':description'  => $data['description'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->normalizeEventData($data);

        $stmt = $this->db->prepare(
            "UPDATE evenements SET
             titre = :titre, categorie = :categorie, statut = :statut,
             date_event = :date_event, heure = :heure, lieu = :lieu,
             organisateur = :organisateur, capacite = :capacite, description = :description
             WHERE id = :id"
        );
        return $stmt->execute([
            ':id'           => $id,
            ':titre'        => $data['titre'],
            ':categorie'    => $data['categorie'],
            ':statut'       => $data['statut'],
            ':date_event'   => $data['date_event'],
            ':heure'        => $data['heure'],
            ':lieu'         => $data['lieu'],
            ':organisateur' => $data['organisateur'],
            ':capacite'     => (int) $data['capacite'],
            ':description'  => $data['description'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM evenements WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ─── Stats ───────────────────────────────────────────────────────────────

    public function countParticipants(int $id): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM participants WHERE evenement_id = :id AND statut != 'cancelled'"
        );
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public function getStats(): array
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total,
             SUM(statut = 'upcoming') AS upcoming,
             SUM(statut = 'ongoing')  AS ongoing,
             SUM(statut = 'past')     AS past,
             COALESCE(SUM(capacite), 0) AS total_cap
             FROM evenements"
        )->fetch();

        return [
            'total'     => (int) $row['total'],
            'upcoming'  => (int) $row['upcoming'],
            'ongoing'   => (int) $row['ongoing'],
            'past'      => (int) $row['past'],
            'total_cap' => (int) $row['total_cap'],
        ];
    }

    // ─── Validation ──────────────────────────────────────────────────────────

    public function validate(array $data): array
    {
        $errors = [];

        $titre = trim($data['titre'] ?? '');
        if ($titre === '')              { $errors['titre'] = 'Le titre est obligatoire.'; }
        elseif (strlen($titre) < 3)    { $errors['titre'] = 'Minimum 3 caracteres.'; }
        elseif (strlen($titre) > 150)  { $errors['titre'] = 'Maximum 150 caracteres.'; }

        if (trim($data['categorie'] ?? '') === '')
            { $errors['categorie'] = 'La categorie est obligatoire.'; }

        $statut = trim($data['statut'] ?? '');
        if (!in_array($statut, ['upcoming', 'ongoing', 'past'], true))
            { $errors['statut'] = 'Statut invalide.'; }

        $date = trim($data['date_event'] ?? '');
        if ($statut !== 'ongoing') {
            if ($date === '')
                { $errors['date_event'] = 'La date est obligatoire.'; }
            elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
                { $errors['date_event'] = 'Format: YYYY-MM-DD.'; }
        } elseif ($date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors['date_event'] = 'Format: YYYY-MM-DD.';
        }

        $heure = trim($data['heure'] ?? '');
        if ($heure === '')
            { $errors['heure'] = "L'heure est obligatoire."; }
        elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $heure))
            { $errors['heure'] = 'Format: HH:MM.'; }

        if (trim($data['lieu'] ?? '') === '')
            { $errors['lieu'] = 'Le lieu est obligatoire.'; }

        if (trim($data['organisateur'] ?? '') === '')
            { $errors['organisateur'] = "L'organisateur est obligatoire."; }

        $cap = $data['capacite'] ?? '';
        if ($cap === '' || !is_numeric($cap) || (int) $cap < 1)
            { $errors['capacite'] = 'Capacite: nombre > 0.'; }

        return $errors;
    }

    // ─── Compatibilité rétrograde (utilisé dans export_pdf, ev_show) ─────────

    /** @deprecated Utiliser listEvents() qui fait déjà la jointure */
    public function attachParticipantCounts(array $rows): array
    {
        // Plus besoin de N requêtes : nb_p est déjà dans les résultats de listEvents()
        // Conservé pour ne pas casser d'éventuels appels externes
        foreach ($rows as &$row) {
            if (!isset($row['nb_p'])) {
                $row['nb_p'] = $this->countParticipants((int) $row['id']);
            }
        }
        unset($row);
        return $rows;
    }

    // ─── Privé ───────────────────────────────────────────────────────────────

    private function normalizeEventData(array $data): array
    {
        $d = [
            'titre'        => trim($data['titre'] ?? ''),
            'categorie'    => trim($data['categorie'] ?? ''),
            'statut'       => trim($data['statut'] ?? ''),
            'date_event'   => trim($data['date_event'] ?? ''),
            'heure'        => trim($data['heure'] ?? ''),
            'lieu'         => trim($data['lieu'] ?? ''),
            'organisateur' => trim($data['organisateur'] ?? ''),
            'capacite'     => $data['capacite'] ?? '',
            'description'  => trim($data['description'] ?? ''),
        ];
        if ($d['statut'] === 'ongoing') {
            $d['date_event'] = date('Y-m-d');
        }
        return $d;
    }
}
