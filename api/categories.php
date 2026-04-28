<?php
/**
 * FoodSave – API : categories.php
 * GET    → liste / détail / simple / stats
 * POST   → créer
 * PUT    → modifier
 * DELETE → supprimer
 */

declare(strict_types=1);

require_once __DIR__ . '/../models/Category.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function readJsonBody(): array {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw ?: '{}', true);
    if (!is_array($body)) {
        respond(400, ['success' => false, 'message' => 'Payload JSON invalide.']);
    }
    return $body;
}

function clean(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$model  = new Category();

try {

    /* -------- GET -------- */
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $cat = $model->getById((int) $_GET['id']);
            if (!$cat) respond(404, ['success' => false, 'message' => 'Catégorie introuvable.']);
            respond(200, ['success' => true, 'data' => $cat]);
        }
        if (isset($_GET['simple'])) {
            respond(200, ['success' => true, 'data' => $model->getAllSimple()]);
        }
        if (isset($_GET['stats'])) {
            respond(200, ['success' => true, 'data' => $model->getStats()]);
        }
        respond(200, ['success' => true, 'data' => $model->getAll()]);
    }

    /* -------- POST -------- */
    if ($method === 'POST') {
        $body = readJsonBody();

        if (empty($body['nom'])) {
            respond(422, ['success' => false, 'message' => 'Le champ nom est obligatoire.']);
        }
        if (!empty($body['couleur']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $body['couleur'])) {
            respond(422, ['success' => false, 'message' => 'Format de couleur invalide (ex : #4caf50).']);
        }

        $data = [
            'nom'         => clean((string) $body['nom']),
            'description' => clean((string) ($body['description'] ?? '')),
            'couleur'     => clean((string) ($body['couleur']     ?? '#4caf50')),
            'icone'       => clean((string) ($body['icone']       ?? 'tag')),
        ];

        if (!$model->create($data)) {
            respond(500, ['success' => false, 'message' => 'Impossible de créer la catégorie.']);
        }

        respond(201, ['success' => true, 'message' => 'Catégorie créée avec succès.']);
    }

    /* -------- PUT -------- */
    if ($method === 'PUT') {
        $body = readJsonBody();
        $id   = (int) ($body['id'] ?? 0);

        if ($id <= 0) respond(422, ['success' => false, 'message' => 'Identifiant manquant.']);
        if (!$model->existsById($id)) respond(404, ['success' => false, 'message' => 'Catégorie introuvable.']);
        if (empty($body['nom'])) respond(422, ['success' => false, 'message' => 'Le champ nom est obligatoire.']);

        $data = [
            'nom'         => clean((string) $body['nom']),
            'description' => clean((string) ($body['description'] ?? '')),
            'couleur'     => clean((string) ($body['couleur']     ?? '#4caf50')),
            'icone'       => clean((string) ($body['icone']       ?? 'tag')),
        ];

        if (!$model->update($id, $data)) {
            respond(500, ['success' => false, 'message' => 'Impossible de modifier la catégorie.']);
        }

        respond(200, ['success' => true, 'message' => 'Catégorie modifiée avec succès.']);
    }

    /* -------- DELETE -------- */
    if ($method === 'DELETE') {
        $body = readJsonBody();
        $id   = (int) ($body['id'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) respond(422, ['success' => false, 'message' => 'Identifiant manquant.']);
        if (!$model->existsById($id)) respond(404, ['success' => false, 'message' => 'Catégorie introuvable.']);

        if (!$model->delete($id)) {
            respond(500, ['success' => false, 'message' => 'Impossible de supprimer la catégorie.']);
        }

        respond(200, ['success' => true, 'message' => 'Catégorie supprimée avec succès.']);
    }

    respond(405, ['success' => false, 'message' => 'Méthode non autorisée.']);

} catch (Throwable $e) {
    respond(500, ['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
