<?php
$apiKey = 'sU2zIj76Ii7G6u8tjquMtIfwBJDQ1R4P';
$question = "Comment conserver des légumes ?";

$ch = curl_init('https://api.mistral.ai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$data = [
    'model' => 'mistral-tiny',
    'messages' => [
        ['role' => 'user', 'content' => $question]
    ],
    'temperature' => 0.7,
    'max_tokens' => 200
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
if($httpCode === 200) {
    $response = json_decode($result, true);
    echo "✅ Réponse: " . $response['choices'][0]['message']['content'];
} else {
    echo "❌ Erreur: " . $result;
}
?>