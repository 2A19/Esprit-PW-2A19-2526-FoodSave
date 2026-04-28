<?php
// Démarrer la session UNE SEULE FOIS
session_start();
$user_id = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - <?php echo htmlspecialchars($article['titre']); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chemin CSS absolu -->
    <link rel="stylesheet" href="/FoodSave/public/assets/css/style.css">
    
    <style>
        .avis-section {
            max-width: 800px;
            margin: 2rem auto 0;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            border: 1px solid #e8e0c8;
        }
        
        .avis-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .avis-stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
        
        .avis-note {
            font-size: 1.2rem;
            font-weight: bold;
            color: #4caf50;
        }
        
        .btn-avis {
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-avis:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-voir-avis {
            background: transparent;
            border: 2px solid #4caf50;
            color: #4caf50;
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-voir-avis:hover {
            background: #4caf50;
            color: white;
        }
        
        .avis-preview {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e8e0c8;
        }
        
        .avis-preview-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .avis-preview-item:last-child {
            border-bottom: none;
        }
        
        .avis-preview-author {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .avis-preview-content {
            color: #666;
            font-size: 0.9rem;
        }
        
        .avis-preview-stars {
            color: #ffc107;
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }
        
        .btn-modifier-avis {
            display: inline-block;
            margin-top: 8px;
            color: #ff6b35;
            text-decoration: none;
            font-size: 0.75rem;
        }
        
        .btn-modifier-avis:hover {
            text-decoration: underline;
        }
    </style>
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
        
        <!-- ========== SECTION AVIS ========== -->
        <?php
        require_once __DIR__ . '/../../../models/Avis.php';
        $avisModel = new Avis();
        $article_id = $article['id'];
        
        $nbAvis = $avisModel->countByArticleId($article_id);
        $noteMoyenne = $avisModel->getAverageNote($article_id);
        $derniersAvis = $avisModel->getByArticleId($article_id, 2);
        ?>
        
        <div class="avis-section">
            <div class="avis-header">
                <div>
                    <div class="avis-note"><?php echo $noteMoyenne; ?> / 5</div>
                    <div class="avis-stars">
                        <?php
                        $fullStars = floor($noteMoyenne);
                        $halfStar = ($noteMoyenne - $fullStars) >= 0.5;
                        for($i = 1; $i <= 5; $i++) {
                            if($i <= $fullStars) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif($halfStar && $i == $fullStars + 1) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <div style="font-size: 0.8rem; color: #888;">Basé sur <?php echo $nbAvis; ?> avis</div>
                </div>
                <div>
                    <a href="index.php?action=addAvisForm&article_id=<?php echo $article['id']; ?>" class="btn-avis">
                        <i class="fas fa-pen"></i> Donner mon avis
                    </a>
                </div>
            </div>
            
            <?php if(!empty($derniersAvis)): ?>
            <div class="avis-preview">
                <h4>Derniers avis</h4>
                <?php foreach($derniersAvis as $a): ?>
                <div class="avis-preview-item">
                    <div class="avis-preview-stars">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php echo ($i <= $a['note']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                        <?php endfor; ?>
                    </div>
                    <div class="avis-preview-author">
                        <?php echo htmlspecialchars($a['user_name']); ?>
                        <span> - <?php echo date('d/m/Y', strtotime($a['created_at'])); ?></span>
                    </div>
                    <div class="avis-preview-content">
                        "<?php echo nl2br(htmlspecialchars(substr($a['contenu'], 0, 100))); ?>..."
                    </div>
                    <?php if($user_id && $a['user_id'] == $user_id): ?>
                    <div>
                        <a href="index.php?action=editUserAvis&id=<?php echo $a['id']; ?>" class="btn-modifier-avis">
                            <i class="fas fa-edit"></i> Modifier mon avis
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <!-- LIEN VERS TOUS LES AVIS (toujours visible) -->
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="index.php?action=showAvis&article_id=<?php echo $article['id']; ?>" class="btn-voir-avis">
                        Voir tous les <?php echo $nbAvis; ?> avis →
                    </a>
                </div>
            </div>
            <?php elseif($nbAvis == 0): ?>
            <div style="text-align: center; padding: 1rem; color: #888;">
                <i class="fas fa-comment-dots"></i>
                <p>Soyez le premier à donner votre avis sur cet article !</p>
            </div>
            <!-- LIEN VERS LA PAGE DES AVIS (même si aucun avis) -->
            <div style="text-align: center; margin-top: 1rem;">
                <a href="index.php?action=showAvis&article_id=<?php echo $article['id']; ?>" class="btn-voir-avis">
                    Voir les avis (0) →
                </a>
            </div>
            <?php endif; ?>
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