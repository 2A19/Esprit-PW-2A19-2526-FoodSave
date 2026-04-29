<?php
/**
 * FoodSave — Controller : CollecteController
 */

require_once __DIR__ . '/../models/Collecte.php';

class CollecteController {

    private Collecte $model;

    public function __construct() {
        $this->model = new Collecte();
    }

    public function index(): void {
        $collectes = $this->model->findAll();
        $stats     = $this->model->getStats();
        require_once __DIR__ . '/../views/frontoffice/collecte/index.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=collecte&action=index');
            exit;
        }

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old']    = $_POST;
            header('Location: index.php?controller=collecte&action=index');
            exit;
        }

        $col = new Collecte();
        $col->setTitre($this->clean($_POST['titre']))
            ->setDescription($this->clean($_POST['description'] ?? ''))
            ->setDateCollecte($this->clean($_POST['date_collecte']))
            ->setLieu($this->clean($_POST['lieu']))
            ->setQuantiteTotale((float)($_POST['quantite_totale'] ?? 0))
            ->setUnite($this->clean($_POST['unite'] ?? 'kg'))
            ->setStatut($this->clean($_POST['statut'] ?? 'planifiee'));

        if ($col->save()) {
            $_SESSION['success'] = '✅ Collecte créée.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la création.';
        }

        header('Location: index.php?controller=collecte&action=index');
        exit;
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=collecte&action=index');
            exit;
        }

        $col = $this->model->findById((int)($_POST['id'] ?? 0));
        if (!$col) {
            $_SESSION['error'] = 'Collecte introuvable.';
            header('Location: index.php?controller=collecte&action=index');
            exit;
        }

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: index.php?controller=collecte&action=index');
            exit;
        }

        $col->setTitre($this->clean($_POST['titre']))
            ->setDescription($this->clean($_POST['description'] ?? ''))
            ->setDateCollecte($this->clean($_POST['date_collecte']))
            ->setLieu($this->clean($_POST['lieu']))
            ->setQuantiteTotale((float)($_POST['quantite_totale'] ?? 0))
            ->setUnite($this->clean($_POST['unite'] ?? 'kg'))
            ->setStatut($this->clean($_POST['statut'] ?? 'planifiee'));

        if ($col->save()) {
            $_SESSION['success'] = '✅ Collecte modifiée.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la modification.';
        }

        header('Location: index.php?controller=collecte&action=index');
        exit;
    }

    public function delete(): void {
        $col = $this->model->findById((int)($_GET['id'] ?? 0));
        if ($col && $col->delete()) {
            $_SESSION['success'] = '🗑️ Collecte supprimée.';
        } else {
            $_SESSION['error'] = '❌ Erreur lors de la suppression.';
        }
        header('Location: index.php?controller=collecte&action=index');
        exit;
    }

    private function validate(array $data): array {
        $errors = [];
        if (empty($data['titre']))         $errors['titre']         = 'Titre obligatoire.';
        if (empty($data['date_collecte'])) $errors['date_collecte'] = 'Date obligatoire.';
        if (empty($data['lieu']))          $errors['lieu']          = 'Lieu obligatoire.';
        return $errors;
    }

    private function clean(string $v): string {
        return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
    }
}
