<?php
/**
 * FoodSave – Controller : DechetController
 * Architecture MVC | Fichier : controllers/DechetController.php
 */

require_once __DIR__ . '/../models/Dechet.php';

class DechetController {

    private Dechet $model;

    public function __construct() {
        $this->model = new Dechet();
    }

    /* =========================================================
       ACTION : Afficher la liste (FrontOffice)
    ========================================================= */
    public function index(): void {
        $userId = $_SESSION['user_id'] ?? 0;
        $dechets = $this->model->getByUser($userId);
        $stats   = $this->model->getStats($userId);
        require_once __DIR__ . '/../views/frontoffice/dechet/index.php';
    }

    /* =========================================================
       ACTION : Afficher formulaire ajout
    ========================================================= */
    public function create(): void {
        require_once __DIR__ . '/../views/frontoffice/dechet/create.php';
    }

    /* =========================================================
       ACTION : Enregistrer un nouveau déchet (POST)
    ========================================================= */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=dechet&action=create');
            exit;
        }

        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            header('Location: index.php?controller=dechet&action=create');
            exit;
        }

        $data = [
            'user_id'      => $_SESSION['user_id'],
            'type_aliment' => $this->sanitize($_POST['type_aliment']),
            'quantite'     => (float) $_POST['quantite'],
            'unite'        => $this->sanitize($_POST['unite']),
            'date_dechet'  => $this->sanitize($_POST['date_dechet']),
            'raison'       => $this->sanitize($_POST['raison']),
            'notes'        => $this->sanitize($_POST['notes'] ?? ''),
        ];

        if ($this->model->create($data)) {
            $_SESSION['success'] = '✅ Déchet enregistré avec succès !';
        } else {
            $_SESSION['error'] = '❌ Une erreur est survenue.';
        }

        header('Location: index.php?controller=dechet&action=index');
        exit;
    }

    /* =========================================================
       ACTION : Afficher formulaire modification
    ========================================================= */
    public function edit(): void {
        $id     = (int) ($_GET['id'] ?? 0);
        $dechet = $this->model->getById($id);

        if (!$dechet) {
            $_SESSION['error'] = 'Déchet introuvable.';
            header('Location: index.php?controller=dechet&action=index');
            exit;
        }

        require_once __DIR__ . '/../views/frontoffice/dechet/edit.php';
    }

    /* =========================================================
       ACTION : Mettre à jour (POST)
    ========================================================= */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=dechet&action=index');
            exit;
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            header("Location: index.php?controller=dechet&action=edit&id={$id}");
            exit;
        }

        $data = [
            'type_aliment' => $this->sanitize($_POST['type_aliment']),
            'quantite'     => (float) $_POST['quantite'],
            'unite'        => $this->sanitize($_POST['unite']),
            'date_dechet'  => $this->sanitize($_POST['date_dechet']),
            'raison'       => $this->sanitize($_POST['raison']),
            'notes'        => $this->sanitize($_POST['notes'] ?? ''),
        ];

        if ($this->model->update($id, $data)) {
            $_SESSION['success'] = '✅ Déchet modifié avec succès !';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la mise à jour.';
        }

        header('Location: index.php?controller=dechet&action=index');
        exit;
    }

    /* =========================================================
       ACTION : Supprimer
    ========================================================= */
    public function delete(): void {
        $id = (int) ($_GET['id'] ?? 0);

        if ($this->model->delete($id)) {
            $_SESSION['success'] = '🗑️ Déchet supprimé.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la suppression.';
        }

        header('Location: index.php?controller=dechet&action=index');
        exit;
    }

    /* =========================================================
       ACTION : Back Office — liste admin
    ========================================================= */
    public function adminIndex(): void {
        $filters = [
            'type'      => $_GET['type']      ?? '',
            'raison'    => $_GET['raison']     ?? '',
            'date_from' => $_GET['date_from']  ?? '',
            'date_to'   => $_GET['date_to']    ?? '',
        ];
        $dechets = $this->model->search($filters);
        require_once __DIR__ . '/../views/backoffice/dechet/index.php';
    }

    /* =========================================================
       PRIVATE : Validation sans HTML5
    ========================================================= */
    private function validate(array $data): array {
        $errors = [];

        if (empty($data['type_aliment'])) {
            $errors['type_aliment'] = 'Le type d\'aliment est obligatoire.';
        }

        if (empty($data['quantite'])) {
            $errors['quantite'] = 'La quantité est obligatoire.';
        } elseif (!is_numeric($data['quantite']) || (float)$data['quantite'] <= 0) {
            $errors['quantite'] = 'La quantité doit être un nombre positif.';
        } elseif ((float)$data['quantite'] > 9999) {
            $errors['quantite'] = 'La quantité maximale est 9999.';
        }

        if (empty($data['unite'])) {
            $errors['unite'] = 'L\'unité est obligatoire.';
        }

        if (empty($data['date_dechet'])) {
            $errors['date_dechet'] = 'La date est obligatoire.';
        } elseif (strtotime($data['date_dechet']) === false) {
            $errors['date_dechet'] = 'Format de date invalide.';
        } elseif (strtotime($data['date_dechet']) > time()) {
            $errors['date_dechet'] = 'La date ne peut pas être dans le futur.';
        }

        if (empty($data['raison'])) {
            $errors['raison'] = 'La raison est obligatoire.';
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
