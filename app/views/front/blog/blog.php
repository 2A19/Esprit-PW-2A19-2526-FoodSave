<?php
// =====================================================
// 1. Démarrer la session (pour la langue)
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// 2. Charger le traducteur
// =====================================================
require_once 'C:/xampp/htdocs/FoodSave/app/core/Translator.php';

// Initialiser le traducteur
$translator = Translator::getInstance();
$lang = $translator->getCurrentLang();

// Fonction de traduction
$t = function($key) use ($translator) {
    return $translator->translate($key);
};

// =====================================================
// 3. Variables de pagination
// =====================================================
$currentPage = isset($page) ? $page : 1;
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - <?php echo $t('blog_title'); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* ========== STYLE TEMPLATE (sombre/vert) ========== */
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:"DM Sans",sans-serif;background:#0d1f14;color:#e8f5e9;overflow-x:hidden}

        /* Shared BG */
        .bg-mesh{position:fixed;inset:0;pointer-events:none;z-index:0}
        .glow-1{position:absolute;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(34,197,94,0.18) 0%,transparent 70%);top:-80px;right:-60px;animation:driftA 8s ease-in-out infinite alternate}
        .glow-2{position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(16,185,129,0.12) 0%,transparent 70%);bottom:0;left:-80px;animation:driftB 10s ease-in-out infinite alternate}
        .glow-3{position:absolute;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(234,179,8,0.09) 0%,transparent 70%);top:40%;left:38%;animation:driftA 12s ease-in-out infinite alternate}
        @keyframes driftA{from{transform:translate(0,0)}to{transform:translate(30px,20px)}}
        @keyframes driftB{from{transform:translate(0,0)}to{transform:translate(-20px,-30px)}}
        .grid-lines{position:absolute;inset:0;background-image:linear-gradient(rgba(34,197,94,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(34,197,94,0.04) 1px,transparent 1px);background-size:48px 48px}

        /* Navigation */
        .navbar{position:relative;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:22px 52px;border-bottom:1px solid rgba(34,197,94,0.1);background:rgba(13,31,20,0.6);backdrop-filter:blur(16px)}
        .logo-wrap{display:flex;align-items:center;gap:10px;cursor:pointer}
        .logo-wrap img{height:44px}
        .logo-wrap span{font-weight:700;font-size:1.4rem;background:linear-gradient(135deg,#4caf50,#ff6b35);-webkit-background-clip:text;background-clip:text;color:transparent}
        .nav-links{display:flex;gap:32px;list-style:none}
        .nav-links a{text-decoration:none;font-size:14px;font-weight:500;color:rgba(255,255,255,0.65);transition:color 0.2s;cursor:pointer}
        .nav-links a.active{color:#4ade80}
        .nav-links a:hover{color:#fff}
        .nav-btns{display:flex;gap:10px}
        .btn-ghost{padding:8px 20px;border:1px solid rgba(74,222,128,0.35);border-radius:50px;background:transparent;color:#4ade80;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit}
        .btn-ghost:hover{background:rgba(74,222,128,0.08)}
        .btn-fill{padding:8px 22px;border:none;border-radius:50px;background:#16a34a;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;box-shadow:0 0 20px rgba(22,163,74,0.45)}
        .btn-fill:hover{background:#15803d;transform:translateY(-1px)}
        
        /* Language selector */
        .language-selector{position:fixed;top:90px;right:20px;z-index:9999;background:rgba(13,31,20,0.85);backdrop-filter:blur(10px);padding:8px 15px;border-radius:30px;border:1px solid rgba(74,222,128,0.2)}
        .language-selector a{text-decoration:none;margin:0 5px;color:rgba(255,255,255,0.6);transition:all 0.3s ease}
        .language-selector a.active{font-weight:bold;color:#4ade80}
        
        /* Hero */
        .hero{position:relative;z-index:5;padding:3rem 52px 2rem;text-align:center}
        .hero h1{font-size:3rem;font-weight:700;color:#fff;margin-bottom:1rem}
        .hero h1 span{color:#4ade80}
        .hero p{color:rgba(255,255,255,0.5);max-width:600px;margin:0 auto}
        
        /* Blog section */
        .blog-section{position:relative;z-index:5;padding:2rem 52px 4rem}
        .section-header{text-align:center;margin-bottom:2rem}
        .section-header h2{font-size:2rem;color:#fff;margin-bottom:0.5rem}
        .section-header p{color:rgba(255,255,255,0.45)}
        
        /* Search & filters */
        .search-bar{max-width:500px;margin:0 auto 2rem;display:flex;gap:10px}
        .search-bar input{flex:1;padding:12px 18px;background:rgba(255,255,255,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:50px;font-family:inherit;color:#fff}
        .search-bar input::placeholder{color:rgba(255,255,255,0.3)}
        .search-bar input:focus{outline:none;border-color:#4ade80}
        .search-bar button{padding:12px 24px;background:#16a34a;color:#fff;border:none;border-radius:50px;cursor:pointer;font-weight:600;box-shadow:0 0 20px rgba(22,163,74,0.3)}
        
        .tri-bar{display:flex;justify-content:center;gap:15px;margin-bottom:2rem}
        .tri-bar a{padding:8px 20px;background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.15);border-radius:50px;text-decoration:none;color:#4ade80;font-weight:500;transition:all 0.2s}
        .tri-bar a:hover,.tri-bar a.active{background:#16a34a;color:#fff;border-color:#16a34a}
        
        .info-articles{text-align:center;color:rgba(255,255,255,0.4);font-size:0.9rem;margin-bottom:1rem}
        
        /* Grille articles */
        .articles-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:2rem;max-width:1400px;margin:0 auto}
        .article-card{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:20px;overflow:hidden;transition:all 0.3s ease;backdrop-filter:blur(8px)}
        .article-card:hover{transform:translateY(-5px);background:rgba(74,222,128,0.05);border-color:rgba(74,222,128,0.25)}
        .article-img{height:180px;background:rgba(74,222,128,0.08);display:flex;align-items:center;justify-content:center;font-size:3rem}
        .article-content{padding:1.5rem}
        .article-category{display:inline-block;padding:4px 12px;background:rgba(74,222,128,0.12);color:#4ade80;border-radius:50px;font-size:0.7rem;font-weight:600;margin-bottom:0.75rem}
        .article-title{font-size:1.2rem;font-weight:700;color:#fff;margin-bottom:0.5rem}
        .article-excerpt{color:rgba(255,255,255,0.6);font-size:0.9rem;line-height:1.5;margin-bottom:1rem}
        .article-link{color:#4ade80;text-decoration:none;font-weight:600;font-size:0.9rem;display:inline-flex;align-items:center;gap:5px}
        .article-link:hover{gap:8px}
        
        /* Pagination */
        .pagination{display:flex;justify-content:center;gap:10px;margin-top:3rem;flex-wrap:wrap}
        .pagination a{display:inline-block;padding:8px 14px;background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.15);border-radius:8px;text-decoration:none;color:#4ade80;font-weight:500}
        .pagination a:hover{background:#16a34a;color:#fff}
        .pagination a.active{background:#16a34a;color:#fff;cursor:default}
        .pagination a.disabled{opacity:0.5;cursor:not-allowed;pointer-events:none}
        
        /* Newsletter Widget */
        .newsletter-widget{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.15);border-radius:20px;padding:1.5rem;margin:2rem 0;text-align:center}
        .newsletter-widget h3{color:#fff;margin-bottom:0.5rem}
        .newsletter-widget p{color:rgba(255,255,255,0.5);font-size:0.85rem;margin-bottom:1rem}
        .newsletter-form{display:flex;gap:10px;max-width:400px;margin:0 auto;flex-wrap:wrap}
        .newsletter-form input{flex:2;min-width:200px;padding:12px 15px;background:rgba(255,255,255,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:50px;color:#fff;font-family:inherit}
        .newsletter-form button{padding:12px 24px;background:#16a34a;border:none;border-radius:50px;color:#fff;font-weight:600;cursor:pointer;transition:all 0.2s}
        .newsletter-form button:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(22,163,74,0.5)}
        .newsletter-message{margin-top:12px;font-size:0.8rem}
        
        /* Footer */
        .footer{position:relative;z-index:5;background:rgba(8,16,8,0.9);border-top:1px solid rgba(74,222,128,0.08);padding:3rem 52px 1rem;margin-top:3rem}
        .footer-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:2rem;max-width:1200px;margin:0 auto}
        .footer-brand span{font-size:1.5rem;font-weight:700;background:linear-gradient(135deg,#4caf50,#ff6b35);-webkit-background-clip:text;background-clip:text;color:transparent}
        .footer-brand p{margin-top:0.5rem;color:rgba(255,255,255,0.4)}
        .footer-bottom{text-align:center;padding-top:2rem;margin-top:2rem;border-top:1px solid rgba(255,255,255,0.05);font-size:0.8rem;color:rgba(255,255,255,0.3)}
        
        @media (max-width:768px){
            .navbar{flex-direction:column;gap:1rem;padding:22px 20px}
            .nav-links{flex-wrap:wrap;justify-content:center}
            .hero{padding:2rem 20px}
            .hero h1{font-size:2rem}
            .blog-section{padding:2rem 20px}
            .articles-grid{grid-template-columns:1fr}
            .language-selector{top:80px;right:15px}
            .newsletter-form{flex-direction:column}
            .footer{padding:2rem 20px 1rem}
        }
    </style>
</head>
<body>

<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div><div class="glow-3"></div>
</div>

<!-- Language selector -->
<div class="language-selector">
    <a href="?lang=fr&action=<?php echo $_GET['action'] ?? 'blog'; ?>&page=<?php echo $currentPage; ?>" class="<?php echo $lang == 'fr' ? 'active' : ''; ?>">🇫🇷 FR</a>
    <span>|</span>
    <a href="?lang=en&action=<?php echo $_GET['action'] ?? 'blog'; ?>&page=<?php echo $currentPage; ?>" class="<?php echo $lang == 'en' ? 'active' : ''; ?>">🇬🇧 EN</a>
</div>

<!-- Navigation -->
<nav class="navbar">
    <div class="logo-wrap" onclick="location.href='index.php?action=blog'">
        <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="FoodSave">
        <span>FoodSave</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php?action=blog" class="<?php echo (!isset($_GET['action']) || $_GET['action'] == 'blog') ? 'active' : ''; ?>"><?php echo $t('nav_home'); ?></a></li>
        <li><a href="index.php?action=blog" class="active"><?php echo $t('nav_blog'); ?></a></li>
        <li><a href="index.php?action=conseils"><?php echo $t('nav_tips'); ?></a></li>
        <li><a href="index.php?action=recettes"><?php echo $t('nav_recipes'); ?></a></li>
        <li><a href="#">Contact</a></li>
    </ul>
    <div class="nav-btns">
        <button class="btn-ghost" onclick="location.href='index.php?action=login'"><?php echo $t('nav_login'); ?></button>
        <button class="btn-fill" onclick="location.href='index.php?action=register'"><?php echo $t('nav_register'); ?></button>
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <h1>🍽️ <span>FoodSave</span> <?php echo $t('nav_blog'); ?></h1>
    <p><?php echo $t('blog_subtitle'); ?></p>
</section>

<!-- Blog Section -->
<section class="blog-section">
    <div class="section-header">
        <h2>📝 <?php echo $t('newest'); ?></h2>
        <p><?php echo $t('blog_subtitle'); ?></p>
    </div>

    <!-- Tri -->
    <div class="tri-bar">
        <a href="?action=blog&order=DESC&page=<?php echo $currentPage; ?>" class="<?php echo ($order ?? 'DESC') == 'DESC' ? 'active' : ''; ?>">📅 <?php echo $t('newest'); ?></a>
        <a href="?action=blog&order=ASC&page=<?php echo $currentPage; ?>" class="<?php echo ($order ?? 'DESC') == 'ASC' ? 'active' : ''; ?>">📅 <?php echo $t('oldest'); ?></a>
    </div>

    <!-- Recherche -->
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="<?php echo $t('search_placeholder'); ?>" autocomplete="off">
        <button id="searchButton">🔍 <?php echo $t('search_button'); ?></button>
    </div>

    <!-- Info -->
    <div class="info-articles" id="infoArticles">
        <?php if(isset($totalArticles)): ?>
            <?php echo $t('page'); ?> <?php echo $currentPage; ?> / <?php echo $totalPages; ?> - 
            <?php echo count($articles); ?> / <?php echo $totalArticles; ?> <?php echo $t('articles'); ?>
        <?php endif; ?>
    </div>

    <!-- Grille articles -->
    <div class="articles-grid" id="articlesGrid">
        <?php if(empty($articles)): ?>
            <div style="text-align:center;padding:50px;grid-column:1/-1">
                <i class="fas fa-search" style="font-size:3rem;color:rgba(255,255,255,0.2);"></i>
                <p style="margin-top:20px;color:rgba(255,255,255,0.5);"><?php echo $t('no_articles'); ?></p>
                <a href="index.php?action=blog" class="article-link"><?php echo $t('view_all'); ?></a>
            </div>
        <?php else: ?>
            <?php foreach($articles as $article): ?>
            <div class="article-card" data-titre="<?php echo strtolower(htmlspecialchars($article['titre'])); ?>">
                <div class="article-img">
                    <?php if($article['categorie'] == 'Astuces'): ?>🥕
                    <?php elseif($article['categorie'] == 'Recettes'): ?>🍲
                    <?php else: ?>💡
                    <?php endif; ?>
                </div>
                <div class="article-content">
                    <span class="article-category"><?php echo htmlspecialchars($article['categorie']); ?></span>
                    <h3 class="article-title"><?php echo htmlspecialchars($article['titre']); ?></h3>
                    <p class="article-excerpt"><?php echo htmlspecialchars(substr($article['resume'], 0, 120)) . '...'; ?></p>
                    <a href="index.php?action=detail&id=<?php echo $article['id']; ?>" class="article-link">
                        <?php echo $t('read_more'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if(isset($totalPages) && $totalPages > 1): ?>
    <div class="pagination">
        <?php if($currentPage > 1): ?>
            <a href="?action=blog&order=<?php echo $order ?? 'DESC'; ?>&page=<?php echo $currentPage-1; ?>">« <?php echo $t('prev'); ?></a>
        <?php else: ?>
            <a href="#" class="disabled">« <?php echo $t('prev'); ?></a>
        <?php endif; ?>
        
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <?php if($i == $currentPage): ?>
                <a href="#" class="active"><?php echo $i; ?></a>
            <?php else: ?>
                <a href="?action=blog&order=<?php echo $order ?? 'DESC'; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if($currentPage < $totalPages): ?>
            <a href="?action=blog&order=<?php echo $order ?? 'DESC'; ?>&page=<?php echo $currentPage+1; ?>"><?php echo $t('next'); ?> »</a>
        <?php else: ?>
            <a href="#" class="disabled"><?php echo $t('next'); ?> »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- ===== NEWSLETTER WIDGET ===== -->
    <div class="newsletter-widget">
        <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 0.5rem;">
            <i class="fas fa-envelope" style="color: #4ade80; font-size: 1.8rem;"></i>
            <h3 style="color: #fff; margin: 0;">📧 Newsletter</h3>
        </div>
        <p>Recevez nos nouveaux articles directement dans votre boîte mail</p>
        
        <form id="newsletterForm" class="newsletter-form">
            <input type="email" id="newsletter_email" placeholder="Votre email" required>
            <input type="text" id="newsletter_nom" placeholder="Votre nom (optionnel)">
            <button type="submit">S'abonner</button>
        </form>
        <div id="newsletterMessage" class="newsletter-message"></div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <span>🍽️ FoodSave</span>
            <p><?php echo $t('blog_subtitle'); ?></p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2025 FoodSave - <?php echo $t('footer_copyright'); ?></p>
    </div>
</footer>

<!-- JavaScript recherche dynamique -->
<script>
const articles = document.querySelectorAll('.article-card');
const searchInput = document.getElementById('searchInput');
const articlesGrid = document.getElementById('articlesGrid');

function filterArticles() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    let visibleCount = 0;
    
    articles.forEach(article => {
        const titre = article.getAttribute('data-titre') || article.querySelector('.article-title').innerText.toLowerCase();
        
        if (searchTerm === '' || titre.includes(searchTerm)) {
            article.style.display = '';
            visibleCount++;
        } else {
            article.style.display = 'none';
        }
    });
    
    const infoDiv = document.getElementById('infoArticles');
    if(infoDiv && searchTerm !== '') {
        infoDiv.innerHTML = `${visibleCount} résultat(s) trouvé(s) pour "${searchTerm}"`;
    } else if(infoDiv && <?php echo isset($totalArticles) ? 'true' : 'false'; ?>) {
        infoDiv.innerHTML = `<?php echo $t('page'); ?> <?php echo $currentPage; ?> / <?php echo $totalPages; ?> - ${visibleCount} / <?php echo $totalArticles; ?> <?php echo $t('articles'); ?>`;
    }
    
    let noResultMsg = document.getElementById('noResultMsg');
    if (visibleCount === 0 && searchTerm !== '') {
        if (!noResultMsg) {
            noResultMsg = document.createElement('div');
            noResultMsg.id = 'noResultMsg';
            noResultMsg.style.cssText = 'text-align:center;padding:50px;grid-column:1/-1';
            noResultMsg.innerHTML = '<i class="fas fa-search" style="font-size:3rem;color:rgba(255,255,255,0.2);"></i><p style="margin-top:20px;color:rgba(255,255,255,0.5);"><?php echo $t('no_articles'); ?></p><a href="index.php?action=blog" class="article-link"><?php echo $t('view_all'); ?></a>';
            articlesGrid.parentNode.insertBefore(noResultMsg, articlesGrid.nextSibling);
        }
        noResultMsg.style.display = 'block';
        articlesGrid.style.display = 'none';
    } else {
        if (noResultMsg) noResultMsg.style.display = 'none';
        articlesGrid.style.display = 'grid';
    }
}

if (searchInput) {
    searchInput.addEventListener('input', filterArticles);
    searchInput.addEventListener('keyup', filterArticles);
}

// ===== NEWSLETTER AJAX =====
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
            messageDiv.innerHTML = '<span style="color: #4ade80;"><i class="fas fa-check-circle"></i> ✓ ' + data.message + '</span>';
            document.getElementById('newsletter_email').value = '';
            document.getElementById('newsletter_nom').value = '';
            setTimeout(() => { messageDiv.innerHTML = ''; }, 5000);
        } else {
            messageDiv.innerHTML = '<span style="color: #f87171;"><i class="fas fa-exclamation-triangle"></i> ⚠️ ' + data.message + '</span>';
        }
    } catch(error) {
        messageDiv.innerHTML = '<span style="color: #f87171;"><i class="fas fa-exclamation-triangle"></i> ⚠️ Erreur, veuillez réessayer</span>';
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});
</script>

</body>
</html>