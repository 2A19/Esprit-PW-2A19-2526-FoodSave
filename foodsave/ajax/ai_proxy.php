<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST requis']); exit;
}

define('MISTRAL_API_KEY', 'OjBxhkvxqZXDtk0arwH2Gxn8AimoKQ1C');
define('MISTRAL_API_URL', 'https://api.mistral.ai/v1/chat/completions');
define('MISTRAL_MODEL', 'mistral-small-latest');

function callMistralAI(string $prompt): ?string
{
    $ch = curl_init(MISTRAL_API_URL);
    
    $payload = [
        'model' => MISTRAL_MODEL,
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 1000,
        'temperature' => 0.7
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . MISTRAL_API_KEY
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || $http_code !== 200) {
        return null;
    }

    $decoded = json_decode($response, true);
    return $decoded['choices'][0]['message']['content'] ?? null;
}

$action = $_POST['action'] ?? '';
$data   = json_decode($_POST['data'] ?? '{}', true);

if ($action === 'sentiment') {
    $text = $data['text'] ?? '';
    $participant_name = $data['participant_name'] ?? 'Participant';
    $event_name = $data['event_name'] ?? "l'evenement";

    if (trim($text) === '') {
        echo json_encode(['error' => 'Texte vide']); exit;
    }

    $prompt = "Tu es un expert en analyse sentimentale pour des evenements. Analyse le texte suivant d'un participant et reponds EN JSON UNIQUEMENT, sans markdown. Le texte concerne le participant '$participant_name' et l'evenement '$event_name'.

Texte: \"$text\"

Reponds EXACTEMENT en JSON:
{\"sentiment\":\"positif|neutre|negatif\",\"score\":0-100,\"emoji\":\"😊|😐|😟\",\"resume\":\"phrase courte\",\"mots_cles\":[\"mot1\",\"mot2\"]}";

    $response = callMistralAI($prompt);

    if (!$response) {
        echo json_encode(['error' => 'Erreur Mistral.ai']); exit;
    }

    $parsed = @json_decode($response, true);
    
    if (!$parsed || !isset($parsed['sentiment'])) {
        $parsed = [
            'sentiment' => 'neutre',
            'score' => 50,
            'emoji' => '😐',
            'resume' => 'Analyse par Mistral.ai',
            'mots_cles' => ['feedback', 'evenement']
        ];
    }

    echo json_encode(['success' => true, 'data' => $parsed]);
    exit;
}

elseif ($action === 'recommend') {
    $ctx = $data;
    
    $total_participants = $ctx['total_participants'] ?? 0;
    $confirmed_count = $ctx['confirmed'] ?? 0;
    $pending_count = $ctx['pending'] ?? 0;
    $cancelled_count = $ctx['cancelled'] ?? 0;
    $total_events = $ctx['total_events'] ?? 0;
    $upcoming_events = $ctx['upcoming_events'] ?? 0;

    $prompt = "Tu es un expert en gestion d'evenements pour FoodSave. Analyse ces donnees et propose 3 recommandations EN JSON:

DONNEES: Total participants: $total_participants, Confirmes: $confirmed_count, En attente: $pending_count, Annules: $cancelled_count, Total evenements: $total_events, A venir: $upcoming_events

Reponds EXACTEMENT en JSON:
{\"recommendations\":[{\"titre\":\"...\",\"description\":\"...\",\"priorite\":\"haute\",\"icone\":\"🎯\"},{\"titre\":\"...\",\"description\":\"...\",\"priorite\":\"moyenne\",\"icone\":\"📊\"},{\"titre\":\"...\",\"description\":\"...\",\"priorite\":\"basse\",\"icone\":\"💡\"}]}";

    $response = callMistralAI($prompt);

    if (!$response) {
        $parsed = [
            'recommendations' => [
                ['titre' => '📢 Ameliorer la communication', 'description' => 'Augmentez les rappels et notifications.', 'priorite' => 'moyenne', 'icone' => '🎯'],
                ['titre' => '📊 Analyser les tendances', 'description' => 'Identifiez les creneaux populaires.', 'priorite' => 'moyenne', 'icone' => '📋'],
                ['titre' => '🌟 Valoriser les participants', 'description' => 'Creez un systeme de recompenses.', 'priorite' => 'moyenne', 'icone' => '💡']
            ]
        ];
    } else {
        $parsed = @json_decode($response, true);
        if (!$parsed || !isset($parsed['recommendations'])) {
            $parsed = ['recommendations' => [['titre' => '🤖 IA', 'description' => 'Analyse par Mistral.ai', 'priorite' => 'moyenne', 'icone' => '✨']]];
        }
    }

    echo json_encode(['success' => true, 'data' => $parsed]);
    exit;
}

else {
    echo json_encode(['error' => 'Action inconnue : ' . htmlspecialchars($action)]); exit;
}
