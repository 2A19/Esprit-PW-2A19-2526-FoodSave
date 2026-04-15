<?php
/**
 * API Dechets (PHP + MySQL/PDO)
 * GET  -> liste des dechets
 * POST -> ajout d'un dechet
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../models/Dechet.php';
require_once __DIR__ . '/../models/User.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function readJsonBody(): array {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw ?: '{}', true);

    if (!is_array($body)) {
        respond(400, ['success' => false, 'message' => 'Payload JSON invalide.']);
    }

    return $body;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$model = new Dechet();
$userModel = new User();

function getActorUserId(): int {
    $sessionUserId = (int) ($_SESSION['user_id'] ?? 0);
    if ($sessionUserId > 0) {
        return $sessionUserId;
    }

    $queryActor = isset($_GET['as_user_id']) ? (int) $_GET['as_user_id'] : 0;
    return $queryActor > 0 ? $queryActor : 0;
}

function isAdminSession(): bool {
    return (string) ($_SESSION['role'] ?? '') === 'admin';
}

try {
    if ($method === 'GET') {
        $resource = isset($_GET['resource']) ? trim((string) $_GET['resource']) : '';

        if ($resource === 'users') {
            respond(200, [
                'success' => true,
                'data' => $userModel->getAllBasic(),
            ]);
        }

        $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
        $scope = isset($_GET['scope']) ? trim((string) $_GET['scope']) : '';
        $actorUserId = getActorUserId();
        $isAdmin = isAdminSession();

        if ($userId > 0) {
            $items = $model->getByUser($userId);
        } elseif ($scope === 'all' || $isAdmin) {
            $items = $model->getAll(500, 0);
        } elseif ($actorUserId > 0) {
            $items = $model->getByUser($actorUserId);
        } else {
            $items = $model->getAll(500, 0);
        }

        respond(200, [
            'success' => true,
            'data' => $items,
        ]);
    }

    if ($method === 'POST') {
        $body = readJsonBody();
        $actorUserId = getActorUserId();
        $isAdmin = isAdminSession();

        $required = ['type_aliment', 'quantite', 'unite', 'date_dechet', 'raison'];
        foreach ($required as $field) {
            if (!isset($body[$field]) || trim((string) $body[$field]) === '') {
                respond(422, ['success' => false, 'message' => "Le champ {$field} est obligatoire."]);
            }
        }

        $quantite = (float) $body['quantite'];
        if ($quantite <= 0 || $quantite > 9999) {
            respond(422, ['success' => false, 'message' => 'Quantite invalide.']);
        }

        $date = (string) $body['date_dechet'];
        if (strtotime($date) === false || strtotime($date) > time()) {
            respond(422, ['success' => false, 'message' => 'Date invalide.']);
        }

        $targetUserId = isset($body['user_id']) ? (int) $body['user_id'] : 0;

        if (!$isAdmin && $actorUserId > 0) {
            $targetUserId = $actorUserId;
        }

        if ($targetUserId <= 0) {
            $targetUserId = 1;
        }

        if (!$userModel->existsById($targetUserId)) {
            respond(422, ['success' => false, 'message' => 'Utilisateur invalide.']);
        }

        $data = [
            'user_id' => $targetUserId,
            'type_aliment' => htmlspecialchars(trim((string) $body['type_aliment']), ENT_QUOTES, 'UTF-8'),
            'quantite' => $quantite,
            'unite' => htmlspecialchars(trim((string) $body['unite']), ENT_QUOTES, 'UTF-8'),
            'date_dechet' => $date,
            'raison' => htmlspecialchars(trim((string) $body['raison']), ENT_QUOTES, 'UTF-8'),
            'notes' => htmlspecialchars(trim((string) ($body['notes'] ?? '')), ENT_QUOTES, 'UTF-8'),
        ];

        $ok = $model->create($data);

        if (!$ok) {
            respond(500, ['success' => false, 'message' => 'Impossible d\'ajouter le dechet.']);
        }

        respond(201, [
            'success' => true,
            'message' => 'Dechet ajoute avec succes.',
        ]);
    }

    if ($method === 'PUT') {
        $body = readJsonBody();
        $id = isset($body['id']) ? (int) $body['id'] : 0;
        $actorUserId = isset($body['as_user_id']) ? (int) $body['as_user_id'] : getActorUserId();
        $isAdmin = isAdminSession();

        if ($id <= 0) {
            respond(422, ['success' => false, 'message' => 'Identifiant manquant.']);
        }

        $existing = $model->getById($id);
        if (!$existing) {
            respond(404, ['success' => false, 'message' => 'Dechet introuvable.']);
        }

        if (!$isAdmin && $actorUserId > 0 && (int) $existing['user_id'] !== $actorUserId) {
            respond(403, ['success' => false, 'message' => 'Operation non autorisee pour cet utilisateur.']);
        }

        $required = ['type_aliment', 'quantite', 'unite', 'date_dechet', 'raison'];
        foreach ($required as $field) {
            if (!isset($body[$field]) || trim((string) $body[$field]) === '') {
                respond(422, ['success' => false, 'message' => "Le champ {$field} est obligatoire."]);
            }
        }

        $quantite = (float) $body['quantite'];
        if ($quantite <= 0 || $quantite > 9999) {
            respond(422, ['success' => false, 'message' => 'Quantite invalide.']);
        }

        $date = (string) $body['date_dechet'];
        if (strtotime($date) === false || strtotime($date) > time()) {
            respond(422, ['success' => false, 'message' => 'Date invalide.']);
        }

        $ok = $model->update($id, [
            'type_aliment' => htmlspecialchars(trim((string) $body['type_aliment']), ENT_QUOTES, 'UTF-8'),
            'quantite' => $quantite,
            'unite' => htmlspecialchars(trim((string) $body['unite']), ENT_QUOTES, 'UTF-8'),
            'date_dechet' => $date,
            'raison' => htmlspecialchars(trim((string) $body['raison']), ENT_QUOTES, 'UTF-8'),
            'notes' => htmlspecialchars(trim((string) ($body['notes'] ?? '')), ENT_QUOTES, 'UTF-8'),
        ]);

        if (!$ok) {
            respond(500, ['success' => false, 'message' => 'Impossible de modifier le dechet.']);
        }

        respond(200, [
            'success' => true,
            'message' => 'Dechet modifie avec succes.',
        ]);
    }

    if ($method === 'DELETE') {
        $body = readJsonBody();
        $id = isset($body['id']) ? (int) $body['id'] : (int) ($_GET['id'] ?? 0);
        $actorUserId = isset($body['as_user_id']) ? (int) $body['as_user_id'] : getActorUserId();
        $isAdmin = isAdminSession();

        if ($id <= 0) {
            respond(422, ['success' => false, 'message' => 'Identifiant manquant.']);
        }

        $existing = $model->getById($id);
        if (!$existing) {
            respond(404, ['success' => false, 'message' => 'Dechet introuvable.']);
        }

        if (!$isAdmin && $actorUserId > 0 && (int) $existing['user_id'] !== $actorUserId) {
            respond(403, ['success' => false, 'message' => 'Operation non autorisee pour cet utilisateur.']);
        }

        $ok = $model->delete($id);
        if (!$ok) {
            respond(500, ['success' => false, 'message' => 'Impossible de supprimer le dechet.']);
        }

        respond(200, [
            'success' => true,
            'message' => 'Dechet supprime avec succes.',
        ]);
    }

    respond(405, [
        'success' => false,
        'message' => 'Methode non autorisee.',
    ]);
} catch (Throwable $e) {
    respond(500, [
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage(),
    ]);
}
