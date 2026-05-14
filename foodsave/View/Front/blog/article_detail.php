<?php
// Démarrer la session (pour la langue)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction de traduction simplifiée
function t($key) {
    $translations = [
        'nav_home' => 'Accueil',
        'nav_blog' => 'Blog',
        'nav_tips' => 'Conseils',
        'nav_recipes' => 'Recettes',
        'nav_login' => 'Connexion',
        'nav_register' => 'Inscription',
        'published_on' => 'Publié le',
        'views' => 'vues',
        'based_on' => 'Basé sur',
        'reviews_count' => 'avis',
        'write_review' => 'Écrire un avis',
        'reviews' => 'Avis',
        'edit_review' => 'Modifier mon avis',
        'view_all' => 'Voir tous les',
        'no_reviews' => 'Aucun avis pour le moment',
        'footer_copyright' => 'Tous droits réservés'
    ];
    return $translations[$key] ?? $key;
}

$user_id = $_SESSION['user_id'] ?? null;
$avisModel = new Avis();
$article_id = $article['id'];
$nbAvis = $avisModel->countByArticleId($article_id);
$noteMoyenne = $avisModel->getAverageNote($article_id);
$derniersAvis = $avisModel->getByArticleId($article_id, 2);

// URL pour le partage social
$base_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/foodsaveforum/foodsave/';
$article_full_url = $base_url . 'index.php?action=detail&id=' . $article['id'];

