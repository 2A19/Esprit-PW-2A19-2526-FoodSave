<?php
/**
 * FoodSave — Controller : CategoryController
 */

require_once __DIR__ . '/../models/Category.php';

class CategoryController {

    private Category $model;

    public function __construct() {
        $this->model = new Category();
    }

    public function index(): void {
        $categories = $this->model->findAll();
        require_once __DIR__ . '/../views/frontoffice/category/index.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=category&action=index');
            exit;
        }

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: index.php?controller=category&action=index');
            exit;
        }

        $c = new Category();
        $c->setNom($this->clean($_POST['nom']))
          ->setDescription($this->clean($_POST['description'] ?? ''))
          ->setCouleur($this->clean($_POST['couleur'] ?? '#4caf50'))
          ->setIcone($this->clean($_POST['icone'] ?? 'tag'));

        if ($c->save()) {
            $_SESSION['success'] = '✅ Catégorie créée.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la création.';
        }

        header('Location: index.php?controller=category&action=index');
        exit;
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=category&action=index');
            exit;
        }

        $c = $this->model->findById((int)($_POST['id'] ?? 0));
        if (!$c) {
            $_SESSION['error'] = 'Catégorie introuvable.';
            header('Location: index.php?controller=category&action=index');
            exit;
        }

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: index.php?controller=category&action=index');
            exit;
        }

        $c->setNom($this->clean($_POST['nom']))
          ->setDescription($this->clean($_POST['description'] ?? ''))
          ->setCouleur($this->clean($_POST['couleur'] ?? '#4caf50'))
          ->setIcone($this->clean($_POST['icone'] ?? 'tag'));

        if ($c->save()) {
            $_SESSION['success'] = '✅ Catégorie modifiée.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la modification.';
        }

        header('Location: index.php?controller=category&action=index');
        exit;
    }

    public function delete(): void {
        $c = $this->model->findById((int)($_GET['id'] ?? 0));
        if ($c && $c->delete()) {
            $_SESSION['success'] = '🗑️ Catégorie supprimée.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la suppression.';
        }
        header('Location: index.php?controller=category&action=index');
        exit;
    }

    private function validate(array $data): array {
        $errors = [];
        if (empty($data['nom'])) $errors['nom'] = 'Le nom est obligatoire.';
        if (!empty($data['couleur']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $data['couleur']))
            $errors['couleur'] = 'Format couleur invalide.';
        return $errors;
    }

    private function clean(string $v): string {
        return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
    }
}
