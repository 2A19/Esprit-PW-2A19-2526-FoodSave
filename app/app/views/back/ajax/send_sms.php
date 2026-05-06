
<?php
/**
 * ajax/send_sms.php → Envoi d'emails via Brevo API
 *
 * Les emails sont envoyés directement via l'API Brevo
 * (anciennement SMS, maintenant EMAIL)
 */
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

$phone   = trim($_POST['phone']   ?? '');
$message = trim($_POST['message'] ?? '');

if (!$phone || !$message) {
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants (phone/email, message)']);
    exit;
}

// ── Credentials Brevo ────────────────────────────────────────
$apiKey = getenv('SENDINBLUE_API_KEY') ?: 'VOTRE_CLE_EN_DEVELOPPEMENT';
$senderEmail = 'laribiwadhah1312@gmail.com';  // ✅ Email vérifié dans Brevo
$senderName  = 'FoodSave';

// ── Déterminer si c'est un email ou un téléphone ─────────────
$recipientEmail = $phone;

// Si c'est un numéro de téléphone, rejeter (attendez un email)
if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Veuillez fournir une adresse email valide']);
    exit;
}

// ── Appel API Brevo ──────────────────────────────────────────
$url = 'https://api.brevo.com/v3/smtp/email';

$payload = json_encode([
    'sender' => [
        'email' => $senderEmail,
        'name'  => $senderName
    ],
    'to' => [
        [
            'email' => $recipientEmail
        ]
    ],
    'subject'      => 'Notification FoodSave',
    'htmlContent'  => '<p style="font-family: Arial; font-size: 14px;">' . htmlspecialchars($message) . '</p>',
    'textContent'  => $message
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'api-key: ' . $apiKey,
    ],
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['success' => false, 'error' => 'Réseau : ' . $curlErr]);
    exit;
}

$data = json_decode($response, true);

// Brevo retourne 201 si succès avec messageId
if ($httpCode === 201 && isset($data['messageId'])) {
    echo json_encode(['success' => true, 'msgId' => $data['messageId'], 'to' => $recipientEmail]);
} else {
    $errMsg = $data['message'] ?? $data['error'] ?? ('HTTP ' . $httpCode . ' : ' . $response);
    echo json_encode(['success' => false, 'error' => $errMsg]);
}
