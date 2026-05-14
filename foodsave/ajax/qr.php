<?php
/**
 * FoodSave — API : qr.php
 * Génère un QR code via API externe
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['error' => 'Method not allowed']);
}

$input = json_decode(file_get_contents('php://input'), true);
$data = $input['data'] ?? '';
$size = $input['size'] ?? '200x200';

if (empty($data)) {
    respond(400, ['error' => 'Data is required']);
}

// Utiliser l'API QR Server (gratuite, pas de clé requise)
$url = 'https://api.qrserver.com/v1/create-qr-code/?size=' . urlencode($size) . '&data=' . urlencode($data);

respond(200, ['success' => true, 'qr_url' => $url]);

function respond($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}
?>
