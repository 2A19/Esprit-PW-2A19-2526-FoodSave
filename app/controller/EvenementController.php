<?php
// ============================================================
//  app/controller/EvenementController.php — Controller MVC
// ============================================================

require_once __DIR__ . '/../models/EvenementModel.php';
require_once __DIR__ . '/../models/ParticipantModel.php';

class EvenementController {

    private EvenementModel $model;

    public function __construct() {
        $this->model = new EvenementModel();
    }

    public function listEvents(string $search = '', string $statut = '', string $categorie = ''): array {
        if ($search !== '') {
            $rows = $this->model->search($search);
        } elseif ($statut !== '') {
            $rows = $this->model->findByStatut($statut);
        } else {
            $rows = $this->model->findAll();
        }

        if ($categorie !== '') {
            $rows = array_filter($rows, fn($r) => $r['categorie'] === $categorie);
        }

        return $this->attachParticipantCounts($rows);
    }

    public function getUpcoming(int $limit = 6): array {
        $rows = $this->model->findUpcoming($limit);
        return $this->attachParticipantCounts($rows);
    }

    public function findEvent(int $id): array|false {
        return $this->model->findById($id);
    }

    public function saveEvent(array $data, int $id = 0): int|bool {
        return $id > 0 ? $this->model->update($id, $data) : $this->model->create($data);
    }

    public function deleteEvent(int $id): bool {
        return $this->model->delete($id);
    }

    public function countParticipants(int $id): int {
        return $this->model->countParticipants($id);
    }

    public function getStats(): array {
        return $this->model->getStats();
    }

    public function validate(array $data): array {
        return EvenementModel::validate($data);
    }

    public function attachParticipantCounts(array $rows): array {
        foreach ($rows as &$row) {
            $row['nb_p'] = $this->countParticipants((int)$row['id']);
        }
        unset($row);
        return array_values($rows);
    }
}
