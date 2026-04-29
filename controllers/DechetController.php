<?php
/**
 * FoodSave — Controller : DechetController
 */

require_once __DIR__ . '/../models/Dechet.php';

class DechetController {

    private Dechet $model;

    public function __construct() {
        $this->model = new Dechet();
    }

    public function index(): void {
        $dechets = $this->model->findAll();
        $stats   = $this->model->getStats();
        require_once __DIR__ . '/../views/frontoffice/dechet/index.php';
    }

    public function create(): void {
        require_once __DIR__ . '/../views/frontoffice/dechet/create.php';
    }

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

        $d = new Dechet();
        $d->setTypeAliment($this->clean($_POST['type_aliment']))
          ->setQuantite((float) $_POST['quantite'])
          ->setUnite($this->clean($_POST['unite']))
          ->setDateDechet($this->clean($_POST['date_dechet']))
          ->setRaison($this->clean($_POST['raison']))
          ->setNotes($this->clean($_POST['notes'] ?? ''))
          ->setCategorieId(!empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null);

        if ($d->save()) {
            $_SESSION['success'] = '✅ Déchet enregistré avec succès.';
        } else {
            $_SESSION['error'] = '❌ Une erreur est survenue.';
        }

        header('Location: index.php?controller=dechet&action=index');
        exit;
    }

    public function edit(): void {
        $d = $this->model->findById((int)($_GET['id'] ?? 0));
        if (!$d) {
            $_SESSION['error'] = 'Déchet introuvable.';
            header('Location: index.php?controller=dechet&action=index');
            exit;
        }
        $dechet = $d;
        require_once __DIR__ . '/../views/frontoffice/dechet/edit.php';
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=dechet&action=index');
            exit;
        }

        $d = $this->model->findById((int)($_POST['id'] ?? 0));
        if (!$d) {
            $_SESSION['error'] = 'Déchet introuvable.';
            header('Location: index.php?controller=dechet&action=index');
            exit;
        }

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            header('Location: index.php?controller=dechet&action=edit&id=' . $d->getId());
            exit;
        }

        $d->setTypeAliment($this->clean($_POST['type_aliment']))
          ->setQuantite((float) $_POST['quantite'])
          ->setUnite($this->clean($_POST['unite']))
          ->setDateDechet($this->clean($_POST['date_dechet']))
          ->setRaison($this->clean($_POST['raison']))
          ->setNotes($this->clean($_POST['notes'] ?? ''))
          ->setCategorieId(!empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null);

        if ($d->save()) {
            $_SESSION['success'] = '✅ Déchet modifié avec succès.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la modification.';
        }

        header('Location: index.php?controller=dechet&action=index');
        exit;
    }

    public function delete(): void {
        $d = $this->model->findById((int)($_GET['id'] ?? 0));
        if ($d && $d->delete()) {
            $_SESSION['success'] = '🗑️ Déchet supprimé.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la suppression.';
        }
        header('Location: index.php?controller=dechet&action=index');
        exit;
    }

    /* ── Validation ── */
    private function validate(array $data): array {
        $errors = [];
        if (empty($data['type_aliment']))       $errors['type_aliment'] = 'Type obligatoire.';
        if (empty($data['quantite']))            $errors['quantite']    = 'Quantité obligatoire.';
        elseif ((float)$data['quantite'] <= 0)  $errors['quantite']    = 'Quantité doit être > 0.';
        if (empty($data['unite']))               $errors['unite']       = 'Unité obligatoire.';
        if (empty($data['date_dechet']))         $errors['date_dechet'] = 'Date obligatoire.';
        if (empty($data['raison']))              $errors['raison']      = 'Raison obligatoire.';
        return $errors;
    }

    private function clean(string $v): string {
        return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
    }
}
