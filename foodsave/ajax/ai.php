<?php
/**
 * FoodSave — API : ai.php
 * Assistant IA propulsé par Mistral AI
 */

require_once __DIR__ . '/../config/Database.php';
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
if (!$input || !isset($input['question'])) {
    respond(400, ['error' => 'Question required']);
}

$question = trim($input['question']);
if (empty($question)) {
    respond(400, ['error' => 'Question cannot be empty']);
}

// Historique de conversation optionnel (tableau de {role, content})
$history = isset($input['history']) && is_array($input['history']) ? $input['history'] : [];

$response = generateMistralResponse($question, $history);

respond(200, ['success' => true, 'response' => $response]);

// ---------------------------------------------------------------------------

function generateMistralResponse(string $question, array $history): string {
    $apiKey = ApiKeys::MISTRAL_API_KEY;

    $systemPrompt = <<<SYSTEM
Tu es l'assistant IA de FoodSave, une application de gestion anti-gaspillage alimentaire.
Tu aides les utilisateurs à :
- Organiser et planifier des collectes alimentaires
- Gérer les déchets, catégories et métiers
- Utiliser les fonctionnalités avancées (SMS, QR codes, calendrier .ics, export CSV/PDF, partage Facebook)
- Analyser les statistiques (CO2 économisé, volumes, tendances)
- Réduire le gaspillage alimentaire de façon efficace

Réponds toujours en français, de façon concise, bienveillante et pratique.
Si la question n'est pas liée à FoodSave ou au gaspillage alimentaire, recentre poliment la conversation.
SYSTEM;

    // Construction des messages : system + historique + question actuelle
    $messages = [['role' => 'system', 'content' => $systemPrompt]];

    foreach ($history as $entry) {
        if (isset($entry['role'], $entry['content']) &&
            in_array($entry['role'], ['user', 'assistant'])) {
            $messages[] = ['role' => $entry['role'], 'content' => $entry['content']];
        }
    }

    $messages[] = ['role' => 'user', 'content' => $question];

    $payload = json_encode([
        'model'       => 'mistral-small-latest',
        'messages'    => $messages,
        'max_tokens'  => 512,
        'temperature' => 0.7,
    ]);

    $ch = curl_init('https://api.mistral.ai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);

    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log('[FoodSave AI] cURL error: ' . $err);
        return 'Désolé, une erreur réseau est survenue. Veuillez réessayer.';
    }

    $data = json_decode($raw, true);

    if ($code !== 200 || !isset($data['choices'][0]['message']['content'])) {
        error_log('[FoodSave AI] Mistral error ' . $code . ': ' . $raw);
        return 'Désolé, le service IA est temporairement indisponible (code ' . $code . ').';
    }

    return trim($data['choices'][0]['message']['content']);
}

function respond(int $code, array $data): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
