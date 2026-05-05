<?php
require_once __DIR__ . '/config.php';

$db = config::getConnexion();

try {
    $db->exec("ALTER TABLE posts ADD COLUMN audio_path VARCHAR(255) NULL AFTER contenu");
    echo "Colonne posts.audio_path ajoutée.\n";
} catch (Exception $e) {
    if (stripos($e->getMessage(), 'Duplicate column name') === false) {
        echo "Erreur posts.audio_path: " . $e->getMessage() . "\n";
    }
}

try {
    $db->exec("ALTER TABLE commentaires ADD COLUMN audio_path VARCHAR(255) NULL AFTER contenu");
    echo "Colonne commentaires.audio_path ajoutée.\n";
} catch (Exception $e) {
    if (stripos($e->getMessage(), 'Duplicate column name') === false) {
        echo "Erreur commentaires.audio_path: " . $e->getMessage() . "\n";
    }
}

echo "Migration audio terminée.\n";
?>
