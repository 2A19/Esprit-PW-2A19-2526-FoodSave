<?php
/**
 * FoodSave — API : calendar.php
 * Génère une URL pour ajouter un événement au calendrier Google
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
$title = $input['title'] ?? 'FoodSave - Réunion métier avancé';
$description = $input['description'] ?? 'Planifiez une session métier avancé pour améliorer la collecte et réduire le gaspillage alimentaire.';
$location = $input['location'] ?? 'FoodSave';
$startTime = $input['start'] ?? null;
$endTime = $input['end'] ?? null;

if (!$startTime) {
    $start = new DateTime();
    $start->setTime(10, 0);
} else {
    $start = new DateTime($startTime);
}

if (!$endTime) {
    $end = clone $start;
    $end->modify('+1 hour');
} else {
    $end = new DateTime($endTime);
}

$startStr = $start->format('Ymd\THis\Z');
$endStr = $end->format('Ymd\THis\Z');

$url = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=' . urlencode($title) . '&dates=' . $startStr . '/' . $endStr . '&details=' . urlencode($description) . '&location=' . urlencode($location);

respond(200, ['success' => true, 'url' => $url]);

function respond($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}
?>
