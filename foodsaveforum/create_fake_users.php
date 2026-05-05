<?php
require_once __DIR__ . '/Model/Database.php';
require_once __DIR__ . '/config.php';

$db = config::getConnexion();

try {
    // Créer 200 utilisateurs fictifs
    for ($i = 100; $i <= 200; $i++) {
        $sql = "INSERT IGNORE INTO utilisateurs (username, email, password, statue) 
                VALUES (?, ?, ?, 'actif')";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'user_' . $i,
            'user' . $i . '@example.com',
            password_hash('password', PASSWORD_DEFAULT)
        ]);
    }
    
    echo "✓ 101 utilisateurs fictifs créés!<br>";
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
