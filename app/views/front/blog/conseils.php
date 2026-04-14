<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Conseils anti-gaspillage</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/FoodSave/public/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-container">
        <div class="nav-logo">
            <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="FoodSave Logo" style="height: 45px; width: auto; margin-right: 10px;">
            <span style="font-weight: 700; font-size: 1.4rem;">
                <span style="color: #ff6b35;">Food</span><span style="color: #4caf50;">Save</span>
            </span>
        </div>
        <div class="nav-menu">
            <a href="index.php?action=blog" class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] == 'blog') ? 'active' : ''; ?>">Blog</a>
            <a href="index.php?action=conseils" class="nav-link <?php echo (!isset($_GET['action']) || $_GET['action'] == 'conseils') ? 'active' : ''; ?>">Conseils</a>
            <a href="index.php?action=recettes" class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] == 'recettes') ? 'active' : ''; ?>">Recettes</a>
        </div>
        <div class="user-actions">
            <button class="login-btn login-outline">Connexion</button>
            <button class="login-btn login-primary">Inscription</button>
        </div>
    </div>
</nav>

<section class="features" style="padding-top: 120px;">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">💡 Tous nos conseils</h1>
            <p class="section-subtitle">Des astuces simples à appliquer au quotidien</p>
        </div>

        <div class="features-grid">
            <?php foreach($articles as $article): ?>
            <div class="feature-card">
                <div class="feature-icon">💡</div>
                <h3><?php echo htmlspecialchars($article['titre']); ?></h3>
                <p><?php echo htmlspecialchars(substr($article['resume'], 0, 100)) . '...'; ?></p>
                <a href="index.php?action=detail&id=<?php echo $article['id']; ?>" class="btn-redirect" style="margin-top: 15px; display: inline-block;">Lire la suite →</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <span>🍽️ FoodSave</span>
            <p>Stop au gaspillage alimentaire</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2025 FoodSave - Tous droits réservés</p>
    </div>
</footer>

</body>
</html>