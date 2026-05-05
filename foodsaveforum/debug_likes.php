<?php
require_once __DIR__ . '/Model/Database.php';
require_once __DIR__ . '/config.php';

$db = config::getConnexion();

$sql = "SELECT 
            p.id_post,
            p.titre,
            COUNT(CASE WHEN pl.type_reaction = 'like' THEN 1 END) as likes,
            COUNT(CASE WHEN pl.type_reaction = 'dislike' THEN 1 END) as dislikes
        FROM posts p
        LEFT JOIN post_likes pl ON p.id_post = pl.id_post
        GROUP BY p.id_post, p.titre
        ORDER BY likes DESC";

$stmt = $db->prepare($sql);
$stmt->execute();
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($stats);
echo "</pre>";

// Aussi afficher le contenu brut de post_likes
echo "<h2>Contenu de post_likes:</h2>";
$raw = $db->query("SELECT * FROM post_likes")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($raw);
echo "</pre>";
?>
