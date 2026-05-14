<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../Model/Evenement.php';

class EvenementController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    private function baseSelect(): string
    {
        return "SELECT e.*,
                       COUNT(CASE WHEN p.statut != 'cancelled' THEN 1 END) AS nb_p
                FROM evenements e
                LEFT JOIN participants p ON p.evenement_id = e.id";
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            $this->baseSelect() .
            " GROUP BY e.id ORDER BY e.titre ASC"
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
              ORDER BY e.titre ASC
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
            " WHERE e.statut = :statut GROUP BY e.id ORDER BY e.titre ASC"
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
              GROUP BY e.id ORDER BY e.titre ASC"
        );
        $stmt->execute([':kw1' => $kw, ':kw2' => $kw, ':kw3' => $kw, ':kw4' => $kw]);
        return $stmt->fetchAll();
    }

    public function listEvents(string $search = '', string $statut = '', string $categorie = '', string $sort = 'date_event', string $dir = 'asc'): array
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

        $allowed = ['titre','categorie','date_event','lieu','organisateur','capacite','statut'];
        if (!in_array($sort, $allowed)) $sort = 'date_event';
        $dir   = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $sql   = $this->baseSelect() . $where . " GROUP BY e.id ORDER BY e.{$sort} {$dir}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

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

    public function attachParticipantCounts(array $rows): array
    {
        foreach ($rows as &$row) {
            if (!isset($row['nb_p'])) {
                $row['nb_p'] = $this->countParticipants((int) $row['id']);
            }
        }
        unset($row);
        return $rows;
    }

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

    // ========== ROUTER METHODS ==========

    public function frontList(): void
    {
        $search    = trim($_GET['search'] ?? '');
        $statut    = $_GET['statut'] ?? '';
        $categorie = $_GET['categorie'] ?? '';
        $rows = $this->listEvents($search, $statut, $categorie);
        $slabels  = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
        $sbadge   = ['upcoming' => 'badge-green', 'ongoing' => 'badge-orange', 'past' => 'badge-gray'];
        include __DIR__ . '/../View/Front/evenement/list.php';
    }

    public function frontDetail(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { header('Location: index.php?action=evenements'); exit; }
        $ev = $this->findById($id);
        if (!$ev) { header('Location: index.php?action=evenements'); exit; }
        $nbParticipants = $this->countParticipants($id);
        $slabels  = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
        $sbadge   = ['upcoming' => 'badge-green', 'ongoing' => 'badge-orange', 'past' => 'badge-gray'];
        include __DIR__ . '/../View/Front/evenement/detail.php';
    }

    public function frontInscription(): void
    {
        require_once __DIR__ . '/ParticipantController.php';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { header('Location: index.php?action=evenements'); exit; }
        $ev = $this->findById($id);
        if (!$ev) { header('Location: index.php?action=evenements'); exit; }
        $pCtrl = new ParticipantController();

        $errors = [];
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom'          => $_POST['nom'] ?? '',
                'prenom'       => $_POST['prenom'] ?? '',
                'email'        => $_POST['email'] ?? '',
                'telephone'    => $_POST['telephone'] ?? '',
                'evenement_id' => $id,
                'statut'       => 'confirmed',
            ];
            $errors = $pCtrl->validate($data);
            if (empty($errors)) {
                if ($pCtrl->emailExists($data['email'], $id)) {
                    $errors['email'] = 'Cet email est deja inscrit a cet evenement.';
                } else {
                    $pCtrl->create($data);
                    $success = true;
                }
            }
        }

        $slabels  = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
        $sbadge   = ['upcoming' => 'badge-green', 'ongoing' => 'badge-orange', 'past' => 'badge-gray'];
        include __DIR__ . '/../View/Front/evenement/inscription.php';
    }

    public function adminList(): void
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
            $this->delete((int) $_POST['id']);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Evenement supprime.'];
            header('Location: admin.php?action=evenements');
            exit;
        }

        $search = trim($_GET['search'] ?? '');
        $statut = $_GET['statut'] ?? '';
        $sort   = $_GET['sort'] ?? 'titre';
        $dir    = ($_GET['dir'] ?? 'asc') === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['titre','categorie','date_event','lieu','organisateur','capacite','statut'];
        if (!in_array($sort, $allowedSort)) $sort = 'date_event';

        $rows  = $this->listEvents($search, $statut, '', $sort, $dir);
        $stats = $this->getStats();
        $slabels = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
        $sbadge  = ['upcoming' => 'badge-green', 'ongoing' => 'badge-orange', 'past' => 'badge-gray'];
        include __DIR__ . '/../View/Back/evenement/list.php';
    }

    public function adminForm(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $errors = [];
        $ev = null;

        if ($id > 0) {
            $ev = $this->findById($id);
            if (!$ev) { header('Location: admin.php?action=evenements'); exit; }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $errors = $this->validate($data);
            if (empty($errors)) {
                if ($id > 0) {
                    $this->update($id, $data);
                    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Evenement mis a jour.'];
                } else {
                    $this->create($data);
                    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Evenement cree.'];
                }
                header('Location: admin.php?action=evenements');
                exit;
            }
        }

        include __DIR__ . '/../View/Back/evenement/form.php';
    }

    public function adminShow(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { header('Location: admin.php?action=evenements'); exit; }
        $ev = $this->findById($id);
        if (!$ev) { header('Location: admin.php?action=evenements'); exit; }

        require_once __DIR__ . '/ParticipantController.php';
        $pCtrl = new ParticipantController();
        $participants = $pCtrl->findByEvent($id);
        $nbParticipants = $this->countParticipants($id);
        $slabels = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
        $sbadge  = ['upcoming' => 'badge-green', 'ongoing' => 'badge-orange', 'past' => 'badge-gray'];
        $plabels = ['confirmed' => 'Confirme', 'pending' => 'En attente', 'cancelled' => 'Annule'];
        $pbadge  = ['confirmed' => 'badge-green', 'pending' => 'badge-orange', 'cancelled' => 'badge-gray'];
        include __DIR__ . '/../View/Back/evenement/show.php';
    }

    public function adminStats(): void
    {
        $stats = $this->getStats();
        $events = $this->findAll();

        require_once __DIR__ . '/ParticipantController.php';
        $pCtrl = new ParticipantController();
        $pstats = $pCtrl->getStats();

        $slabels = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
        $sbadge  = ['upcoming' => 'badge-green', 'ongoing' => 'badge-orange', 'past' => 'badge-gray'];
        include __DIR__ . '/../View/Back/evenement/stats.php';
    }

    public function exportPdf(): void
    {
        $type = $_GET['type'] ?? 'evenements';
        $slabels = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
        $sbadge  = ['upcoming' => 'badge-green', 'ongoing' => 'badge-orange', 'past' => 'badge-gray'];
        $plabels = ['confirmed' => 'Confirme', 'pending' => 'En attente', 'cancelled' => 'Annule'];
        $pbadge  = ['confirmed' => 'badge-green', 'pending' => 'badge-orange', 'cancelled' => 'badge-gray'];

        if ($type === 'evenements') {
            $rows = $this->listEvents();
            include __DIR__ . '/../View/Back/evenement/export_pdf.php';
        } elseif ($type === 'evenement' && isset($_GET['id'])) {
            $ev = $this->findById((int)$_GET['id']);
            if (!$ev) { header('Location: admin.php?action=evenements'); exit; }
            require_once __DIR__ . '/ParticipantController.php';
            $pCtrl = new ParticipantController();
            $participants = $pCtrl->findByEvent((int)$_GET['id']);
            include __DIR__ . '/../View/Back/evenement/export_pdf.php';
        } elseif ($type === 'participants') {
            require_once __DIR__ . '/ParticipantController.php';
            $pCtrl = new ParticipantController();
            $rows = $pCtrl->findAll();
            include __DIR__ . '/../View/Back/evenement/export_pdf.php';
        } elseif ($type === 'stats') {
            $stats = $this->getStats();
            $events = $this->findAll();
            require_once __DIR__ . '/ParticipantController.php';
            $pCtrl = new ParticipantController();
            $pstats = $pCtrl->getStats();
            include __DIR__ . '/../View/Back/evenement/export_pdf.php';
        }
    }
}
