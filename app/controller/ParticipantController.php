<?php
// ============================================================
//  app/controller/ParticipantController.php — Controller MVC
// ============================================================

require_once __DIR__ . '/../models/ParticipantModel.php';
require_once __DIR__ . '/../models/EvenementModel.php';

class ParticipantController {

    private ParticipantModel $model;
    private EvenementModel $eventModel;

    public function __construct() {
        $this->model = new ParticipantModel();
        $this->eventModel = new EvenementModel();
    }

    public function listParticipants(string $search = '', string $statut = '', int $evenementId = 0): array {
        if ($search !== '') {
            $rows = $this->model->search($search);
        } elseif ($evenementId > 0) {
            $rows = $this->model->findByEvent($evenementId);
        } else {
            $rows = $this->model->findAll();
        }

        if ($statut !== '') {
            $rows = array_filter($rows, fn($r) => $r['statut'] === $statut);
        }

        return array_values($rows);
    }

    public function findParticipant(int $id): array|false {
        return $this->model->findById($id);
    }

    public function saveParticipant(array $data, int $id = 0): int|bool {
        return $id > 0 ? $this->model->update($id, $data) : $this->model->create($data);
    }

    public function deleteParticipant(int $id): bool {
        return $this->model->delete($id);
    }

    public function getEventList(): array {
        return $this->eventModel->findAll();
    }

    public function findEvent(int $id): array|false {
        return $this->eventModel->findById($id);
    }

    public function emailExists(string $email, int $eventId, int $excludeId = 0): bool {
        return $this->model->emailExists($email, $eventId, $excludeId);
    }

    public function getStats(): array {
        return $this->model->getStats();
    }

    public function validate(array $data): array {
        return ParticipantModel::validate($data);
    }
}
