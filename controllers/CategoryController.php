<?php
/**
 * FoodSave – Controller : CategoryController
 * Architecture MVC | Fichier : controllers/CategoryController.php
 */

require_once __DIR__ . '/../models/Category.php';

class CategoryController {

    private Category $model;

    public function __construct() {
        $this->model = new Category();
    }

    /* =========================================================
       ACTION : Liste des catégories (FrontOffice)
    ========================================================= */
    public function index(): void {
        $categories = $this->model->getAll();
        require_once __DIR__ . '/../views/frontoffice/category/index.php';
    }

    /* =========================================================
       ACTION : Formulaire création
    ========================================================= */
    public function create(): void {
        require_once __DIR__ . '/../views/frontoffice/category/create.php';
    }

    /* =========================================================
       ACTION : Enregistrer (POST)
    ========================================================= */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=category&action=create');
            exit;
        }

        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            header('Location: index.php?controller=category&action=create');
            exit;
        }

        $data = [
            'nom'         => $this->sanitize($_POST['nom']),
            'description' => $this->sanitize($_POST['description'] ?? ''),
            'couleur'     => $this->sanitize($_POST['couleur']     ?? '#28a745'),
            'icone'       => $this->sanitize($_POST['icone']       ?? 'tag'),
        ];

        if ($this->model->create($data)) {
            $_SESSION['success'] = '✅ Catégorie créée avec succès !';
        } else {
            $_SESSION['error'] = '❌ Une erreur est survenue.';
        }

        header('Location: index.php?controller=category&action=index');
        exit;
    }

    /* =========================================================
       ACTION : Formulaire modification
    ========================================================= */
    public function edit(): void {
        $id       = (int) ($_GET['id'] ?? 0);
        $category = $this->model->getById($id);

        if (!$category) {
            $_SESSION['error'] = 'Catégorie introuvable.';
            header('Location: index.php?controller=category&action=index');
            exit;
        }

        require_once __DIR__ . '/../views/frontoffice/category/edit.php';
    }

    /* =========================================================
       ACTION : Mettre à jour (POST)
    ========================================================= */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=category&action=index');
            exit;
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            header("Location: index.php?controller=category&action=edit&id={$id}");
            exit;
        }

        $data = [
            'nom'         => $this->sanitize($_POST['nom']),
            'description' => $this->sanitize($_POST['description'] ?? ''),
            'couleur'     => $this->sanitize($_POST['couleur']     ?? '#28a745'),
            'icone'       => $this->sanitize($_POST['icone']       ?? 'tag'),
        ];

        if ($this->model->update($id, $data)) {
            $_SESSION['success'] = '✅ Catégorie modifiée avec succès !';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la mise à jour.';
        }

        header('Location: index.php?controller=category&action=index');
        exit;
    }

    /* =========================================================
       ACTION : Supprimer
    ========================================================= */
    public function delete(): void {
        $id = (int) ($_GET['id'] ?? 0);

        if ($this->model->delete($id)) {
            $_SESSION['success'] = '🗑️ Catégorie supprimée.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la suppression.';
        }

        header('Location: index.php?controller=category&action=index');
        exit;
    }

    /* =========================================================
       ACTION : Back Office — liste admin
    ========================================================= */
    public function adminIndex(): void {
        $categories = $this->model->getAll();
        $stats      = $this->model->getStats();
        require_once __DIR__ . '/../views/backoffice/category/index.php';
    }

    /* =========================================================
       PRIVATE : Validation
    ========================================================= */
    private function validate(array $data): array {
        $errors = [];

        if (empty($data['nom'])) {
            $errors['nom'] = 'Le nom de la catégorie est obligatoire.';
        } elseif (mb_strlen($data['nom']) > 100) {
            $errors['nom'] = 'Le nom ne peut pas dépasser 100 caractères.';
        }

        if (!empty($data['couleur']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $data['couleur'])) {
            $errors['couleur'] = 'Format de couleur invalide (ex : #28a745).';
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
