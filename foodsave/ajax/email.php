<?php
/**
 * FoodSave — API : email.php
 * Envoi d'email via Brevo (ex-Sendinblue)
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/ApiKeys.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function body(): array {
    $raw     = file_get_contents('php://input') ?: '{}';
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(405, ['success' => false, 'message' => 'Méthode non autorisée.']);
    }

    $data = body();

    $to      = trim($data['to']      ?? '');
    $subject = trim($data['subject'] ?? '');
    $body    = trim($data['body']    ?? '');
    $toName  = sanitize($data['to_name'] ?? '');

    // Validations
    if ($to === '') {
        respond(422, ['success' => false, 'message' => 'Adresse email destinataire manquante.']);
    }
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        respond(422, ['success' => false, 'message' => 'Adresse email invalide.']);
    }
    if ($subject === '') {
        respond(422, ['success' => false, 'message' => 'Sujet de l\'email manquant.']);
    }
    if ($body === '') {
        respond(422, ['success' => false, 'message' => 'Corps de l\'email manquant.']);
    }

    // Construire le payload Brevo
    $payload = [
        'sender' => [
            'name'  => ApiKeys::BREVO_SENDER_NAME,
            'email' => ApiKeys::BREVO_SENDER_EMAIL,
        ],
        'to' => [
            [
                'email' => $to,
                'name'  => $toName ?: $to,
            ]
        ],
        'subject'     => $subject,
        'htmlContent' => nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')),
        'textContent' => $body,
    ];

    // Appel API Brevo
    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'accept: application/json',
            'api-key: ' . ApiKeys::BREVO_API_KEY,
            'content-type: application/json',
        ],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response   = curl_exec($ch);
    $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        respond(500, ['success' => false, 'message' => 'Erreur réseau : ' . $curlError]);
    }

    $result = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        respond(200, [
            'success'   => true,
            'message'   => 'Email envoyé avec succès.',
            'messageId' => $result['messageId'] ?? null,
        ]);
    } else {
        $errMsg = $result['message'] ?? $response;
        respond($httpCode, ['success' => false, 'message' => 'Erreur Brevo : ' . $errMsg]);
    }

} catch (Throwable $e) {
    respond(500, ['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
