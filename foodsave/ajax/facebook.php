<?php
/**
 * FoodSave — API : facebook.php
 * Partage sur Facebook avec lien visible
 */

require_once __DIR__ . '/../config/ApiKeys.php';

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
$url = $input['url'] ?? '';
$quote = $input['quote'] ?? 'Rejoignez FoodSave pour réduire le gaspillage alimentaire !';

$appId = ApiKeys::FACEBOOK_APP_ID;

// Approche directe avec sharer.php et paramètres pour forcer l'affichage du lien
// hashtag aide aussi à montrer le contenu
$shareUrl = 'https://www.facebook.com/sharer/sharer.php?' .
            'u=' . urlencode($url) . 
            '&quote=' . urlencode($quote) .
            '&app_id=' . urlencode($appId) .
            '&display=popup' .
            '&href=' . urlencode($url);

respond(200, ['success' => true, 'share_url' => $shareUrl]);

function respond($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}
?>
