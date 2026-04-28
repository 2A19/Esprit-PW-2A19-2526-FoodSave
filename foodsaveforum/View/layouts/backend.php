<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'FoodSave - BackOffice'; ?></title>
    <link rel="stylesheet" href="/foodsaveforum/public/assets/css/style.css?v=1.2">
</head>
<body class="admin-dashboard">
    <header class="admin-header">
        <div class="container admin-header-inner">
            <a href="admin.php?action=dashboard" class="logo logo-brand">
                <img src="/foodsaveforum/public/assets/images/logo-foodsave.svg?v=20260421_v2" alt="FoodSave Logo" class="logo-image">
            </a>
            <div class="admin-user-chip">
                <span class="admin-avatar">👤</span>
                <span>Admin</span>
            </div>
        </div>
    </header>

    <div class="admin-shell container">
        <aside class="admin-sidebar">
            <ul>
                <li><a href="admin.php?action=dashboard">Tableau de bord</a></li>
                <li><a href="admin.php?action=posts">Sujets</a></li>
                <li><a href="admin.php?action=commentaires">Messages</a></li>
                <li><a href="index.php?action=posts">Retour au front</a></li>
                <li><a href="#logout">Déconnexion</a></li>
            </ul>
        </aside>

        <main class="admin-content page-card">
            <?php
            if (isset($errors) && !empty($errors)) {
                echo '<div class="alert alert-danger">';
                foreach ($errors as $error) {
                    echo '<p>' . htmlspecialchars($error) . '</p>';
                }
                echo '</div>';
            }
            if (isset($success) && $success) {
                echo '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
            }
            ?>
            <?php include $content; ?>
        </main>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 FoodSave - Panel d'Administration</p>
        </div>
    </footer>

    <script src="/foodsaveforum/public/assets/js/script.js"></script>
</body>
</html>
