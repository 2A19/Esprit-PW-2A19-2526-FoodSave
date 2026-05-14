<?php
// =====================================================
// 1. Démarrer la session (pour la langue)
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// 2. Fonction de traduction simplifiée (sans Translator)
// =====================================================
function t($key) {
    $translations = [
        'blog_title' => 'Blog FoodSave',
        'nav_home' => 'Accueil',
        'nav_blog' => 'Blog',
        'nav_tips' => 'Conseils',
        'nav_recipes' => 'Recettes',
        'nav_login' => 'Connexion',
        'nav_register' => 'Inscription',
        'blog_subtitle' => 'Astuces et recettes anti-gaspillage',
        'newest' => 'Derniers articles',
        'oldest' => 'Plus anciens',
        'search_placeholder' => 'Rechercher un article...',
        'search_button' => 'Rechercher',
        'read_more' => 'Lire la suite',
        'no_articles' => 'Aucun article trouvé',
        'view_all' => 'Voir tous les articles',
        'prev' => 'Précédent',
        'next' => 'Suivant',
        'page' => 'Page',
        'articles' => 'articles',
        'footer_copyright' => 'Tous droits réservés'
    ];
    return $translations[$key] ?? $key;
}

$currentPage = isset($page) ? $page : 1;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - <?php echo t('blog_title'); ?></title>
    
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
        
        /* ========== STYLES MODERNES POUR LES ARTICLES ========== */
        
        /* Grille */
        .articles-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:2rem;max-width:1400px;margin:0 auto}
        
        /* Carte article */
        .article-card {
            background:rgba(255,255,255,0.04);
            border:1px solid rgba(74,222,128,0.1);
            border-radius:24px;
            overflow:hidden;
            transition:all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            backdrop-filter:blur(8px);
            position:relative;
            animation: fadeInUp 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1) both;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .article-card:hover {
            transform:translateY(-8px);
            background:rgba(74,222,128,0.06);
            border-color:rgba(74,222,128,0.35);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        /* Badge "Nouveau" */
        .article-badge-new {
            position: absolute;
            top: 16px;
            right: 16px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #fff;
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            z-index: 10;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: pulse 2s infinite;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .article-badge-new i {
            font-size: 0.7rem;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
            50% { transform: scale(1.05); box-shadow: 0 6px 20px rgba(251,191,36,0.4); }
        }
        
        /* Wrapper image */
        .article-img-wrapper {
            position: relative;
            height: 220px;
            overflow: hidden;
            cursor: pointer;
            background: rgba(74,222,128,0.05);
        }
        
        /* Image en background */
        .article-img-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            transition: transform 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            z-index: 1;
        }
        
        /* Zoom au hover */
        .article-card:hover .article-img-bg {
            transform: scale(1.1);
        }
        
        /* Overlay sombre pour lisibilité */
        .img-dark-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.3), rgba(0,0,0,0.5));
            z-index: 2;
            transition: opacity 0.3s ease;
        }
        
        /* Icône décorative par-dessus l'image */
        .img-icon-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 3;
            background: rgba(0,0,0,0.4);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
            transition: all 0.3s ease;
        }
        
        .article-card:hover .img-icon-overlay {
            background: rgba(74,222,128,0.8);
            transform: translate(-50%, -50%) scale(1.1);
        }
        
        .img-icon-overlay .fallback-icon {
            font-size: 1.8rem;
            transition: all 0.3s ease;
        }
        
        /* Fallback icône animée (pas d'image) */
        .article-img-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
        }
        
        .article-card:hover .article-img-fallback {
            background: linear-gradient(135deg, rgba(74,222,128,0.15), rgba(74,222,128,0.08)) !important;
        }
        
        .fallback-icon {
            font-size: 3.5rem;
            animation: float 3s ease-in-out infinite;
            transition: all 0.3s ease;
        }
        
        .article-card:hover .fallback-icon {
            transform: scale(1.1);
            animation: none;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }
        
        /* Overlay au hover */
        .article-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(13, 31, 20, 0.85);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 15;
        }
        
        .article-card:hover .article-overlay {
            opacity: 1;
        }
        
        .overlay-link {
            background: #16a34a;
            color: #fff;
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transform: translateY(20px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        
        .article-card:hover .overlay-link {
            transform: translateY(0);
        }
        
        .overlay-link:hover {
            background: #15803d;
            gap: 12px;
        }
        
        /* Contenu */
        .article-content {
            padding: 1.5rem;
        }
        
        .article-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .article-category {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            background: rgba(74,222,128,0.12);
            color: #4ade80;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .article-rating {
            display: flex;
            gap: 3px;
        }
        
        .article-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.75rem;
            line-height: 1.4;
            transition: color 0.3s ease;
        }
        
        .article-card:hover .article-title {
            color: #4ade80;
        }
        
        .article-excerpt {
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .article-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        
        .article-meta {
            display: flex;
            gap: 1rem;
        }
        
        .meta-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.7rem;
            color: rgba(255,255,255,0.4);
        }
        
        .meta-item i {
            font-size: 0.7rem;
        }
        
        .article-link {
            color: #4ade80;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }
        
        .article-link:hover {
            gap: 12px;
            color: #86efac;
        }
        
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
        
        /* Responsive */
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
            .article-footer{flex-direction:column;align-items:flex-start}
        }
        
        /* Chatbot Styles */
        .chatbot-btn{position:fixed;bottom:25px;right:25px;background:#16a34a;border:none;border-radius:50px;padding:12px 20px;color:white;cursor:pointer;font-weight:bold;z-index:1000;font-family:inherit;transition:all 0.3s;box-shadow:0 4px 12px rgba(22,163,74,0.3)}
        .chatbot-btn:hover{transform:scale(1.05);box-shadow:0 6px 20px rgba(22,163,74,0.5)}
        .chatbot-window{position:fixed;bottom:80px;right:25px;width:380px;height:500px;background:#0d1f14;border:1px solid #4ade80;border-radius:16px;display:none;flex-direction:column;z-index:1000;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.5)}
        .chatbot-window.open{display:flex}
        .chatbot-header{background:#16a34a;padding:14px;color:white;font-weight:bold;display:flex;justify-content:space-between;align-items:center}
        .chatbot-header button{background:transparent;border:none;color:white;cursor:pointer;font-size:18px}
        .chatbot-messages{flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px}
        .chatbot-messages div{margin-bottom:8px;padding:8px 12px;border-radius:8px;word-wrap:break-word;white-space:pre-wrap;line-height:1.4}
        .chatbot-messages .user{text-align:right;color:#4ade80;background:rgba(74,222,128,0.1);align-self:flex-end;max-width:80%}
        .chatbot-messages .bot{text-align:left;color:rgba(255,255,255,0.9);background:rgba(255,255,255,0.05);align-self:flex-start;max-width:90%}
        .chatbot-input{display:flex;padding:12px;border-top:1px solid rgba(255,255,255,0.1);gap:8px}
        .chatbot-input input{flex:1;padding:10px 14px;border-radius:20px;border:1px solid #4ade80;background:#1a2a1a;color:white;font-family:inherit}
        .chatbot-input input::placeholder{color:rgba(255,255,255,0.4)}
        .chatbot-input button{background:#16a34a;border:none;border-radius:20px;padding:10px 18px;color:white;cursor:pointer;font-weight:600;transition:all 0.2s}
        .chatbot-input button:hover{background:#15803d;transform:translateY(-1px)}
        .chatbot-messages::-webkit-scrollbar{width:6px}
        .chatbot-messages::-webkit-scrollbar-thumb{background:rgba(74,222,128,0.3);border-radius:3px}
        .chatbot-messages::-webkit-scrollbar-thumb:hover{background:rgba(74,222,128,0.5)}
    </style>
</head>
<body>

<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div><div class="glow-3"></div>
</div>

<div class="language-selector">
    <a href="?lang=fr&action=blog&page=<?php echo $currentPage; ?>" class="active">🇫🇷 FR</a>
    <span>|</span>
    <a href="?lang=en&action=blog&page=<?php echo $currentPage; ?>">🇬🇧 EN</a>
</div>

<nav class="navbar">
    <div class="logo-wrap" onclick="location.href='index.php?action=blog'">
        <img src="./assets/images/logo-foodsave.png" alt="FoodSave">
        <span>FoodSave</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php?action=blog" class="active"><?php echo t('nav_home'); ?></a></li>
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

<section class="hero">
    <h1>🍽️ <span>FoodSave</span> <?php echo t('nav_blog'); ?></h1>
    <p><?php echo t('blog_subtitle'); ?></p>
</section>

<section class="blog-section">
    <div class="section-header">
        <h2>📝 <?php echo t('newest'); ?></h2>
        <p><?php echo t('blog_subtitle'); ?></p>
    </div>

    <div class="tri-bar">
        <a href="?action=blog&order=DESC&page=<?php echo $currentPage; ?>" class="<?php echo ($order ?? 'DESC') == 'DESC' ? 'active' : ''; ?>">📅 <?php echo t('newest'); ?></a>
        <a href="?action=blog&order=ASC&page=<?php echo $currentPage; ?>" class="<?php echo ($order ?? 'DESC') == 'ASC' ? 'active' : ''; ?>">📅 <?php echo t('oldest'); ?></a>
    </div>

    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="<?php echo t('search_placeholder'); ?>" autocomplete="off">
        <button id="searchButton">🔍 <?php echo t('search_button'); ?></button>
    </div>

    <div class="info-articles" id="infoArticles">
        <?php if(isset($totalArticles)): ?>
            <?php echo t('page'); ?> <?php echo $currentPage; ?> / <?php echo $totalPages; ?> - 
            <?php echo count($articles); ?> / <?php echo $totalArticles; ?> <?php echo t('articles'); ?>
        <?php endif; ?>
    </div>

    <div class="articles-grid" id="articlesGrid">
        <?php if(empty($articles)): ?>
            <div style="text-align:center;padding:50px;grid-column:1/-1">
                <i class="fas fa-search" style="font-size:3rem;color:rgba(255,255,255,0.2);"></i>
                <p style="margin-top:20px;"><?php echo t('no_articles'); ?></p>
                <a href="index.php?action=blog" class="article-link"><?php echo t('view_all'); ?></a>
            </div>
        <?php else: ?>
            <?php foreach($articles as $index => $article): 
                $estRecent = (strtotime($article['created_at']) > strtotime('-7 days'));
                $fallbackIcon = match($article['categorie']) {
                    'Astuces' => '🥕',
                    'Recettes' => '🍲',
                    default => '💡'
                };
                $badgeColor = match($article['categorie']) {
                    'Astuces' => '#4ade80',
                    'Recettes' => '#fbbf24',
                    default => '#60a5fa'
                };
                $hasImage = !empty($article['image']) && file_exists(__DIR__ . '/../../assets/uploads/' . $article['image']);
            ?>
            <div class="article-card" data-titre="<?php echo strtolower(htmlspecialchars($article['titre'])); ?>" style="animation-delay: <?php echo $index * 0.05; ?>s;">
                <?php if($estRecent): ?>
                    <div class="article-badge-new"><i class="fas fa-bolt"></i> Nouveau</div>
                <?php endif; ?>
                
                <div class="article-img-wrapper">
                    <?php if($hasImage): ?>
                        <div class="article-img-bg" style="background-image: url('./assets/uploads/<?php echo $article['image']; ?>');" ></div>
                        <div class="img-dark-overlay"></div>
                        <div class="img-icon-overlay">
                            <div class="fallback-icon"><?php echo $fallbackIcon; ?></div>
                        </div>
                    <?php else: ?>
                        <div class="article-img-fallback" style="background:linear-gradient(135deg, rgba(74,222,128,0.1), rgba(74,222,128,0.03));">
                            <div class="fallback-icon"><?php echo $fallbackIcon; ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="article-overlay">
                        <a href="index.php?action=detail&id=<?php echo $article['id']; ?>" class="overlay-link">
                            <i class="fas fa-arrow-right"></i> Lire l'article
                        </a>
                    </div>
                </div>
                
                <div class="article-content">
                    <div class="article-header">
                        <span class="article-category" style="background: <?php echo $badgeColor; ?>20; color: <?php echo $badgeColor; ?>;">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($article['categorie']); ?>
                        </span>
                        <div class="article-rating">
                            <?php $randomNote = rand(3,5); for($i=1;$i<=5;$i++): ?>
                                <?php echo ($i <= $randomNote) ? '<i class="fas fa-star" style="color:#fbbf24;font-size:0.7rem"></i>' : '<i class="far fa-star" style="color:rgba(255,255,255,0.3);font-size:0.7rem"></i>'; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <h3 class="article-title"><?php echo htmlspecialchars($article['titre']); ?></h3>
                    <p class="article-excerpt"><?php echo htmlspecialchars(substr($article['resume'], 0, 100)) . '...'; ?></p>
                    <div class="article-footer">
                        <div class="article-meta">
                            <span class="meta-item"><i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($article['created_at'])); ?></span>
                            <span class="meta-item"><i class="far fa-eye"></i> <?php echo number_format($article['vue'] ?? 0); ?></span>
                        </div>
                        <a href="index.php?action=detail&id=<?php echo $article['id']; ?>" class="article-link">
                            <span><?php echo t('read_more'); ?></span> <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if(isset($totalPages) && $totalPages > 1): ?>
    <div class="pagination">
        <?php if($currentPage > 1): ?>
            <a href="?action=blog&order=<?php echo $order ?? 'DESC'; ?>&page=<?php echo $currentPage-1; ?>">« <?php echo t('prev'); ?></a>
        <?php else: ?>
            <a href="#" class="disabled">« <?php echo t('prev'); ?></a>
        <?php endif; ?>
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <?php if($i == $currentPage): ?>
                <a href="#" class="active"><?php echo $i; ?></a>
            <?php else: ?>
                <a href="?action=blog&order=<?php echo $order ?? 'DESC'; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if($currentPage < $totalPages): ?>
            <a href="?action=blog&order=<?php echo $order ?? 'DESC'; ?>&page=<?php echo $currentPage+1; ?>"><?php echo t('next'); ?> »</a>
        <?php else: ?>
            <a href="#" class="disabled"><?php echo t('next'); ?> »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
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

<button class="chatbot-btn" id="chatbotToggle">💬 Assistant IA</button>
<div class="chatbot-window" id="chatbotWindow">
    <div class="chatbot-header">
        <span>🤖 FoodSave IA</span>
        <button id="chatbotClose">✕</button>
    </div>
    <div class="chatbot-messages" id="chatMessages">
        <div class="bot">👋 Bonjour ! Posez-moi vos questions sur le gaspillage alimentaire...</div>
    </div>
    <div class="chatbot-input">
        <input type="text" id="chatInput" placeholder="Votre question...">
        <button id="chatSend">Envoyer</button>
    </div>
</div>

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
    }
}

if (searchInput) {
    searchInput.addEventListener('input', filterArticles);
    searchInput.addEventListener('keyup', filterArticles);
}

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
        messageDiv.innerHTML = '<span style="color: #f87171;">⚠️ Erreur</span>';
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

const toggle = document.getElementById('chatbotToggle');
const close = document.getElementById('chatbotClose');
const windowBot = document.getElementById('chatbotWindow');
const messagesDiv = document.getElementById('chatMessages');
const input = document.getElementById('chatInput');
const send = document.getElementById('chatSend');

if (toggle) toggle.onclick = () => windowBot.classList.add('open');
if (close) close.onclick = () => windowBot.classList.remove('open');

function addMessage(text, isUser = false) {
    const div = document.createElement('div');
    div.className = isUser ? 'user' : 'bot';
    
    // Convertir le markdown et les sauts de ligne en HTML
    let formattedText = text;
    formattedText = formattedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>'); // **gras**
    formattedText = formattedText.replace(/\n/g, '<br>'); // Sauts de ligne
    
    div.innerHTML = (isUser ? '👤 ' : '🤖 ') + formattedText;
    messagesDiv.appendChild(div);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

async function sendMessage() {
    const question = input.value.trim();
    if (!question) return;
    
    addMessage(question, true);
    input.value = '';
    
    try {
        const response = await fetch('index.php?action=chatbot', { 
            method: 'POST', 
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            }, 
            body: 'question=' + encodeURIComponent(question) 
        });
        
        // Vérifier le status HTTP
        if (!response.ok) {
            addMessage('❌ Erreur serveur (HTTP ' + response.status + ')');
            return;
        }
        
        // Parser le JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            addMessage('❌ Format de réponse invalide');
            return;
        }
        
        const data = await response.json();
        
        if (data.success) {
            addMessage(data.response);
        } else {
            addMessage('❌ ' + (data.response || 'Erreur inconnue'));
        }
    } catch (error) {
        addMessage('❌ Erreur de connexion: ' + error.message);
    }
}
if (send) send.onclick = sendMessage;
if (input) input.onkeypress = (e) => { if (e.key === 'Enter') sendMessage(); };
</script>

</body>
</html>