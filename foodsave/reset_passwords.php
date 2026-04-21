<?php
/**
 * RESET PASSWORDS - Hache les mots de passe correctement
 * 
 * Exécutez ce script une seule fois pour réinitialiser les mots de passe
 * Mots de passe par défaut:
 * - admin@foodsave.com: Admin@12345
 * - user@foodsave.com: User@12345
 * - test@foodsave.com: Test@12345
 */

include 'config/Database.php';

echo "=== RÉINITIALISATION DES MOTS DE PASSE ===\n\n";

try {
    $db = config::getConnexion();
    echo "✅ Connexion à la base de données réussie\n\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
    exit;
}

// Mots de passe à hasher
$credentials = [
    [
        'email' => 'admin@foodsave.com',
        'password' => 'Admin@12345',
        'prenom' => 'Admin',
        'nom' => 'Administrateur',
        'role' => 'admin'
    ],
    [
        'email' => 'user@foodsave.com',
        'password' => 'User@12345',
        'prenom' => 'Jean',
        'nom' => 'Dupont',
        'role' => 'user'
    ],
    [
        'email' => 'test@foodsave.com',
        'password' => 'Test@12345',
        'prenom' => 'Marie',
        'nom' => 'Martin',
        'role' => 'user'
    ]
];

foreach ($credentials as $cred) {
    // Hasher le mot de passe avec bcrypt
    $hashedPassword = password_hash($cred['password'], PASSWORD_BCRYPT, ['cost' => 10]);
    
    // Vérifier si l'utilisateur existe
    $checkQuery = 'SELECT id FROM user WHERE email = :email';
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':email', $cred['email']);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        // UPDATE
        $updateQuery = 'UPDATE user SET password = :password WHERE email = :email';
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':password', $hashedPassword);
        $updateStmt->bindParam(':email', $cred['email']);
        
        if ($updateStmt->execute()) {
            echo "✅ {$cred['email']}: Mot de passe mis à jour\n";
            echo "   Mot de passe: {$cred['password']}\n";
        } else {
            echo "❌ {$cred['email']}: Erreur lors de la mise à jour\n";
        }
    } else {
        // INSERT
        $insertQuery = 'INSERT INTO user (nom, prenom, email, password, role, statut, date_inscription) 
                       VALUES (:nom, :prenom, :email, :password, :role, :statut, NOW())';
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':nom', $cred['nom']);
        $insertStmt->bindParam(':prenom', $cred['prenom']);
        $insertStmt->bindParam(':email', $cred['email']);
        $insertStmt->bindParam(':password', $hashedPassword);
        $insertStmt->bindParam(':role', $cred['role']);
        $statut = 'actif';
        $insertStmt->bindParam(':statut', $statut);
        
        if ($insertStmt->execute()) {
            echo "✅ {$cred['email']}: Utilisateur créé\n";
            echo "   Mot de passe: {$cred['password']}\n";
        } else {
            echo "❌ {$cred['email']}: Erreur lors de la création\n";
        }
    }
}

echo "\n=== DONE ===\n";
echo "Essayez maintenant de vous connecter avec:\n";
echo "📧 admin@foodsave.com / 🔐 Admin@12345\n";
echo "ou\n";
echo "📧 user@foodsave.com / 🔐 User@12345\n";
?>
