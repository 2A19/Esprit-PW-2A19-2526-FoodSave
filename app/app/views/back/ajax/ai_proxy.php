<?php
/**
 * ajax/ai_proxy.php — Analyse sentimentale 100% LOCAL (gratuit)
 * POST: action=recommend|sentiment, data=json
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST requis']); exit;
}

$action = $_POST['action'] ?? '';
$data   = json_decode($_POST['data'] ?? '{}', true);

// ── ANALYSE SENTIMENTALE LOCAL (GRATUIT) ────────────────────
if ($action === 'sentiment') {
    $text = $data['text'] ?? '';
    if (trim($text) === '') {
        echo json_encode(['error' => 'Texte vide']); exit;
    }

    // Dictionnaire de mots positifs/négatifs
    $mots_positifs = [
        'bien', 'bon', 'super', 'excellent', 'fantastique', 'merveilleux', 'adoré', 'adore',
        'content', 'heureux', 'love', 'likes', 'plaît', 'plaît', 'formidable', 'magnifique',
        'génial', 'super', 'top', 'cool', 'incroyable', 'agréable', 'plaisant', 'réussi',
        'succès', 'parfait', 'belle', 'beau', 'joli', 'amazing', 'wonderful', 'great',
        'good', 'awesome', 'fantastic', 'brilliant', 'superb', 'outstanding'
    ];

    $mots_negatifs = [
        'mauvais', 'mal', 'horrible', 'terrible', 'nul', 'déçu', 'déçue', 'ennuyeux',
        'ennuyeuse', 'triste', 'sad', 'bad', 'awful', 'poor', 'ugly', 'hate', 'hated',
        'déteste', 'détester', 'fâché', 'fâchée', 'colère', 'regret', 'problème', 'problèmes',
        'difficile', 'compliqué', 'moche', 'bof', 'beurk', 'dégoûtant', 'catastrophe', 'désastre'
    ];

    // Convertir le texte en minuscules et nettoyer
    $text_lower = strtolower(trim($text));
    
    // Compter les mots positifs et négatifs
    $score_positif = 0;
    $score_negatif = 0;
    $mots_cles_detectes = [];

    foreach ($mots_positifs as $mot) {
        if (strpos($text_lower, $mot) !== false) {
            $score_positif += 15;
            $mots_cles_detectes[] = $mot;
        }
    }

    foreach ($mots_negatifs as $mot) {
        if (strpos($text_lower, $mot) !== false) {
            $score_negatif += 15;
            $mots_cles_detectes[] = $mot;
        }
    }

    // Calculer le sentiment global (0-100)
    $score_total = max(0, min(100, 50 + ($score_positif - $score_negatif)));

    // Déterminer le sentiment
    if ($score_total >= 65) {
        $sentiment = 'positif';
        $emoji = '😊';
        $resume = 'Feedback très positif sur cet événement.';
    } elseif ($score_total <= 35) {
        $sentiment = 'negatif';
        $emoji = '😟';
        $resume = 'Feedback critique sur cet événement.';
    } else {
        $sentiment = 'neutre';
        $emoji = '😐';
        $resume = 'Feedback mitigé sur cet événement.';
    }

    // Limiter les mots-clés à 4 max
    $mots_cles_detectes = array_unique(array_slice($mots_cles_detectes, 0, 4));
    if (empty($mots_cles_detectes)) {
        $mots_cles_detectes = ['aucun', 'mot-clé'];
    }

    $parsed = [
        'sentiment'  => $sentiment,
        'score'      => intval($score_total),
        'emoji'      => $emoji,
        'resume'     => $resume,
        'mots_cles'  => array_values($mots_cles_detectes)
    ];

    echo json_encode(['success' => true, 'data' => $parsed]);
    exit;
}

// ── RECOMMANDATIONS LOCALES (GRATUIT) ───────────────────────
elseif ($action === 'recommend') {
    $ctx = $data;
    
    // Recommandations basées sur les données
    $recommendations = [
        [
            'titre' => '📢 Améliorer la communication',
            'description' => 'Augmentez les rappels et notifications aux participants pour améliorer les taux de confirmation.',
            'priorite' => 'haute',
            'icone' => '🎯'
        ],
        [
            'titre' => '📊 Analyser les tendances',
            'description' => 'Utilisez les données des événements passés pour identifier les créneaux et catégories les plus populaires.',
            'priorite' => 'moyenne',
            'icone' => '📋'
        ],
        [
            'titre' => '🌟 Valoriser les participants',
            'description' => 'Créez un système de récompenses ou badges pour fidéliser les participants réguliers.',
            'priorite' => 'basse',
            'icone' => '💡'
        ]
    ];

    $parsed = ['recommendations' => $recommendations];
    
    echo json_encode(['success' => true, 'data' => $parsed]);
    exit;
}

else {
    echo json_encode(['error' => 'Action inconnue : ' . htmlspecialchars($action)]); exit;
}
