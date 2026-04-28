<?php
/**
 * FoodSave – API : dechets.php
 * GET    → liste de tous les déchets
 * GET?id → un déchet
 * POST   → créer
 * PUT    → modifier
 * DELETE → supprimer
 */

declare(strict_types=1);

require_once __DIR__ . '/../models/Dechet.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function readBody(): array {
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
$model  = new Dechet();

try {

    /* -------- GET -------- */
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $d = $model->getById((int) $_GET['id']);
            if (!$d) respond(404, ['success' => false, 'message' => 'Déchet introuvable.']);
            respond(200, ['success' => true, 'data' => $d]);
        }
        if (isset($_GET['stats'])) {
            respond(200, ['success' => true, 'data' => $model->getStats()]);
        }
        respond(200, ['success' => true, 'data' => $model->getAll()]);
    }

    /* -------- POST -------- */
    if ($method === 'POST') {
        $b = readBody();

        foreach (['type_aliment', 'quantite', 'unite', 'date_dechet', 'raison'] as $f) {
            if (empty($b[$f])) {
                respond(422, ['success' => false, 'message' => "Le champ {$f} est obligatoire."]);
            }
        }

        $q = (float) $b['quantite'];
        if ($q <= 0 || $q > 9999) {
            respond(422, ['success' => false, 'message' => 'Quantité invalide (0.001 – 9999).']);
        }

        if (strtotime((string) $b['date_dechet']) === false || strtotime((string) $b['date_dechet']) > time()) {
            respond(422, ['success' => false, 'message' => 'Date invalide ou dans le futur.']);
        }

        $data = [
            'type_aliment' => clean((string) $b['type_aliment']),
            'quantite'     => $q,
            'unite'        => clean((string) $b['unite']),
            'date_dechet'  => clean((string) $b['date_dechet']),
            'raison'       => clean((string) $b['raison']),
            'notes'        => clean((string) ($b['notes'] ?? '')),
            'categorie_id' => isset($b['categorie_id']) && (int) $b['categorie_id'] > 0
                              ? (int) $b['categorie_id'] : null,
        ];

        if (!$model->create($data)) {
            respond(500, ['success' => false, 'message' => 'Erreur lors de l\'ajout.']);
        }

        respond(201, ['success' => true, 'message' => 'Déchet ajouté avec succès.']);
    }

    /* -------- PUT -------- */
    if ($method === 'PUT') {
        $b  = readBody();
        $id = (int) ($b['id'] ?? 0);

        if ($id <= 0) respond(422, ['success' => false, 'message' => 'Identifiant manquant.']);
        if (!$model->getById($id)) respond(404, ['success' => false, 'message' => 'Déchet introuvable.']);

        foreach (['type_aliment', 'quantite', 'unite', 'date_dechet', 'raison'] as $f) {
            if (empty($b[$f])) {
                respond(422, ['success' => false, 'message' => "Le champ {$f} est obligatoire."]);
            }
        }

        $q = (float) $b['quantite'];
        if ($q <= 0 || $q > 9999) {
            respond(422, ['success' => false, 'message' => 'Quantité invalide.']);
        }

        $data = [
            'type_aliment' => clean((string) $b['type_aliment']),
            'quantite'     => $q,
            'unite'        => clean((string) $b['unite']),
            'date_dechet'  => clean((string) $b['date_dechet']),
            'raison'       => clean((string) $b['raison']),
            'notes'        => clean((string) ($b['notes'] ?? '')),
            'categorie_id' => isset($b['categorie_id']) && (int) $b['categorie_id'] > 0
                              ? (int) $b['categorie_id'] : null,
        ];

        if (!$model->update($id, $data)) {
            respond(500, ['success' => false, 'message' => 'Erreur lors de la modification.']);
        }

        respond(200, ['success' => true, 'message' => 'Déchet modifié avec succès.']);
    }

    /* -------- DELETE -------- */
    if ($method === 'DELETE') {
        $b  = readBody();
        $id = (int) ($b['id'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) respond(422, ['success' => false, 'message' => 'Identifiant manquant.']);
        if (!$model->getById($id)) respond(404, ['success' => false, 'message' => 'Déchet introuvable.']);

        if (!$model->delete($id)) {
            respond(500, ['success' => false, 'message' => 'Erreur lors de la suppression.']);
        }

        respond(200, ['success' => true, 'message' => 'Déchet supprimé avec succès.']);
    }

    respond(405, ['success' => false, 'message' => 'Méthode non autorisée.']);

} catch (Throwable $e) {
    respond(500, ['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
