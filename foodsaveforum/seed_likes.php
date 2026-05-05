<?php
require_once __DIR__ . '/Model/Database.php';
require_once __DIR__ . '/config.php';

$db = config::getConnexion();

try {
    $posts = $db->query("SELECT id_post FROM posts ORDER BY id_post ASC")->fetchAll(PDO::FETCH_COLUMN);
    $users = $db->query("SELECT id_utilisateur FROM utilisateurs ORDER BY id_utilisateur ASC")->fetchAll(PDO::FETCH_COLUMN);

    if (empty($posts) || empty($users)) {
        echo "Aucun post ou utilisateur trouvé.";
        exit;
    }

    $stmtCount = $db->prepare("SELECT COUNT(*) FROM post_likes WHERE id_post = :id_post");
    $stmtInsert = $db->prepare(
        "INSERT IGNORE INTO post_likes (id_post, id_utilisateur, type_reaction) VALUES (:id_post, :id_utilisateur, :type_reaction)"
    );

    $addedLikes = 0;
    $addedDislikes = 0;
    $updatedPosts = 0;

    foreach ($posts as $postId) {
        $stmtCount->execute([':id_post' => $postId]);
        $existing = (int) $stmtCount->fetchColumn();

        // On ne touche qu'aux posts sans réactions (posts ajoutés récemment, par ex.)
        if ($existing > 0) {
            continue;
        }

        $updatedPosts++;
        $pool = $users;
        shuffle($pool);

        // 2 à 3 réactions max selon nombre d'utilisateurs
        $targetReactions = min(count($pool), random_int(2, min(3, count($pool))));
        $selectedUsers = array_slice($pool, 0, $targetReactions);

        foreach ($selectedUsers as $idx => $userId) {
            // Distribution orientée vers les likes
            $reaction = ($idx === $targetReactions - 1 && $targetReactions > 2) ? 'dislike' : ((random_int(1, 100) <= 75) ? 'like' : 'dislike');
            $stmtInsert->execute([
                ':id_post' => $postId,
                ':id_utilisateur' => $userId,
                ':type_reaction' => $reaction
            ]);

            if ($reaction === 'like') {
                $addedLikes++;
            } else {
                $addedDislikes++;
            }
        }
    }

    echo "Posts mis à jour: {$updatedPosts}\n";
    echo "Likes ajoutés: {$addedLikes}\n";
    echo "Dislikes ajoutés: {$addedDislikes}\n";
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
