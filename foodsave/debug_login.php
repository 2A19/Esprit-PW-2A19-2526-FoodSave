<?php
/**
 * SCRIPT DE DÉBOGAGE LOGIN
 * 
 * Ce script teste la connexion et affiche les détails du problème
 */

session_start();
include 'config/Database.php';

echo "=== DIAGNOSTIC LOGIN ===\n\n";

// 1. Vérifier la connexion BD
try {
    $db = config::getConnexion();
    echo "✅ Connexion BD réussie\n\n";
} catch (Exception $e) {
    echo "❌ Erreur connexion BD: " . $e->getMessage() . "\n";
    exit;
}

// 2. Récupérer tous les utilisateurs
echo "--- UTILISATEURS EN BD ---\n";
$query = 'SELECT id, email, role, statut FROM user';
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($users) === 0) {
    echo "❌ AUCUN UTILISATEUR EN BD!\n";
    echo "Vous devez d'abord exécuter: database_setup.sql\n\n";
} else {
    foreach ($users as $user) {
        echo "- {$user['email']} (ID: {$user['id']}, Role: {$user['role']}, Statut: {$user['statut']})\n";
    }
    echo "\n";
}

// 3. Tester les credentials
$testCredentials = [
    ['email' => 'admin@foodsave.com', 'password' => 'Admin@12345', 'name' => 'Admin'],
    ['email' => 'user@foodsave.com', 'password' => 'User@12345', 'name' => 'User Test'],
    ['email' => 'test@foodsave.com', 'password' => 'Test@12345', 'name' => 'Test User']
];

echo "--- TEST CREDENTIALS ---\n";
foreach ($testCredentials as $cred) {
    $query = 'SELECT id, prenom, nom, email, password, role, statut FROM user WHERE email = :email LIMIT 1';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $cred['email']);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo "❌ {$cred['email']}: UTILISATEUR NON TROUVÉ\n";
    } else {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $passwordHash = $user['password'];
        
        // Test sans htmlspecialchars (CORRECT)
        $isValid = password_verify($cred['password'], $passwordHash);
        
        if ($isValid) {
            echo "✅ {$cred['email']}: LOGIN VALIDE ✓\n";
        } else {
            echo "❌ {$cred['email']}: MOT DE PASSE INVALIDE ✗\n";
            // Aide au débogage
            echo "   Hash stocké: " . substr($passwordHash, 0, 20) . "...\n";
            echo "   Mot de passe testé: {$cred['password']}\n";
        }
    }
}

echo "\n--- SOLUTION ---\n";
echo "Le problème vient probablement de:\n";
echo "1. Les mots de passe ne sont pas hashés correctement en BD\n";
echo "2. Vous utilisez des identifiants erronés\n";
echo "3. Les caractères spéciaux sont mal traités\n\n";

echo "ESSAYEZ CES IDENTIFIANTS:\n";
echo "📧 Email: admin@foodsave.com\n";
echo "🔐 Password: Admin@12345\n\n";

echo "OU\n\n";

echo "📧 Email: user@foodsave.com\n";
echo "🔐 Password: User@12345\n\n";

echo "Si ça ne marche toujours pas, lancez ce script:\n";
echo "php reset_passwords.php\n";
?>
