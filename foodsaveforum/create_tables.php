<?php
// Create post_likes table
require_once __DIR__ . '/Model/Database.php';
require_once __DIR__ . '/config.php';

$db = config::getConnexion();

try {
    $sql = "CREATE TABLE IF NOT EXISTS post_likes (
        id_like INT AUTO_INCREMENT PRIMARY KEY,
        id_post INT NOT NULL,
        id_utilisateur INT NOT NULL,
        type_reaction VARCHAR(10) NOT NULL COMMENT 'like ou dislike',
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (id_post) REFERENCES posts(id_post) ON DELETE CASCADE,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE,
        
        UNIQUE KEY unique_user_post_reaction (id_post, id_utilisateur),
        INDEX idx_id_post (id_post),
        INDEX idx_id_utilisateur (id_utilisateur),
        INDEX idx_type_reaction (type_reaction)
        
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->exec($sql);
    echo "✓ Table 'post_likes' créée avec succès!";
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
