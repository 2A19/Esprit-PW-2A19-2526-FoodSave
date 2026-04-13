<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'FoodSave Forum'; ?></title>
    <link rel="stylesheet" href="/foodsaveforum/public/assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-inner">
            <div class="logo logo-brand">
                <a href="index.php?action=posts">
                    <img src="/foodsaveforum/public/assets/images/logo-foodsave.svg" alt="FoodSave Logo" class="logo-image">
                </a>
                <div class="logo-text">
                    <span class="brand-name">FoodSave</span>
                    <span class="brand-subtitle">Forum Anti-Gaspillage</span>
                </div>
            </div>
            <nav class="navbar">
                <ul>
                    <li><a href="index.php?action=posts">Forum</a></li>
                    <li><a href="index.php?action=create-post">Créer un post</a></li>
                    <li><a href="#profile">Mon Profil</a></li>
                    <li><a href="#logout">Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
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
            <p>&copy; 2026 FoodSave - Plateforme Anti-Gaspillage. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="/foodsaveforum/public/assets/js/script.js"></script>
</body>
</html>
