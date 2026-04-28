<?php
/**
 * FoodSave – API : collectes.php
 * GET    → liste / détail / stats
 * POST   → créer | action: add_dechet | remove_dechet
 * PUT    → modifier (ou statut seul)
 * DELETE → supprimer
 */

declare(strict_types=1);

require_once __DIR__ . '/../models/Collecte.php';

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
$model  = new Collecte();

try {

    /* -------- GET -------- */
    if ($method === 'GET') {
        if (isset($_GET['stats'])) {
            respond(200, ['success' => true, 'data' => $model->getStats()]);
        }
        if (isset($_GET['id'])) {
            $c = $model->getById((int) $_GET['id']);
            if (!$c) respond(404, ['success' => false, 'message' => 'Collecte introuvable.']);
            respond(200, ['success' => true, 'data' => $c]);
        }
        respond(200, ['success' => true, 'data' => $model->getAll()]);
    }

    /* -------- POST -------- */
    if ($method === 'POST') {
        $b      = readBody();
        $action = trim((string) ($b['action'] ?? ''));

        if ($action === 'add_dechet') {
            $cid = (int) ($b['collecte_id'] ?? 0);
            $did = (int) ($b['dechet_id']   ?? 0);
            if ($cid <= 0 || $did <= 0) respond(422, ['success' => false, 'message' => 'IDs manquants.']);
            $ok = $model->addDechet($cid, $did);
            respond($ok ? 200 : 500, ['success' => $ok, 'message' => $ok ? 'Déchet rattaché.' : 'Erreur.']);
        }

        if ($action === 'remove_dechet') {
            $cid = (int) ($b['collecte_id'] ?? 0);
            $did = (int) ($b['dechet_id']   ?? 0);
            if ($cid <= 0 || $did <= 0) respond(422, ['success' => false, 'message' => 'IDs manquants.']);
            $ok = $model->removeDechet($cid, $did);
            respond($ok ? 200 : 500, ['success' => $ok, 'message' => $ok ? 'Déchet retiré.' : 'Erreur.']);
        }

        foreach (['titre', 'date_collecte', 'lieu'] as $f) {
            if (empty($b[$f])) respond(422, ['success' => false, 'message' => "Le champ {$f} est obligatoire."]);
        }

        if (strtotime((string) $b['date_collecte']) === false) {
            respond(422, ['success' => false, 'message' => 'Format de date invalide.']);
        }

        $statuts = ['planifiee', 'en_cours', 'terminee', 'annulee'];
        $statut  = trim((string) ($b['statut'] ?? 'planifiee'));
        if (!in_array($statut, $statuts, true)) $statut = 'planifiee';

        $data = [
            'titre'           => clean((string) $b['titre']),
            'description'     => clean((string) ($b['description'] ?? '')),
            'date_collecte'   => (string) $b['date_collecte'],
            'lieu'            => clean((string) $b['lieu']),
            'quantite_totale' => max(0, (float) ($b['quantite_totale'] ?? 0)),
            'unite'           => clean((string) ($b['unite'] ?? 'kg')),
            'statut'          => $statut,
        ];

        $newId = $model->create($data);
        if (!$newId) respond(500, ['success' => false, 'message' => 'Erreur lors de la création.']);

        respond(201, ['success' => true, 'message' => 'Collecte créée avec succès.', 'id' => $newId]);
    }

    /* -------- PUT -------- */
    if ($method === 'PUT') {
        $b  = readBody();
        $id = (int) ($b['id'] ?? 0);

        if ($id <= 0) respond(422, ['success' => false, 'message' => 'Identifiant manquant.']);
        if (!$model->getById($id)) respond(404, ['success' => false, 'message' => 'Collecte introuvable.']);

        // Mise à jour du statut seul
        if (isset($b['statut']) && count($b) <= 2) {
            $ok = $model->updateStatut($id, trim((string) $b['statut']));
            respond($ok ? 200 : 422, ['success' => $ok, 'message' => $ok ? 'Statut mis à jour.' : 'Statut invalide.']);
        }

        foreach (['titre', 'date_collecte', 'lieu'] as $f) {
            if (empty($b[$f])) respond(422, ['success' => false, 'message' => "Le champ {$f} est obligatoire."]);
        }

        $statuts = ['planifiee', 'en_cours', 'terminee', 'annulee'];
        $statut  = trim((string) ($b['statut'] ?? 'planifiee'));
        if (!in_array($statut, $statuts, true)) $statut = 'planifiee';

        $data = [
            'titre'           => clean((string) $b['titre']),
            'description'     => clean((string) ($b['description'] ?? '')),
            'date_collecte'   => (string) $b['date_collecte'],
            'lieu'            => clean((string) $b['lieu']),
            'quantite_totale' => max(0, (float) ($b['quantite_totale'] ?? 0)),
            'unite'           => clean((string) ($b['unite'] ?? 'kg')),
            'statut'          => $statut,
        ];

        if (!$model->update($id, $data)) {
            respond(500, ['success' => false, 'message' => 'Erreur lors de la modification.']);
        }

        respond(200, ['success' => true, 'message' => 'Collecte modifiée avec succès.']);
    }

    /* -------- DELETE -------- */
    if ($method === 'DELETE') {
        $b  = readBody();
        $id = (int) ($b['id'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) respond(422, ['success' => false, 'message' => 'Identifiant manquant.']);
        if (!$model->getById($id)) respond(404, ['success' => false, 'message' => 'Collecte introuvable.']);

        if (!$model->delete($id)) {
            respond(500, ['success' => false, 'message' => 'Erreur lors de la suppression.']);
        }

        respond(200, ['success' => true, 'message' => 'Collecte supprimée avec succès.']);
    }

    respond(405, ['success' => false, 'message' => 'Méthode non autorisée.']);

} catch (Throwable $e) {
    respond(500, ['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