// Extraire l'ID YouTube
function getYouTubeId($url) {
    if (empty($url)) return null;
    $pattern = '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    preg_match($pattern, $url, $matches);
    return $matches[1] ?? null;
}
$video_id = getYouTubeId($article['video_url'] ?? '');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - <?php echo htmlspecialchars($article['titre']); ?></title>
    
    <!-- Balises Open Graph pour le partage social -->
    <meta property="og:title" content="<?php echo htmlspecialchars($article['titre']); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars(substr(strip_tags($article['contenu']), 0, 200)); ?>" />
    <?php if(!empty($article['image'])): ?>
    <meta property="og:image" content="<?php echo $base_url . 'assets/uploads/' . $article['image']; ?>" />
    <?php else: ?>
    <meta property="og:image" content="<?php echo $base_url . 'assets/images/logo-foodsave.png'; ?>" />
    <?php endif; ?>
    <meta property="og:url" content="<?php echo $article_full_url; ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:site_name" content="FoodSave" />
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo htmlspecialchars($article['titre']); ?>" />
    <meta name="twitter:description" content="<?php echo htmlspecialchars(substr(strip_tags($article['contenu']), 0, 200)); ?>" />
    <?php if(!empty($article['image'])): ?>
    <meta name="twitter:image" content="<?php echo $base_url . 'assets/uploads/' . $article['image']; ?>" />
    <?php endif; ?>
    
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:"DM Sans",sans-serif;background:#0d1f14;color:#e8f5e9;overflow-x:hidden}

        .bg-mesh{position:fixed;inset:0;pointer-events:none;z-index:0}
        .glow-1{position:absolute;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(34,197,94,0.18) 0%,transparent 70%);top:-80px;right:-60px;animation:driftA 8s ease-in-out infinite alternate}
        .glow-2{position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(16,185,129,0.12) 0%,transparent 70%);bottom:0;left:-80px;animation:driftB 10s ease-in-out infinite alternate}
        .glow-3{position:absolute;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(234,179,8,0.09) 0%,transparent 70%);top:40%;left:38%;animation:driftA 12s ease-in-out infinite alternate}
        @keyframes driftA{from{transform:translate(0,0)}to{transform:translate(30px,20px)}}
        @keyframes driftB{from{transform:translate(0,0)}to{transform:translate(-20px,-30px)}}
        .grid-lines{position:absolute;inset:0;background-image:linear-gradient(rgba(34,197,94,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(34,197,94,0.04) 1px,transparent 1px);background-size:48px 48px}

        .navbar{position:relative;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:22px 52px;border-bottom:1px solid rgba(34,197,94,0.1);background:rgba(13,31,20,0.6);backdrop-filter:blur(16px)}
        .logo-wrap{display:flex;align-items:center;gap:10px;cursor:pointer}
        .logo-wrap img{height:44px}
        .logo-wrap span{font-weight:700;font-size:1.4rem;background:linear-gradient(135deg,#4caf50,#ff6b35);-webkit-background-clip:text;background-clip:text;color:transparent}
        .nav-links{display:flex;gap:32px;list-style:none}
        .nav-links a{text-decoration:none;font-size:14px;font-weight:500;color:rgba(255,255,255,0.65);transition:color 0.2s}
        .nav-links a.active{color:#4ade80}
        .nav-links a:hover{color:#fff}
        .nav-btns{display:flex;gap:10px}
        .btn-ghost{padding:8px 20px;border:1px solid rgba(74,222,128,0.35);border-radius:50px;background:transparent;color:#4ade80;font-size:13px;font-weight:600;cursor:pointer}
        .btn-fill{padding:8px 22px;border:none;border-radius:50px;background:#16a34a;color:#fff;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 0 20px rgba(22,163,74,0.45)}
        
        .language-selector{position:fixed;top:90px;right:20px;z-index:9999;background:rgba(13,31,20,0.85);backdrop-filter:blur(10px);padding:8px 15px;border-radius:30px;border:1px solid rgba(74,222,128,0.2)}
        .language-selector a{text-decoration:none;margin:0 5px;color:rgba(255,255,255,0.6)}
        .language-selector a.active{font-weight:bold;color:#4ade80}
        
        .article-section{position:relative;z-index:5;max-width:900px;margin:0 auto;padding:2rem}
        .article-category{display:inline-block;padding:5px 15px;background:rgba(74,222,128,0.12);color:#4ade80;border-radius:50px;font-size:0.8rem;font-weight:600;margin-bottom:1rem}
        .article-title{font-size:2rem;font-weight:700;color:#fff;margin-bottom:1rem}
        .article-meta{color:rgba(255,255,255,0.5);margin-bottom:2rem;display:flex;gap:1rem;flex-wrap:wrap}
        .article-content{color:rgba(255,255,255,0.8);line-height:1.7;font-size:1rem}
        
        /* Vidéo YouTube responsive */
        .video-container{position:relative;padding-bottom:56.25%;height:0;margin:25px 0;border-radius:16px;overflow:hidden}
        .video-container iframe{position:absolute;top:0;left:0;width:100%;height:100%;border:none}
        
        /* Boutons de partage social */
        .share-buttons{display:flex;gap:10px;margin:25px 0;flex-wrap:wrap}
        .share-btn{display:inline-flex;align-items:center;gap:8px;padding:8px 18px;border-radius:50px;text-decoration:none;font-size:13px;font-weight:600;transition:all 0.2s}
        .share-btn:hover{transform:translateY(-2px)}
        .share-facebook{background:#1877f2;color:#fff}
        .share-twitter{background:#000;color:#fff}
        .share-whatsapp{background:#25D366;color:#fff}
        .share-linkedin{background:#0077b5;color:#fff}
        
        /* Newsletter Widget */
        .newsletter-widget{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.15);border-radius:20px;padding:1.5rem;margin:2rem 0;text-align:center}
        .newsletter-widget h3{color:#fff;margin-bottom:0.5rem}
        .newsletter-widget p{color:rgba(255,255,255,0.5);font-size:0.85rem;margin-bottom:1rem}
        .newsletter-form{display:flex;gap:10px;max-width:400px;margin:0 auto;flex-wrap:wrap}
        .newsletter-form input{flex:2;min-width:200px;padding:12px 15px;background:rgba(255,255,255,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:50px;color:#fff;font-family:inherit}
        .newsletter-form button{padding:12px 24px;background:#16a34a;border:none;border-radius:50px;color:#fff;font-weight:600;cursor:pointer;transition:all 0.2s}
        .newsletter-form button:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(22,163,74,0.5)}
        .newsletter-message{margin-top:12px;font-size:0.8rem}
        
        .avis-section{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:20px;padding:2rem;margin-top:3rem}
        .avis-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
        .avis-note{font-size:1.5rem;font-weight:700;color:#4ade80}
        .avis-stars{color:#ffc107;font-size:1.1rem}
        .btn-avis{padding:10px 20px;background:#16a34a;color:#fff;border:none;border-radius:50px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block}
        .btn-avis:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(22,163,74,0.3)}
        .btn-voir-avis{padding:8px 16px;background:transparent;border:1px solid #4ade80;color:#4ade80;border-radius:50px;font-weight:600;text-decoration:none;display:inline-block}
        
        .avis-list{margin-top:1rem}
        .avis-item{padding:1rem 0;border-bottom:1px solid rgba(255,255,255,0.05)}
        .avis-author{font-weight:600;color:#fff;margin-bottom:0.25rem}
        .avis-date{font-size:0.7rem;color:rgba(255,255,255,0.4)}
        .avis-content{color:rgba(255,255,255,0.7);margin-top:0.5rem}
        .avis-stars-small{color:#ffc107;font-size:0.8rem;margin-bottom:0.25rem}
        .btn-modifier-avis{display:inline-block;margin-top:8px;color:#fbbf24;text-decoration:none;font-size:0.75rem}
        
        .footer{background:rgba(8,16,8,0.9);border-top:1px solid rgba(74,222,128,0.08);padding:3rem 52px 1rem;margin-top:3rem}
        .footer-bottom{text-align:center;padding-top:2rem;border-top:1px solid rgba(255,255,255,0.05);font-size:0.8rem;color:rgba(255,255,255,0.3)}
        
        @media (max-width:768px){
            .navbar{padding:22px 20px;flex-direction:column;gap:1rem}
            .nav-links{flex-wrap:wrap;justify-content:center}
            .article-section{padding:1rem}
            .article-title{font-size:1.5rem}
            .language-selector{top:80px;right:15px}
            .share-buttons{justify-content:center}
            .newsletter-form{flex-direction:column}
        }
    </style>
</head>
<body>

<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div><div class="glow-3"></div>
</div>

<div class="language-selector">
    <a href="?lang=fr&action=detail&id=<?php echo $article['id']; ?>" class="active">🇫🇷 FR</a>
    <span>|</span>
    <a href="?lang=en&action=detail&id=<?php echo $article['id']; ?>">🇬🇧 EN</a>
</div>

<nav class="navbar">
    <div class="logo-wrap" onclick="location.href='index.php?action=blog'">
        <img src="./assets/images/logo-foodsave.png" alt="FoodSave">
        <span>FoodSave</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php?action=blog"><?php echo t('nav_home'); ?></a></li>
        <li><a href="index.php?action=blog" class="active"><?php echo t('nav_blog'); ?></a></li>
        <li><a href="index.php?action=conseils"><?php echo t('nav_tips'); ?></a></li>
        <li><a href="index.php?action=recettes"><?php echo t('nav_recipes'); ?></a></li>
    </ul>
    <div class="nav-btns">
        <?php if(isset($_SESSION['user_id'])): ?>
            <button class="btn-ghost" onclick="location.href='index.php?action=profile'">👤 <?php echo $_SESSION['user_prenom'] ?? 'Mon compte'; ?></button>
            <button class="btn-fill" onclick="location.href='index.php?action=logout'"><?php echo t('nav_login'); ?></button>
        <?php else: ?>
            <button class="btn-ghost" onclick="location.href='index.php?action=login'"><?php echo t('nav_login'); ?></button>
            <button class="btn-fill" onclick="location.href='index.php?action=register'"><?php echo t('nav_register'); ?></button>
        <?php endif; ?>
    </div>
</nav>

<section class="article-section">
    <span class="article-category"><?php echo htmlspecialchars($article['categorie']); ?></span>
    <h1 class="article-title"><?php echo htmlspecialchars($article['titre']); ?></h1>
    <div class="article-meta">
        <span>📅 <?php echo t('published_on'); ?> <?php echo date('d/m/Y', strtotime($article['created_at'])); ?></span>
        <span>👁️ <?php echo $article['vue']; ?> <?php echo t('views'); ?></span>
    </div>
    
    <!-- Boutons de partage social -->
    <div class="share-buttons">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($article_full_url); ?>" target="_blank" class="share-btn share-facebook">
            <i class="fab fa-facebook-f"></i> Facebook
        </a>
        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($article['titre']); ?>&url=<?php echo urlencode($article_full_url); ?>" target="_blank" class="share-btn share-twitter">
            <i class="fab fa-twitter"></i> X
        </a>
        <a href="https://wa.me/?text=<?php echo urlencode($article['titre'] . ' - ' . $article_full_url); ?>" target="_blank" class="share-btn share-whatsapp">
            <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($article_full_url); ?>" target="_blank" class="share-btn share-linkedin">
            <i class="fab fa-linkedin-in"></i> LinkedIn
        </a>
    </div>
    
    <!-- Vidéo YouTube intégrée -->
    <?php if($video_id): ?>
    <div class="video-container">
        <iframe 
            src="https://www.youtube.com/embed/<?php echo $video_id; ?>" 
            title="YouTube video player" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
        </iframe>
    </div>
    <?php endif; ?>
    
    <!-- L'image NE s'affiche PAS ici (conformément à ta demande) -->
    
    <div class="article-content">
        <?php echo nl2br(htmlspecialchars($article['contenu'])); ?>
    </div>
    
    <div class="avis-section">
        <div class="avis-header">
            <div>
                <div class="avis-note"><?php echo $noteMoyenne; ?> / 5</div>
                <div class="avis-stars">
                    <?php
                    $fullStars = floor($noteMoyenne);
                    $halfStar = ($noteMoyenne - $fullStars) >= 0.5;
                    for($i = 1; $i <= 5; $i++) {
                        if($i <= $fullStars) echo '<i class="fas fa-star"></i>';
                        elseif($halfStar && $i == $fullStars + 1) echo '<i class="fas fa-star-half-alt"></i>';
                        else echo '<i class="far fa-star"></i>';
                    }
                    ?>
                </div>
                <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5);"><?php echo t('based_on'); ?> <?php echo $nbAvis; ?> <?php echo t('reviews_count'); ?></div>
            </div>
            <div>
                <a href="index.php?action=addAvisForm&article_id=<?php echo $article['id']; ?>" class="btn-avis">
                    <i class="fas fa-pen"></i> <?php echo t('write_review'); ?>
                </a>
            </div>
        </div>
        
        <?php if(!empty($derniersAvis)): ?>
            <div class="avis-list">
                <h4 style="margin-bottom:1rem"><?php echo t('reviews'); ?></h4>
                <?php foreach($derniersAvis as $a): ?>
                <div class="avis-item">
                    <div class="avis-stars-small">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php echo ($i <= $a['note']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                        <?php endfor; ?>
                    </div>
                    <div class="avis-author">
                        <?php echo htmlspecialchars($a['user_name'] ?? 'Utilisateur'); ?>
                        <span class="avis-date"> - <?php echo date('d/m/Y', strtotime($a['created_at'])); ?></span>
                    </div>
                    <div class="avis-content">
                        "<?php echo nl2br(htmlspecialchars(substr($a['contenu'], 0, 150))); ?>"
                    </div>
                    <?php if($user_id && isset($a['user_id']) && $a['user_id'] == $user_id): ?>
                        <a href="index.php?action=editUserAvis&id=<?php echo $a['id']; ?>" class="btn-modifier-avis">
                            <i class="fas fa-edit"></i> <?php echo t('edit_review'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="index.php?action=showAvis&article_id=<?php echo $article['id']; ?>" class="btn-voir-avis">
                        <?php echo t('view_all'); ?> <?php echo $nbAvis; ?> <?php echo t('reviews_count'); ?> →
                    </a>
                </div>
            </div>
        <?php elseif($nbAvis == 0): ?>
            <div style="text-align: center; padding: 2rem; color: rgba(255,255,255,0.5);">
                <i class="fas fa-comment-dots" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                <p><?php echo t('no_reviews'); ?></p>
            </div>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="index.php?action=showAvis&article_id=<?php echo $article['id']; ?>" class="btn-voir-avis">
                    <?php echo t('view_all'); ?> (0) →
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Newsletter Widget -->
    <div class="newsletter-widget">
        <h3>📧 Newsletter</h3>
        <p>Recevez nos nouveaux articles directement dans votre boîte mail</p>
        <form id="newsletterForm" class="newsletter-form">
            <input type="email" id="newsletter_email" placeholder="Votre email" required>
            <input type="text" id="newsletter_nom" placeholder="Votre nom (optionnel)">
            <button type="submit">S'abonner</button>
        </form>
        <div id="newsletterMessage" class="newsletter-message"></div>
    </div>
</section>

<footer class="footer">
    <div class="footer-bottom">
        <p>© 2025 FoodSave - <?php echo t('footer_copyright'); ?></p>
    </div>
</footer>

<script>
// Newsletter AJAX
document.getElementById('newsletterForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const email = document.getElementById('newsletter_email').value;
    const nom = document.getElementById('newsletter_nom').value;
    const messageDiv = document.getElementById('newsletterMessage');
    
    const submitBtn = this.querySelector('button');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('index.php?action=newsletterSubscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email=' + encodeURIComponent(email) + '&nom=' + encodeURIComponent(nom)
        });
        const data = await response.json();
        
        if(data.success) {
            messageDiv.innerHTML = '<span style="color: #4ade80;">✓ ' + data.message + '</span>';
            document.getElementById('newsletter_email').value = '';
            document.getElementById('newsletter_nom').value = '';
            setTimeout(() => { messageDiv.innerHTML = ''; }, 5000);
        } else {
            messageDiv.innerHTML = '<span style="color: #f87171;">⚠️ ' + data.message + '</span>';
        }
    } catch(error) {
        messageDiv.innerHTML = '<span style="color: #f87171;">⚠️ Erreur, veuillez réessayer</span>';
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});
</script>

</body>
</html>