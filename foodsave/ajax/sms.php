<?php
/**
 * FoodSave — API : sms.php
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/Sms.php';
header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function body(): array {
    $raw = file_get_contents('php://input') ?: '{}';
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : respond(400, ['success' => false, 'message' => 'JSON invalide']);
}

function c(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(405, ['success' => false, 'message' => 'Méthode non autorisée.']);
    }

    $data = body();
    $phone = c($data['phone'] ?? '');
    $message = trim($data['message'] ?? '');

    if ($phone === '') {
        respond(422, ['success' => false, 'message' => 'Numéro de téléphone manquant.']);
    }
    if ($message === '') {
        respond(422, ['success' => false, 'message' => 'Message manquant.']);
    }
    if (!preg_match('/^\+?[0-9]{6,15}$/', $phone)) {
        respond(422, ['success' => false, 'message' => 'Numéro de téléphone invalide. Utilisez un format international tel que +33123456789.']);
    }

    SmsProvider::send($phone, $message);
    respond(200, ['success' => true, 'message' => 'SMS envoyé.']);
} catch (Throwable $e) {
    respond(500, ['success' => false, 'message' => 'Erreur SMS : ' . $e->getMessage()]);
}
