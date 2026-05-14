<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/EvenementController.php';

class ParticipantController
{
    private PDO $db;
    private EvenementController $evCtrl;

    public function __construct()
    {
        $this->db     = config::getConnexion();
        $this->evCtrl = new EvenementController();
    }

    private function baseSelect(): string
    {
        return "SELECT p.*,
                       e.titre        AS ev_titre,
                       e.date_event   AS ev_date,
                       e.heure        AS ev_heure,
                       e.lieu         AS ev_lieu,
                       e.categorie    AS ev_categorie,
                       e.organisateur AS ev_organisateur,
                       e.capacite     AS ev_capacite,
                       e.statut       AS ev_statut
                FROM participants p
                LEFT JOIN evenements e ON p.evenement_id = e.id";
    }

    public function findAll(): array
    {
        return $this->db->query(
            $this->baseSelect() . " ORDER BY p.nom ASC, p.prenom ASC"
        )->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare($this->baseSelect() . " WHERE p.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findByEvent(int $eventId): array
    {
        $stmt = $this->db->prepare(
            $this->baseSelect() . " WHERE p.evenement_id = :id ORDER BY p.nom ASC, p.prenom ASC"
        );
        $stmt->execute([':id' => $eventId]);
        return $stmt->fetchAll();
    }

    public function search(string $keyword): array
    {
        $kw   = '%' . $keyword . '%';
        $stmt = $this->db->prepare(
            $this->baseSelect() .
            " WHERE p.nom LIKE :kw1 OR p.prenom LIKE :kw2
               OR p.email LIKE :kw3 OR e.titre LIKE :kw4
              ORDER BY p.nom ASC, p.prenom ASC"
        );
        $stmt->execute([':kw1' => $kw, ':kw2' => $kw, ':kw3' => $kw, ':kw4' => $kw]);
        return $stmt->fetchAll();
    }

    public function listParticipants(
        string $search      = '',
        string $statut      = '',
        int    $evenementId = 0,
        string $sort        = 'date_inscription',
        string $dir         = 'DESC'
    ): array {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $kw = '%' . $search . '%';
            $conditions[] = "(p.nom LIKE :kw1 OR p.prenom LIKE :kw2 OR p.email LIKE :kw3 OR e.titre LIKE :kw4)";
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
            $params[':kw4'] = $kw;
        }
        if ($statut !== '') {
            $conditions[] = "p.statut = :statut";
            $params[':statut'] = $statut;
        }
        if ($evenementId > 0) {
            $conditions[] = "p.evenement_id = :ev_id";
            $params[':ev_id'] = $evenementId;
        }

        $allowed = ['nom', 'prenom', 'email', 'statut', 'date_inscription', 'ev_titre'];
        if (!in_array($sort, $allowed, true)) {
            $sort = 'date_inscription';
        }
        $dir     = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
        $sortCol = ($sort === 'ev_titre') ? 'e.titre' : ('p.' . $sort);

        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $sql   = $this->baseSelect() . $where . " ORDER BY {$sortCol} {$dir}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

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

    public function emailExists(string $email, int $eventId, int $excludeId = 0): bool
    {
        if ($excludeId > 0) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM participants WHERE email = :email AND evenement_id = :ev AND id != :ex"
            );
            $stmt->execute([':email' => $email, ':ev' => $eventId, ':ex' => $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM participants WHERE email = :email AND evenement_id = :ev"
            );
            $stmt->execute([':email' => $email, ':ev' => $eventId]);
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
        return $this->evCtrl->findAll();
    }

    public function findEvent(int $id)
    {
        return $this->evCtrl->findById($id);
    }

    public function validate(array $data): array
    {
        $errors = [];

        $nom = trim($data['nom'] ?? '');
        if ($nom === '')
            { $errors['nom'] = 'Le nom est obligatoire.'; }
        elseif (strlen($nom) < 2 || strlen($nom) > 100)
            { $errors['nom'] = 'Le nom doit avoir entre 2 et 100 caracteres.'; }

        $prenom = trim($data['prenom'] ?? '');
        if ($prenom === '')
            { $errors['prenom'] = 'Le prenom est obligatoire.'; }
        elseif (strlen($prenom) < 2 || strlen($prenom) > 100)
            { $errors['prenom'] = 'Le prenom doit avoir entre 2 et 100 caracteres.'; }

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

    // ========== ROUTER METHODS ==========

    public function adminList(): void
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
            $this->delete((int) $_POST['id']);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Participant supprime.'];
            header('Location: admin.php?action=participants');
            exit;
        }

        $search = trim($_GET['search'] ?? '');
        $statut = $_GET['statut'] ?? '';
        $eventId = isset($_GET['evenement_id']) ? (int)$_GET['evenement_id'] : 0;
        $sort   = $_GET['sort'] ?? 'date_inscription';
        $dir    = ($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $rows = $this->listParticipants($search, $statut, $eventId, $sort, $dir);
        $stats = $this->getStats();
        $events = $this->getEventList();
        $plabels = ['confirmed' => 'Confirme', 'pending' => 'En attente', 'cancelled' => 'Annule'];
        $pbadge  = ['confirmed' => 'badge-green', 'pending' => 'badge-orange', 'cancelled' => 'badge-gray'];
        $slabels = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
        $sbadge  = ['upcoming' => 'badge-green', 'ongoing' => 'badge-orange', 'past' => 'badge-gray'];
        include __DIR__ . '/../View/Back/evenement/participants.php';
    }

    public function adminForm(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $errors = [];
        $p = null;

        if ($id > 0) {
            $p = $this->findById($id);
            if (!$p) { header('Location: admin.php?action=participants'); exit; }
        }

        $events = $this->getEventList();
        $selectedEventId = isset($_GET['evenement_id']) ? (int)$_GET['evenement_id'] : ($p['evenement_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $errors = $this->validate($data);
            if (empty($errors)) {
                if ($this->emailExists($data['email'], (int)$data['evenement_id'], $id)) {
                    $errors['email'] = 'Cet email est deja inscrit a cet evenement.';
                } else {
                    if ($id > 0) {
                        $this->update($id, $data);
                        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Participant mis a jour.'];
                    } else {
                        $this->create($data);
                        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Participant cree.'];
                    }
                    header('Location: admin.php?action=participants');
                    exit;
                }
            }
        }

        $plabels = ['confirmed' => 'Confirme', 'pending' => 'En attente', 'cancelled' => 'Annule'];
        include __DIR__ . '/../View/Back/evenement/p_form.php';
    }
}
