<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'FoodSave Forum'; ?></title>
    <link rel="stylesheet" href="/foodsaveforum/public/assets/css/style.css?v=1.2">
</head>
<body class="front-office">
    <header class="header">
        <div class="container header-inner">
            <div class="logo logo-brand">
                <a href="index.php?action=posts">
                    <img src="/foodsaveforum/public/assets/images/logo-foodsave.svg?v=20260421_v2" alt="FoodSave Logo" class="logo-image">
                </a>
            </div>
            <nav class="navbar">
                <ul>
                    <li><a href="index.php?action=posts">Accueil</a></li>
                    <li><a href="index.php?action=posts">Catégories</a></li>
                    <li><a href="index.php?action=posts">Sujets récents</a></li>
                    <li><a href="index.php?action=create-post">Créer un post</a></li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li><a href="admin.php?action=dashboard">Administration</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="header-actions">
                <input type="text" class="search-input" placeholder="Rechercher...">
                <a href="#profile" class="btn-small btn-secondary">Connexion</a>
                <a href="#logout" class="btn-small btn-warning">S'inscrire</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container page-card">
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
