<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/Database.php';

$db = new Database();
$conn = $db->connect();

// Mot de passe cible pour l'admin
$password = 'Admin123456';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

echo "<h2>Correction du compte Admin</h2>";

// Supprimer l'admin existant (au cas où)
$query = "DELETE FROM `user` WHERE email = 'admin@foodsave.com'";
$stmt = $conn->prepare($query);
$stmt->execute();
echo "<p>✓ Ancien admin supprimé (s'il existait)</p>";

// Insérer le nouvel admin avec le nouveau hash
$query = "INSERT INTO `user` (nom, prenom, email, password, role, statut, telephone, date_naissance, date_inscription)
VALUES ('FoodSave', 'Admin', 'admin@foodsave.com', :password, 'admin', 'actif', '0600000000', '1980-01-01', NOW())";
$stmt = $conn->prepare($query);
$stmt->bindParam(':password', $hash);
$result = $stmt->execute();

if ($result) {
    echo "<p style='color: green; font-weight: bold;'>✓ Admin créé avec succès!</p>";
} else {
    echo "<p style='color: red;'>✗ Erreur lors de la création</p>";
}

// Afficher les informations
echo "<hr>";
echo "<h3>Identifiants de connexion:</h3>";
echo "<p><strong>Email:</strong> admin@foodsave.com</p>";
echo "<p><strong>Mot de passe:</strong> Admin123456</p>";
echo "<hr>";
echo "<p><a href='index.php?action=login'>← Retour au login</a></p>";
echo "<p style='color: #999; font-size: 0.9em;'>Hash généré: " . substr($hash, 0, 20) . "...</p>";
?>
