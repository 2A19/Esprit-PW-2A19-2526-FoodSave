<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Conseils anti-gaspillage</title>
    
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
                <a href="index.php?action=conseils" class="nav-link active">Conseils</a>
                <a href="index.php?action=recettes" class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] == 'recettes') ? 'active' : ''; ?>">Recettes</a>
            </div>
            <div class="user-actions">
                <button class="login-btn login-outline">Connexion</button>
                <button class="login-btn login-primary">Inscription</button>
            </div>
        </div>
    </nav>

    <!-- Section Conseils (contenu statique + articles dynamiques) -->
    <section class="features" style="padding-top: 120px;">
        <div class="container">
            <div class="section-header">
                <h1 class="section-title">💡 Tous nos conseils</h1>
                <p class="section-subtitle">Des astuces simples à appliquer au quotidien</p>
            </div>

            <div class="features-grid">
                <!-- ========== CARTES STATIQUES (conseils fixes) ========== -->
                <div class="feature-card">
                    <div class="feature-icon">🥕</div>
                    <h3>Rangez votre frigo dans le bon ordre</h3>
                    <p>En haut : produits laitiers. Au milieu : restes. En bas : viande. Bac à légumes : fruits et légumes.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📅</div>
                    <h3>La date limite n'est pas une date de mort</h3>
                    <p>La DDM (Date de Durabilité Minimale) est une recommandation. Après cette date, le produit est encore consommable !</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🛒</div>
                    <h3>Faites une liste de courses</h3>
                    <p>Avant d'aller au supermarché, listez ce dont vous avez vraiment besoin pour éviter les achats impulsifs.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">❄️</div>
                    <h3>Congelez vos surplus</h3>
                    <p>Vous avez trop cuisiné ? Congelez en portions individuelles. Vos légumes vont faner ? Coupez-les et congelez-les !</p>
                </div>

                <!-- ========== ARTICLES DYNAMIQUES (catégorie Conseils) ========== -->
                <?php if(isset($articles) && !empty($articles)): ?>
                    <?php foreach($articles as $article): ?>
                    <div class="feature-card">
                        <div class="feature-icon">💡</div>
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