<?php
/**
 * FoodSave — Controller : MetierController
 */

require_once __DIR__ . '/../Model/Metier.php';

class MetierController {

    private Metier $model;

    public function __construct() {
        $this->model = new Metier();
    }

    /* ══════════════════════════════
       ACTIONS
    ══════════════════════════════ */

    public function index(): void {
        $metiers = $this->model->findAll();
        require_once __DIR__ . '/../View/Front/metier/index.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=metier_index');
            exit;
        }

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: index.php?action=metier_index');
            exit;
        }

        $m = new Metier();
        $m->setNom($this->clean($_POST['nom']))
          ->setDescription($this->clean($_POST['description'] ?? ''))
          ->setIcone($this->clean($_POST['icone'] ?? '💼'))
          ->setActif(isset($_POST['actif']));

        if ($m->save()) {
            $_SESSION['success'] = '✅ Métier créé.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la création.';
        }

        header('Location: index.php?action=metier_index');
        exit;
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=metier_index');
            exit;
        }

        $m = $this->model->findById((int)($_POST['id'] ?? 0));
        if (!$m) {
            $_SESSION['error'] = 'Métier introuvable.';
            header('Location: index.php?action=metier_index');
            exit;
        }

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: index.php?action=metier_index');
            exit;
        }

        $m->setNom($this->clean($_POST['nom']))
          ->setDescription($this->clean($_POST['description'] ?? ''))
          ->setIcone($this->clean($_POST['icone'] ?? '💼'))
          ->setActif(isset($_POST['actif']));

        if ($m->save()) {
            $_SESSION['success'] = '✅ Métier modifié.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la modification.';
        }

        header('Location: index.php?action=metier_index');
        exit;
    }

    public function delete(): void {
        $m = $this->model->findById((int)($_GET['id'] ?? 0));
        if ($m && $m->delete()) {
            $_SESSION['success'] = '🗑️ Métier supprimé.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la suppression.';
        }
        header('Location: index.php?action=metier_index');
        exit;
    }

    /* ══════════════════════════════
       VALIDATION & UTILITAIRES
    ══════════════════════════════ */

    private function validate(array $data): array {
        $errors = [];
        if (empty($data['nom'])) $errors['nom'] = 'Le nom est obligatoire.';
        return $errors;
    }

    private function clean(string $v): string {
        return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
    }
}
