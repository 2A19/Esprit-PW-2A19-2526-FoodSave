<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Recettes anti-gaspillage</title>
    
    <!-- Police et icônes -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/FoodSave/public/assets/css/style.css">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container nav-container">
            <div class="nav-logo">
                <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="FoodSave Logo" style="height: 45px; width: auto; margin-right: 10px;">
                <span style="font-weight: 700; font-size: 1.4rem;">
                    <span style="color: #ff6b35;">Food</span><span style="color: #4caf50;">Save</span>
                </span>
            </div>
            <div class="nav-menu">
                <a href="index.php?action=blog" class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] == 'blog') ? 'active' : ''; ?>">Accueil</a>
                <a href="index.php?action=blog" class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] == 'blog') ? 'active' : ''; ?>">Blog</a>
                <a href="index.php?action=conseils" class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] == 'conseils') ? 'active' : ''; ?>">Conseils</a>
                <a href="index.php?action=recettes" class="nav-link active">Recettes</a>
            </div>
            <div class="user-actions">
                <button class="login-btn login-outline">Connexion</button>
                <button class="login-btn login-primary">Inscription</button>
            </div>
        </div>
    </nav>

    <!-- Section Recettes (contenu statique + articles dynamiques) -->
    <section class="features" style="padding-top: 120px;">
        <div class="container">
            <div class="section-header">
                <h1 class="section-title">🍳 Recettes anti-gaspillage</h1>
                <p class="section-subtitle">Des idées pour cuisiner vos restes et épluchures</p>
            </div>

            <div class="features-grid">
                <!-- ========== CARTES STATIQUES (recettes fixes) ========== -->
                <div class="feature-card">
                    <div class="feature-icon">🥣</div>
                    <h3>Soupe aux épluchures de légumes</h3>
                    <p><strong>Ingrédients :</strong> Épluchures de carottes, pommes de terre, poireaux</p>
                    <p><strong>Préparation :</strong> Lavez les épluchures, faites-les revenir, ajoutez de l'eau et mixez.</p>
                    <p style="color: #4caf50; margin-top: 10px;">✅ Économie : 2€ par repas</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🍞</div>
                    <h3>Pain perdu aux fruits abîmés</h3>
                    <p><strong>Ingrédients :</strong> Pain rassis, fruits mous (pommes, bananes), œufs, lait</p>
                    <p><strong>Préparation :</strong> Mélangez, faites cuire à la poêle.</p>
                    <p style="color: #4caf50; margin-top: 10px;">✅ Économie : 3€ par recette</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🍪</div>
                    <h3>Cake aux épluchures de pommes</h3>
                    <p><strong>Ingrédients :</strong> Épluchures de pommes, farine, sucre, œufs, beurre</p>
                    <p><strong>Préparation :</strong> Mélangez, enfournez 30 min à 180°C.</p>
                    <p style="color: #4caf50; margin-top: 10px;">✅ Économie : 1.50€ par cake</p>
                </div>

                <!-- ========== ARTICLES DYNAMIQUES (catégorie Recettes) ========== -->
                <?php if(isset($articles) && !empty($articles)): ?>
                    <?php foreach($articles as $article): ?>
                    <div class="feature-card">
                        <div class="feature-icon">🍳</div>
                        <h3><?php echo htmlspecialchars($article['titre']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($article['resume'], 0, 100)) . '...'; ?></p>
                        <a href="index.php?action=detail&id=<?php echo $article['id']; ?>" class="btn-redirect" style="margin-top: 15px; display: inline-block;">Lire la suite →</a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
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