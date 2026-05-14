<?php
// Démarrer la session (pour la langue)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger le traducteur
require_once __DIR__ . '/../../../../foodsave/config/Translator.php';

// Initialiser le traducteur
$translator = Translator::getInstance();
$lang = $translator->getCurrentLang();

// Fonction de traduction
$t = function($key) use ($translator) {
    return $translator->translate($key);
};
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - <?php echo $t('nav_recipes'); ?></title>
    
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
        
        .hero{position:relative;z-index:5;padding:3rem 52px 2rem;text-align:center}
        .hero h1{font-size:2.5rem;font-weight:700;color:#fff;margin-bottom:1rem}
        .hero h1 span{color:#4ade80}
        .hero p{color:rgba(255,255,255,0.5)}
        
        .recipes-section{position:relative;z-index:5;padding:2rem 52px 4rem}
        .section-header{text-align:center;margin-bottom:2rem}
        .section-header h2{font-size:2rem;color:#fff;margin-bottom:0.5rem}
        .section-header p{color:rgba(255,255,255,0.45)}
        
        /* Filtres catégories */
        .filters{display:flex;justify-content:center;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
        .filter-btn{padding:8px 20px;background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.15);border-radius:50px;color:#4ade80;cursor:pointer;font-weight:500;transition:all 0.2s}
        .filter-btn:hover,.filter-btn.active{background:#16a34a;color:#fff;border-color:#16a34a}
        
        /* Barre de recherche et tri */
        .search-sort-bar{display:flex;justify-content:center;gap:15px;margin-bottom:2rem;flex-wrap:wrap}
        .search-input{width:300px;padding:12px 18px;background:rgba(255,255,255,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:50px;color:#fff;font-family:inherit}
        .search-input::placeholder{color:rgba(255,255,255,0.3)}
        .search-input:focus{outline:none;border-color:#4ade80}
        .sort-select{padding:12px 18px;background:rgba(255,255,255,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:50px;color:#fff;font-family:inherit;cursor:pointer}
        .sort-select option{background:#0d1f14}
        .btn-clear{padding:12px 24px;background:#6c757d;border:none;border-radius:50px;color:#fff;cursor:pointer;font-weight:600;transition:all 0.2s}
        .btn-clear:hover{background:#5a6268;transform:translateY(-1px)}
        
        .info-recipes{text-align:center;color:rgba(255,255,255,0.4);font-size:0.9rem;margin-bottom:1rem}
        
        /* ========== STYLES MODERNES POUR LES RECETTES (COMME BLOG) ========== */
        .recipes-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:2rem;max-width:1400px;margin:0 auto}
        
        .recipe-card{
            background:rgba(255,255,255,0.04);
            border:1px solid rgba(74,222,128,0.1);
            border-radius:24px;
            overflow:hidden;
            transition:all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            backdrop-filter:blur(8px);
            position:relative;
            animation:fadeInUp 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1) both;
        }
        
        @keyframes fadeInUp{
            from{opacity:0;transform:translateY(40px)}
            to{opacity:1;transform:translateY(0)}
        }
        
        .recipe-card:hover{
            transform:translateY(-8px);
            background:rgba(74,222,128,0.06);
            border-color:rgba(74,222,128,0.35);
            box-shadow:0 20px 40px rgba(0,0,0,0.3);
        }
        
        /* Wrapper image */
        .recipe-img-wrapper{
            position:relative;
            height:220px;
            overflow:hidden;
            cursor:pointer;
            background:rgba(74,222,128,0.05);
        }
        
        .recipe-img-bg{
            position:absolute;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background-size:cover;
            background-position:center;
            transition:transform 0.6s cubic-bezier(0.2,0.9,0.4,1.1);
            z-index:1;
        }
        
        .recipe-card:hover .recipe-img-bg{
            transform:scale(1.1);
        }
        
        .recipe-dark-overlay{
            position:absolute;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:linear-gradient(135deg, rgba(0,0,0,0.3), rgba(0,0,0,0.5));
            z-index:2;
        }
        
        .recipe-icon-overlay{
            position:absolute;
            top:50%;
            left:50%;
            transform:translate(-50%,-50%);
            z-index:3;
            background:rgba(0,0,0,0.4);
            width:70px;
            height:70px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            backdrop-filter:blur(4px);
            transition:all 0.3s ease;
        }
        
        .recipe-card:hover .recipe-icon-overlay{
            background:rgba(74,222,128,0.8);
            transform:translate(-50%,-50%) scale(1.1);
        }
        
        .recipe-icon-overlay .recipe-fallback-icon{
            font-size:2rem;
            transition:all 0.3s ease;
        }
        
        .recipe-img-fallback{
            width:100%;
            height:100%;
            display:flex;
            align-items:center;
            justify-content:center;
            transition:all 0.4s ease;
        }
        
        .recipe-card:hover .recipe-img-fallback{
            background:linear-gradient(135deg, rgba(74,222,128,0.15), rgba(74,222,128,0.08)) !important;
        }
        
        .recipe-fallback-icon{
            font-size:3.5rem;
            animation:float 3s ease-in-out infinite;
            transition:all 0.3s ease;
        }
        
        .recipe-card:hover .recipe-fallback-icon{
            transform:scale(1.1);
            animation:none;
        }
        
        @keyframes float{
            0%,100%{transform:translateY(0)}
            50%{transform:translateY(-12px)}
        }
        
        .recipe-overlay{
            position:absolute;
            top:0;
            left:0;
            right:0;
            bottom:0;
            background:rgba(13,31,20,0.85);
            backdrop-filter:blur(4px);
            display:flex;
            align-items:center;
            justify-content:center;
            opacity:0;
            transition:opacity 0.4s ease;
            z-index:15;
        }
        
        .recipe-card:hover .recipe-overlay{
            opacity:1;
        }
        
        .recipe-overlay-link{
            background:#16a34a;
            color:#fff;
            padding:12px 28px;
            border-radius:50px;
            text-decoration:none;
            font-weight:600;
            font-size:0.9rem;
            display:flex;
            align-items:center;
            gap:8px;
            transform:translateY(20px);
            transition:all 0.3s ease;
            box-shadow:0 4px 15px rgba(0,0,0,0.3);
        }
        
        .recipe-card:hover .recipe-overlay-link{
            transform:translateY(0);
        }
        
        .recipe-overlay-link:hover{
            background:#15803d;
            gap:12px;
        }
        
        .recipe-content{padding:1.5rem}
        
        .recipe-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:1rem;
            flex-wrap:wrap;
            gap:0.5rem;
        }
        
        .recipe-badge{
            display:inline-flex;
            align-items:center;
            gap:5px;
            padding:4px 12px;
            background:rgba(74,222,128,0.12);
            color:#4ade80;
            border-radius:50px;
            font-size:0.7rem;
            font-weight:600;
        }
        
        .recipe-title{
            font-size:1.2rem;
            font-weight:700;
            color:#fff;
            margin-bottom:0.75rem;
            line-height:1.4;
            transition:color 0.3s ease;
        }
        
        .recipe-card:hover .recipe-title{
            color:#4ade80;
        }
        
        .recipe-desc{
            color:rgba(255,255,255,0.6);
            font-size:0.85rem;
            line-height:1.6;
            margin-bottom:1rem;
        }
        
        .recipe-footer{
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
            gap:1rem;
            margin-top:1rem;
            padding-top:1rem;
            border-top:1px solid rgba(255,255,255,0.05);
        }
        
        .recipe-meta{
            display:flex;
            gap:1rem;
        }
        
        .recipe-meta-item{
            display:inline-flex;
            align-items:center;
            gap:5px;
            font-size:0.7rem;
            color:rgba(255,255,255,0.4);
        }
        
        .recipe-link{
            color:#4ade80;
            text-decoration:none;
            font-weight:600;
            font-size:0.85rem;
            display:inline-flex;
            align-items:center;
            gap:6px;
            transition:all 0.3s ease;
        }
        
        .recipe-link:hover{
            gap:12px;
            color:#86efac;
        }
        
        .empty-state{text-align:center;padding:50px;grid-column:1/-1}
        .empty-state i{font-size:3rem;color:rgba(255,255,255,0.2);margin-bottom:1rem}
        .empty-state p{color:rgba(255,255,255,0.5)}
        
        .footer{background:rgba(8,16,8,0.9);border-top:1px solid rgba(74,222,128,0.08);padding:3rem 52px 1rem;margin-top:3rem}
        .footer-bottom{text-align:center;padding-top:2rem;border-top:1px solid rgba(255,255,255,0.05);font-size:0.8rem;color:rgba(255,255,255,0.3)}
        
        @media (max-width:768px){
            .navbar{padding:22px 20px;flex-direction:column;gap:1rem}
            .nav-links{flex-wrap:wrap;justify-content:center}
            .hero{padding:2rem 20px}
            .hero h1{font-size:2rem}
            .recipes-section{padding:2rem 20px}
            .recipes-grid{grid-template-columns:1fr}
            .language-selector{top:80px;right:15px}
            .filters{gap:0.5rem}
            .filter-btn{padding:6px 12px;font-size:0.8rem}
            .search-sort-bar{flex-direction:column;align-items:center}
            .search-input{width:100%}
        }
        
        .chatbot-btn{position:fixed;bottom:25px;right:25px;background:#16a34a;border:none;border-radius:50px;padding:12px 20px;color:white;cursor:pointer;font-weight:bold;z-index:1000;box-shadow:0 2px 10px rgba(0,0,0,0.2);font-family:inherit}
        .chatbot-window{position:fixed;bottom:80px;right:25px;width:350px;height:450px;background:#0d1f14;border:1px solid #4ade80;border-radius:16px;display:none;flex-direction:column;z-index:1000;overflow:hidden}
        .chatbot-window.open{display:flex}
        .chatbot-header{background:#16a34a;padding:12px;color:white;font-weight:bold;display:flex;justify-content:space-between}
        .chatbot-header button{background:none;border:none;color:white;font-size:18px;cursor:pointer}
        .chatbot-messages{flex:1;overflow-y:auto;padding:12px}
        .chatbot-messages .user{text-align:right;color:#4ade80;margin:8px 0}
        .chatbot-messages .bot{text-align:left;color:white;margin:8px 0}
        .chatbot-input{display:flex;padding:12px;border-top:1px solid #333;gap:8px}
        .chatbot-input input{flex:1;padding:8px 12px;border-radius:20px;border:1px solid #4ade80;background:#1a2a1a;color:white;font-family:inherit}
        .chatbot-input button{background:#16a34a;border:none;border-radius:20px;padding:8px 16px;color:white;cursor:pointer}
    </style>
</head>
<body>

<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div><div class="glow-3"></div>
</div>

<div class="language-selector">
    <a href="?lang=fr&action=recettes" class="<?php echo $lang == 'fr' ? 'active' : ''; ?>">🇫🇷 FR</a>
    <span>|</span>
    <a href="?lang=en&action=recettes" class="<?php echo $lang == 'en' ? 'active' : ''; ?>">🇬🇧 EN</a>
</div>

<nav class="navbar">
    <div class="logo-wrap" onclick="location.href='index.php?action=blog'">
        <img src="./assets/images/logo-foodsave.png" alt="FoodSave">
        <span>FoodSave</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php?action=blog"><?php echo $t('nav_home'); ?></a></li>
        <li><a href="index.php?action=blog"><?php echo $t('nav_blog'); ?></a></li>
        <li><a href="index.php?action=conseils"><?php echo $t('nav_tips'); ?></a></li>
        <li><a href="index.php?action=recettes" class="active"><?php echo $t('nav_recipes'); ?></a></li>
    </ul>
    <div class="nav-btns">
        <button class="btn-ghost" onclick="location.href='index.php?action=login'"><?php echo $t('nav_login'); ?></button>
        <button class="btn-fill" onclick="location.href='index.php?action=register'"><?php echo $t('nav_register'); ?></button>
    </div>
</nav>

<section class="hero">
    <h1>🍳 <span><?php echo $t('nav_recipes'); ?></span> anti-gaspillage</h1>
    <p><?php echo $t('blog_subtitle'); ?></p>
</section>

<section class="recipes-section">
    <div class="section-header">
        <h2>📖 <?php echo $t('nav_recipes'); ?></h2>
        <p><?php echo $t('blog_subtitle'); ?></p>
    </div>
    
    <!-- Filtres par catégorie -->
    <div class="filters" id="filters">
        <button class="filter-btn active" data-filter="all">Toutes</button>
        <button class="filter-btn" data-filter="Astuces">🥕 Astuces</button>
        <button class="filter-btn" data-filter="Recettes">🍲 Recettes</button>
        <button class="filter-btn" data-filter="Conseils">💡 Conseils</button>
    </div>
    
    <!-- Barre de recherche et tri -->
    <div class="search-sort-bar">
        <input type="text" id="searchRecipesInput" class="search-input" placeholder="🔍 Rechercher une recette...">
        <select id="sortRecipesSelect" class="sort-select">
            <option value="date_desc">📅 Plus récentes</option>
            <option value="date_asc">📅 Plus anciennes</option>
            <option value="title_asc">🔤 Titre A-Z</option>
            <option value="title_desc">🔤 Titre Z-A</option>
        </select>
        <button id="clearRecipesBtn" class="btn-clear">✖ Effacer</button>
    </div>
    <div id="infoRecipes" class="info-recipes"></div>
    
    <div class="recipes-grid" id="recipesGrid">
        <?php if(empty($articles)): ?>
            <div class="empty-state">
                <i class="fas fa-utensils"></i>
                <p>Aucune recette pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach($articles as $index => $article): 
                $estRecent = (strtotime($article['created_at']) > strtotime('-7 days'));
                $categorie = $article['categorie'];
                $icon = match($categorie) {
                    'Astuces' => '🥕',
                    'Recettes' => '🍲',
                    'Conseils' => '💡',
                    default => '🍽️'
                };
                $badgeColor = match($categorie) {
                    'Astuces' => '#4ade80',
                    'Recettes' => '#fbbf24',
                    default => '#60a5fa'
                };
                $hasImage = !empty($article['image']) && file_exists(__DIR__ . '/../../../public/uploads/' . $article['image']);
                $timestamp = strtotime($article['created_at']);
            ?>
            <div class="recipe-card" data-category="<?php echo htmlspecialchars($categorie); ?>" data-titre="<?php echo strtolower(htmlspecialchars($article['titre'])); ?>" data-date="<?php echo $timestamp; ?>" style="animation-delay: <?php echo $index * 0.05; ?>s">
                
                <?php if($estRecent): ?>
                    <div class="article-badge-new" style="position:absolute;top:16px;right:16px;background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#fff;padding:5px 14px;border-radius:50px;font-size:0.7rem;font-weight:700;z-index:10;box-shadow:0 4px 15px rgba(0,0,0,0.2);animation:pulse 2s infinite;display:flex;align-items:center;gap:5px">
                        <i class="fas fa-bolt"></i> Nouveau
                    </div>
                <?php endif; ?>
                
                <div class="recipe-img-wrapper">
                    <?php if($hasImage): ?>
                        <div class="recipe-img-bg" style="background-image: url('./assets/uploads/<?php echo $article['image']; ?>');" ></div>
                        <div class="recipe-dark-overlay"></div>
                        <div class="recipe-icon-overlay">
                            <div class="recipe-fallback-icon" style="font-size:2rem;opacity:0.8"><?php echo $icon; ?></div>
                        </div>
                    <?php else: ?>
                        <div class="recipe-img-fallback" style="background:linear-gradient(135deg, rgba(74,222,128,0.1), rgba(74,222,128,0.03));">
                            <div class="recipe-fallback-icon"><?php echo $icon; ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="recipe-overlay">
                        <a href="index.php?action=detail&id=<?php echo $article['id']; ?>" class="recipe-overlay-link">
                            <i class="fas fa-arrow-right"></i> Lire la recette
                        </a>
                    </div>
                </div>
                
                <div class="recipe-content">
                    <div class="recipe-header">
                        <span class="recipe-badge" style="background:<?php echo $badgeColor; ?>20; color:<?php echo $badgeColor; ?>">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($categorie); ?>
                        </span>
                        <div class="recipe-rating">
                            <?php $randomNote = rand(4,5); for($i=1;$i<=5;$i++): ?>
                                <?php if($i<=$randomNote): ?>
                                    <i class="fas fa-star" style="color:#fbbf24;font-size:0.7rem"></i>
                                <?php else: ?>
                                    <i class="far fa-star" style="color:rgba(255,255,255,0.3);font-size:0.7rem"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <h3 class="recipe-title"><?php echo htmlspecialchars($article['titre']); ?></h3>
                    <p class="recipe-desc"><?php echo htmlspecialchars(substr($article['resume'], 0, 100)) . '...'; ?></p>
                    
                    <div class="recipe-footer">
                        <div class="recipe-meta">
                            <span class="recipe-meta-item"><i class="far fa-calendar-alt"></i> <?php echo date('d M Y', $timestamp); ?></span>
                            <span class="recipe-meta-item"><i class="far fa-eye"></i> <?php echo number_format($article['vue'] ?? 0); ?> vues</span>
                        </div>
                        <a href="index.php?action=detail&id=<?php echo $article['id']; ?>" class="recipe-link"><?php echo $t('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<footer class="footer">
    <div class="footer-bottom">
        <p>© 2025 FoodSave - <?php echo $t('footer_copyright'); ?></p>
    </div>
</footer>

<style>
    @keyframes pulse{0%,100%{transform:scale(1);box-shadow:0 4px 15px rgba(0,0,0,0.2)}50%{transform:scale(1.05);box-shadow:0 6px 20px rgba(251,191,36,0.4)}}
</style>

<script>
// Recherche, tri et filtres pour les recettes
const searchInput = document.getElementById('searchRecipesInput');
const sortSelect = document.getElementById('sortRecipesSelect');
const clearBtn = document.getElementById('clearRecipesBtn');
const filterBtns = document.querySelectorAll('.filter-btn');
const infoDiv = document.getElementById('infoRecipes');
const recipesGrid = document.getElementById('recipesGrid');

let recipesArray = [];
let currentFilter = 'all';

function initRecipesArray() {
    const cards = document.querySelectorAll('.recipe-card');
    recipesArray = Array.from(cards).map((card, idx) => {
        let titre = card.getAttribute('data-titre');
        let date = parseInt(card.getAttribute('data-date'));
        let category = card.getAttribute('data-category') || '';
        if (!titre) {
            const titleElem = card.querySelector('.recipe-title');
            titre = titleElem ? titleElem.innerText.toLowerCase() : '';
        }
        if (isNaN(date)) date = idx;
        return { element: card, titre: titre, date: date, category: category, originalIndex: idx };
    });
}

function filterAndSortRecipes() {
    if (recipesArray.length === 0) initRecipesArray();
    const searchTerm = searchInput.value.toLowerCase().trim();
    const sortValue = sortSelect.value;
    let filtered = [...recipesArray];
    if (searchTerm !== '') filtered = filtered.filter(recipe => recipe.titre.includes(searchTerm));
    if (currentFilter !== 'all') filtered = filtered.filter(recipe => recipe.category === currentFilter);
    
    switch(sortValue) {
        case 'date_desc': filtered.sort((a,b) => b.date - a.date); break;
        case 'date_asc': filtered.sort((a,b) => a.date - b.date); break;
        case 'title_asc': filtered.sort((a,b) => a.titre.localeCompare(b.titre)); break;
        case 'title_desc': filtered.sort((a,b) => b.titre.localeCompare(a.titre)); break;
        default: filtered.sort((a,b) => b.date - a.date);
    }
    
    if (recipesGrid) {
        filtered.forEach((recipe, newIndex) => {
            recipe.element.style.animation = 'none';
            recipe.element.offsetHeight;
            recipe.element.style.animation = `fadeInUp 0.5s cubic-bezier(0.2,0.9,0.4,1.1) both`;
            recipe.element.style.animationDelay = `${newIndex * 0.05}s`;
            recipesGrid.appendChild(recipe.element);
        });
    }
    
    const visibleCount = filtered.length;
    if (infoDiv) {
        if (searchTerm === '' && currentFilter === 'all') infoDiv.innerHTML = `📊 ${visibleCount} recette(s) affichée(s)`;
        else if (searchTerm !== '' && currentFilter !== 'all') infoDiv.innerHTML = `🔍 ${visibleCount} résultat(s) trouvé(s) pour "${searchTerm}" dans ${currentFilter}`;
        else if (searchTerm !== '') infoDiv.innerHTML = `🔍 ${visibleCount} résultat(s) trouvé(s) pour "${searchTerm}"`;
        else if (currentFilter !== 'all') infoDiv.innerHTML = `📁 ${visibleCount} recette(s) dans "${currentFilter}"`;
    }
}

filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.getAttribute('data-filter');
        filterAndSortRecipes();
    });
});

if (searchInput) { searchInput.addEventListener('input', filterAndSortRecipes); searchInput.addEventListener('keyup', filterAndSortRecipes); }
if (sortSelect) sortSelect.addEventListener('change', filterAndSortRecipes);
if (clearBtn) {
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        sortSelect.value = 'date_desc';
        filterBtns.forEach(btn => btn.classList.remove('active'));
        document.querySelector('.filter-btn[data-filter="all"]').classList.add('active');
        currentFilter = 'all';
        filterAndSortRecipes();
        searchInput.focus();
    });
}
document.addEventListener('DOMContentLoaded', function() { initRecipesArray(); filterAndSortRecipes(); });

// Chatbot
const toggle = document.getElementById('chatbotToggle');
const close = document.getElementById('chatbotClose');
const windowBot = document.getElementById('chatbotWindow');
const messagesDiv = document.getElementById('chatMessages');
const input = document.getElementById('chatInput');
const send = document.getElementById('chatSend');

toggle.onclick = () => windowBot.classList.add('open');
close.onclick = () => windowBot.classList.remove('open');

function addMessage(text, isUser = false) {
    const div = document.createElement('div');
    div.className = isUser ? 'user' : 'bot';
    div.innerHTML = isUser ? '👤 ' + text : '🤖 ' + text;
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
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'question=' + encodeURIComponent(question)
        });
        const data = await response.json();
        addMessage(data.response);
    } catch (error) {
        addMessage('❌ Erreur, veuillez réessayer.');
    }
}
send.onclick = sendMessage;
input.onkeypress = (e) => { if (e.key === 'Enter') sendMessage(); };
</script>

</body>
</html>