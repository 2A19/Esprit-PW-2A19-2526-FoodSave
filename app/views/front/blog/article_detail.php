<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - <?php echo htmlspecialchars($article['titre']); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chemin CSS absolu (comme les autres pages) -->
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
            <a href="index.php?action=blog" class="nav-link">Blog</a>
            <a href="index.php?action=conseils" class="nav-link">Conseils</a>
            <a href="index.php?action=recettes" class="nav-link">Recettes</a>
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
            <span style="background: #4caf50; color: white; padding: 5px 15px; border-radius: 50px; display: inline-block; margin-bottom: 15px;">
                <?php echo htmlspecialchars($article['categorie']); ?>
            </span>
            <h1 class="section-title"><?php echo htmlspecialchars($article['titre']); ?></h1>
            <p class="section-subtitle">📅 Publié le <?php echo date('d/m/Y', strtotime($article['created_at'])); ?> • 👁️ <?php echo $article['vue']; ?> vues</p>
        </div>

        <div style="max-width: 800px; margin: 0 auto;">
            <?php if($article['image']): ?>
                <img src="/FoodSave/public/uploads/<?php echo $article['image']; ?>" alt="<?php echo htmlspecialchars($article['titre']); ?>" style="width: 100%; border-radius: 20px; margin-bottom: 30px;">
            <?php endif; ?>
            
            <div class="article-content">
                <?php echo nl2br(htmlspecialchars($article['contenu'])); ?>
            </div>
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