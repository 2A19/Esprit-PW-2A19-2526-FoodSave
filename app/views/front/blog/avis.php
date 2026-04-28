<?php
// Vérifier que les variables existent
if(!isset($article) || !isset($avis) || !isset($nbAvis) || !isset($noteMoyenne)) {
    header('Location: index.php?action=blog');
    exit;
}

// Récupérer l'ID de l'utilisateur connecté (temporaire)
$user_id = $_SESSION['user_id'] ?? 1;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Avis des lecteurs</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/FoodSave/public/assets/css/style.css">
    
    <style>
        .avis-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .avis-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e8e0c8;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }
        
        .avis-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        
        .avis-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .avis-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4caf50, #ff6b35);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .author-info h4 {
            margin: 0;
            font-size: 1rem;
        }
        
        .author-info .date {
            font-size: 0.7rem;
            color: #888;
        }
        
        .avis-note {
            display: flex;
            gap: 5px;
        }
        
        .star {
            color: #ffc107;
            font-size: 1rem;
        }
        
        .star-empty {
            color: #ddd;
        }
        
        .avis-content {
            color: #555;
            line-height: 1.6;
            margin: 1rem 0;
        }
        
        .btn-avis {
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-avis:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .avis-stats {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(255, 107, 53, 0.05));
            padding: 1rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .avis-stats .note-moyenne {
            font-size: 2rem;
            font-weight: 800;
            color: #4caf50;
        }
        
        .avis-vide {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 20px;
            color: #888;
        }
        
        .btn-back {
            display: inline-block;
            margin-bottom: 1rem;
            color: #4caf50;
            text-decoration: none;
        }
        
        .btn-back:hover {
            text-decoration: underline;
        }
        
        /* Style pour le bouton Modifier */
        .btn-modifier {
            display: inline-block;
            margin-top: 10px;
            color: #ff6b35;
            text-decoration: none;
            font-size: 0.8rem;
        }
        
        .btn-modifier:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="container nav-container">
            <div class="nav-logo">
                <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="Logo" style="height: 50px; width: auto;">
                <span style="font-weight: 700; font-size: 1.4rem;">
                    <span style="color: #ff6b35;">Food</span><span style="color: #4caf50;">Save</span>
                </span>
            </div>
            <div class="nav-menu">
                <a href="index.php?action=blog" class="nav-link">Accueil</a>
                <a href="index.php?action=conseils" class="nav-link">Conseils</a>
                <a href="index.php?action=recettes" class="nav-link">Recettes</a>
            </div>
            <div class="user-actions">
                <button class="login-btn login-outline">Connexion</button>
                <button class="login-btn login-primary">Inscription</button>
            </div>
        </div>
    </nav>

    <div class="avis-container">
        
        <!-- Lien retour à l'article -->
        <a href="index.php?action=detail&id=<?php echo $article['id']; ?>" class="btn-back">
            ← Retour à l'article
        </a>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="color: #333;">Avis des lecteurs</h1>
            <a href="index.php?action=addAvisForm&article_id=<?php echo $article['id']; ?>" class="btn-avis">
                <i class="fas fa-pen"></i> Donner mon avis
            </a>
        </div>
        
        <!-- Stats des avis -->
        <div class="avis-stats">
            <?php if($nbAvis > 0): ?>
                <div class="note-moyenne"><?php echo $noteMoyenne; ?> / 5</div>
                <div class="avis-note" style="justify-content: center;">
                    <?php
                    $fullStars = floor($noteMoyenne);
                    $halfStar = ($noteMoyenne - $fullStars) >= 0.5;
                    for($i = 1; $i <= 5; $i++) {
                        if($i <= $fullStars) {
                            echo '<i class="fas fa-star star"></i>';
                        } elseif($halfStar && $i == $fullStars + 1) {
                            echo '<i class="fas fa-star-half-alt star"></i>';
                        } else {
                            echo '<i class="far fa-star star-empty"></i>';
                        }
                    }
                    ?>
                </div>
                <div style="font-size: 0.8rem; color: #666;">Basé sur <?php echo $nbAvis; ?> avis</div>
            <?php else: ?>
                <div class="note-moyenne">-- / 5</div>
                <div class="avis-note" style="justify-content: center;">
                    <i class="far fa-star star-empty"></i>
                    <i class="far fa-star star-empty"></i>
                    <i class="far fa-star star-empty"></i>
                    <i class="far fa-star star-empty"></i>
                    <i class="far fa-star star-empty"></i>
                </div>
                <div style="font-size: 0.8rem; color: #666;">Aucun avis pour le moment</div>
            <?php endif; ?>
        </div>
        
        <!-- Liste des avis -->
        <div class="avis-list">
            <?php if(empty($avis)): ?>
                <div class="avis-vide">
                    <i class="fas fa-comment-dots" style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
                    <p>Soyez le premier à donner votre avis sur cet article !</p>
                </div>
            <?php else: ?>
                <?php foreach($avis as $a): ?>
                <div class="avis-card">
                    <div class="avis-header">
                        <div class="avis-author">
                            <div class="avatar">
                                <?php echo strtoupper(substr($a['user_name'], 0, 1)); ?>
                            </div>
                            <div class="author-info">
                                <h4><?php echo htmlspecialchars($a['user_name']); ?></h4>
                                <div class="date">Publié le <?php echo date('d/m/Y', strtotime($a['created_at'])); ?></div>
                            </div>
                        </div>
                        <div class="avis-note">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php if($i <= $a['note']): ?>
                                    <i class="fas fa-star star"></i>
                                <?php else: ?>
                                    <i class="far fa-star star-empty"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="avis-content">
                        "<?php echo nl2br(htmlspecialchars($a['contenu'])); ?>"
                    </div>
                    
                    <!-- Bouton MODIFIER MON AVIS (uniquement si l'utilisateur est l'auteur) -->
                    <?php if($a['user_id'] == $user_id): ?>
                    <div>
                        <a href="index.php?action=editUserAvis&id=<?php echo $a['id']; ?>" class="btn-modifier">
                            <i class="fas fa-edit"></i> Modifier mon avis
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

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