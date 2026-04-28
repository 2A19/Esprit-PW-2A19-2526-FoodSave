<?php
/**
 * FoodSave – Controller : CollecteController
 * Architecture MVC | Fichier : controllers/CollecteController.php
 */

require_once __DIR__ . '/../models/Collecte.php';
require_once __DIR__ . '/../models/User.php';

class CollecteController {

    private Collecte $model;
    private User     $userModel;

    public function __construct() {
        $this->model     = new Collecte();
        $this->userModel = new User();
    }

    /* =========================================================
       ACTION : Liste des collectes (FrontOffice)
    ========================================================= */
    public function index(): void {
        $userId   = $_SESSION['user_id'] ?? 0;
        $collectes = $this->model->getByUser($userId);
        $stats     = $this->model->getStats();
        require_once __DIR__ . '/../views/frontoffice/collecte/index.php';
    }

    /* =========================================================
       ACTION : Formulaire création
    ========================================================= */
    public function create(): void {
        require_once __DIR__ . '/../views/frontoffice/collecte/create.php';
    }

    /* =========================================================
       ACTION : Enregistrer (POST)
    ========================================================= */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=collecte&action=create');
            exit;
        }

        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            header('Location: index.php?controller=collecte&action=create');
            exit;
        }

        $data = [
            'user_id'         => $_SESSION['user_id'],
            'titre'           => $this->sanitize($_POST['titre']),
            'description'     => $this->sanitize($_POST['description'] ?? ''),
            'date_collecte'   => $this->sanitize($_POST['date_collecte']),
            'lieu'            => $this->sanitize($_POST['lieu']),
            'quantite_totale' => (float) ($_POST['quantite_totale'] ?? 0),
            'unite'           => $this->sanitize($_POST['unite'] ?? 'kg'),
            'statut'          => $this->sanitize($_POST['statut'] ?? 'planifiee'),
        ];

        $newId = $this->model->create($data);

        if ($newId) {
            $_SESSION['success'] = '✅ Collecte créée avec succès !';
        } else {
            $_SESSION['error'] = '❌ Une erreur est survenue.';
        }

        header('Location: index.php?controller=collecte&action=index');
        exit;
    }

    /* =========================================================
       ACTION : Détail d'une collecte
    ========================================================= */
    public function show(): void {
        $id      = (int) ($_GET['id'] ?? 0);
        $collecte = $this->model->getById($id);

        if (!$collecte) {
            $_SESSION['error'] = 'Collecte introuvable.';
            header('Location: index.php?controller=collecte&action=index');
            exit;
        }

        require_once __DIR__ . '/../views/frontoffice/collecte/show.php';
    }

    /* =========================================================
       ACTION : Formulaire modification
    ========================================================= */
    public function edit(): void {
        $id      = (int) ($_GET['id'] ?? 0);
        $collecte = $this->model->getById($id);

        if (!$collecte) {
            $_SESSION['error'] = 'Collecte introuvable.';
            header('Location: index.php?controller=collecte&action=index');
            exit;
        }

        require_once __DIR__ . '/../views/frontoffice/collecte/edit.php';
    }

    /* =========================================================
       ACTION : Mettre à jour (POST)
    ========================================================= */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=collecte&action=index');
            exit;
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            header("Location: index.php?controller=collecte&action=edit&id={$id}");
            exit;
        }

        $data = [
            'titre'           => $this->sanitize($_POST['titre']),
            'description'     => $this->sanitize($_POST['description'] ?? ''),
            'date_collecte'   => $this->sanitize($_POST['date_collecte']),
            'lieu'            => $this->sanitize($_POST['lieu']),
            'quantite_totale' => (float) ($_POST['quantite_totale'] ?? 0),
            'unite'           => $this->sanitize($_POST['unite'] ?? 'kg'),
            'statut'          => $this->sanitize($_POST['statut'] ?? 'planifiee'),
        ];

        if ($this->model->update($id, $data)) {
            $_SESSION['success'] = '✅ Collecte modifiée avec succès !';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la mise à jour.';
        }

        header('Location: index.php?controller=collecte&action=index');
        exit;
    }

    /* =========================================================
       ACTION : Changer le statut (POST)
    ========================================================= */
    public function updateStatut(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=collecte&action=index');
            exit;
        }

        $id     = (int) ($_POST['id']     ?? 0);
        $statut = $this->sanitize($_POST['statut'] ?? '');

        if ($this->model->updateStatut($id, $statut)) {
            $_SESSION['success'] = '✅ Statut mis à jour.';
        } else {
            $_SESSION['error'] = '❌ Statut invalide ou erreur.';
        }

        header('Location: index.php?controller=collecte&action=index');
        exit;
    }

    /* =========================================================
       ACTION : Supprimer
    ========================================================= */
    public function delete(): void {
        $id = (int) ($_GET['id'] ?? 0);

        if ($this->model->delete($id)) {
            $_SESSION['success'] = '🗑️ Collecte supprimée.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la suppression.';
        }

        header('Location: index.php?controller=collecte&action=index');
        exit;
    }

    /* =========================================================
       ACTION : Back Office — liste admin
    ========================================================= */
    public function adminIndex(): void {
        $filters = [
            'statut'    => $_GET['statut']    ?? '',
            'lieu'      => $_GET['lieu']      ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to']   ?? '',
        ];
        $collectes = $this->model->search($filters);
        $stats     = $this->model->getStats();
        require_once __DIR__ . '/../views/backoffice/collecte/index.php';
    }

    /* =========================================================
       PRIVATE : Validation
    ========================================================= */
    private function validate(array $data): array {
        $errors = [];

        if (empty($data['titre'])) {
            $errors['titre'] = 'Le titre est obligatoire.';
        } elseif (mb_strlen($data['titre']) > 150) {
            $errors['titre'] = 'Le titre ne peut pas dépasser 150 caractères.';
        }

        if (empty($data['date_collecte'])) {
            $errors['date_collecte'] = 'La date est obligatoire.';
        } elseif (strtotime($data['date_collecte']) === false) {
            $errors['date_collecte'] = 'Format de date invalide.';
        }

        if (empty($data['lieu'])) {
            $errors['lieu'] = 'Le lieu est obligatoire.';
        }

        if (!empty($data['quantite_totale'])) {
            $q = (float) $data['quantite_totale'];
            if ($q < 0 || $q > 99999) {
                $errors['quantite_totale'] = 'Quantité invalide (0 – 99 999).';
            }
        }

        $statutsValides = ['planifiee', 'en_cours', 'terminee', 'annulee'];
        if (!empty($data['statut']) && !in_array($data['statut'], $statutsValides, true)) {
            $errors['statut'] = 'Statut invalide.';
        }

        return $errors;
    }

    /* =========================================================
       PRIVATE : Nettoyage des entrées
    ========================================================= */
    private function sanitize(string $value): string {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
}
