<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Methode non autorisee']);
    exit;
}

$phone   = trim($_POST['phone']   ?? '');
$message = trim($_POST['message'] ?? '');

if (!$phone || !$message) {
    echo json_encode(['success' => false, 'error' => 'Parametres manquants (phone/email, message)']);
    exit;
}

$apiKey      = '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';
$senderEmail = 'laribiwadhah1312@gmail.com';
$senderName  = 'FoodSave';

$recipientEmail = $phone;

if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Veuillez fournir une adresse email valide']);
    exit;
}

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
    echo json_encode(['success' => false, 'error' => 'Reseau : ' . $curlErr]);
    exit;
}

$data = json_decode($response, true);

if ($httpCode === 201 && isset($data['messageId'])) {
    echo json_encode(['success' => true, 'msgId' => $data['messageId'], 'to' => $recipientEmail]);
} else {
    $errMsg = $data['message'] ?? $data['error'] ?? ('HTTP ' . $httpCode . ' : ' . $response);
    echo json_encode(['success' => false, 'error' => $errMsg]);
}
