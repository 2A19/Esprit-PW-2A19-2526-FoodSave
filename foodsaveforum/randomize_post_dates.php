<?php
require_once __DIR__ . '/config.php';

$db = config::getConnexion();

try {
    $posts = $db->query("SELECT id_post FROM posts ORDER BY id_post ASC")->fetchAll(PDO::FETCH_COLUMN);

    if (empty($posts)) {
        echo "Aucun post trouvé.\n";
        exit;
    }

    $start = new DateTime('2026-02-01 08:00:00');
    $end = new DateTime();
    $startTs = $start->getTimestamp();
    $endTs = $end->getTimestamp();

    $updateStmt = $db->prepare("UPDATE posts SET date_creation = :date_creation WHERE id_post = :id_post");

    foreach ($posts as $postId) {
        $randomTs = random_int($startTs, $endTs);
        $randomDate = date('Y-m-d H:i:s', $randomTs);
        $updateStmt->execute([
            ':date_creation' => $randomDate,
            ':id_post' => $postId
        ]);
    }

    echo "Dates mises à jour pour " . count($posts) . " posts.\n";
    echo "Période: du " . $start->format('Y-m-d') . " à aujourd'hui.\n";
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
