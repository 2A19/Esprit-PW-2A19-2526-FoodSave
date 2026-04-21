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
        <div class="container header-inner">
            <div class="logo logo-brand">
                <a href="admin.php?action=dashboard">
                    <img src="/foodsaveforum/public/assets/images/logo-foodsave.svg?v=20260421_v2" alt="FoodSave Logo" class="logo-image">
                </a>
                <div class="logo-text">
                </div>
            </div>
            <nav class="admin-navbar">
                <ul>
                    <li><a href="admin.php?action=dashboard">Dashboard</a></li>
                    <li><a href="admin.php?action=posts">Gérer Posts</a></li>
                    <li><a href="admin.php?action=commentaires">Gérer Commentaires</a></li>
                    <li><a href="index.php?action=posts">Retour au Front</a></li>
                    <li><a href="#logout">Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-content">
        <div class="container">
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
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 FoodSave - Panel d'Administration</p>
        </div>
    </footer>

    <script src="/foodsaveforum/public/assets/js/script.js"></script>
</body>
</html>
