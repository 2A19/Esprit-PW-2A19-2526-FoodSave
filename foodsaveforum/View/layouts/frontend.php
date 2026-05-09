<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'fr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'FoodSave Forum'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/foodsaveforum/public/assets/css/style.css?v=3.0">
</head>
<body class="front-office">

    <header class="header">
        <div class="container header-inner">
            <!-- Logo -->
            <div class="logo logo-brand">
                <a href="index.php?action=posts">
                    <img src="/foodsaveforum/public/assets/images/logo-foodsave.svg?v=20260421_v2" alt="FoodSave Logo" class="logo-image">
                </a>
            </div>

            <!-- Nav -->
            <nav class="navbar">
                <ul>
                    <li><a href="index.php?action=posts">Accueil</a></li>
                    <li><a href="index.php?action=posts">Catégories</a></li>
                    <li><a href="index.php?action=posts">Forum</a></li>
                    <li><a href="index.php?action=calendar">Calendrier</a></li>
                    <li><a href="index.php?action=create">Créer un post</a></li>
                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <li><a href="admin.php?action=dashboard">Administration</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <!-- Header actions -->
            <div class="header-actions">
                <input type="text" class="search-input" placeholder="Rechercher...">

                <div class="language-selector-container" role="region" aria-label="Sélecteur de langue">
                    <div class="language-selector" role="group" aria-label="Choix de la langue">
                        <a href="?lang=fr"
                           class="lang-btn lang-fr <?php echo isset($_SESSION['lang']) && $_SESSION['lang'] === 'fr' ? 'active' : ''; ?>"
                           title="Français" aria-label="Français"
                           <?php echo isset($_SESSION['lang']) && $_SESSION['lang'] === 'fr' ? 'aria-current="page"' : ''; ?>>
                            <span class="lang-flag" aria-hidden="true">🇫🇷</span>
                            <span class="lang-code">FR</span>
                        </a>
                        <a href="?lang=en"
                           class="lang-btn lang-en <?php echo isset($_SESSION['lang']) && $_SESSION['lang'] === 'en' ? 'active' : ''; ?>"
                           title="English" aria-label="English"
                           <?php echo isset($_SESSION['lang']) && $_SESSION['lang'] === 'en' ? 'aria-current="page"' : ''; ?>>
                            <span class="lang-flag" aria-hidden="true">🇬🇧</span>
                            <span class="lang-code">EN</span>
                        </a>
                    </div>
                </div>

                <a href="/foodsaveforum/foodsave/index.php?action=profile" class="btn btn-small btn-secondary">Profil</a>
                <a href="/foodsaveforum/foodsave/index.php?action=logout" class="btn btn-small btn-primary">Deconnexion</a>
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
            <p><?php
                if (!isset($_SESSION['lang'])) {
                    $_SESSION['lang'] = 'fr';
                }
                $lang = $_SESSION['lang'];
                echo $lang === 'en'
                    ? '© 2026 FoodSave – Anti-Waste Platform. All rights reserved.'
                    : '© 2026 FoodSave – Plateforme Anti-Gaspillage. Tous droits réservés.';
            ?></p>
        </div>
    </footer>

    <script src="/foodsaveforum/public/assets/js/script.js?v=2.0"></script>
</body>
</html>
