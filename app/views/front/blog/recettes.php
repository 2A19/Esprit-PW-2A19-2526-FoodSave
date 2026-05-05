<?php
// Démarrer la session (pour la langue)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger le traducteur
require_once 'C:/xampp/htdocs/FoodSave/app/core/Translator.php';

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
        
        .filters{display:flex;justify-content:center;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
        .filter-btn{padding:8px 20px;background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.15);border-radius:50px;color:#4ade80;cursor:pointer;font-weight:500;transition:all 0.2s}
        .filter-btn:hover,.filter-btn.active{background:#16a34a;color:#fff;border-color:#16a34a}
        
        .recipes-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(350px,1fr));gap:2rem;max-width:1400px;margin:0 auto}
        .recipe-card{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:20px;overflow:hidden;transition:all 0.3s ease}
        .recipe-card:hover{transform:translateY(-5px);background:rgba(74,222,128,0.05);border-color:rgba(74,222,128,0.25)}
        .recipe-icon{height:120px;background:rgba(74,222,128,0.08);display:flex;align-items:center;justify-content:center;font-size:3rem}
        .recipe-content{padding:1.5rem}
        .recipe-title{font-size:1.3rem;font-weight:700;color:#fff;margin-bottom:0.5rem}
        .recipe-desc{color:rgba(255,255,255,0.6);font-size:0.9rem;margin-bottom:1rem;line-height:1.5}
        .recipe-meta{display:flex;gap:1rem;margin-bottom:1rem;font-size:0.8rem;color:#4ade80}
        .recipe-ingredients{color:rgba(255,255,255,0.5);font-size:0.85rem;margin-bottom:1rem;padding-top:0.5rem;border-top:1px solid rgba(255,255,255,0.05)}
        .recipe-link{color:#4ade80;text-decoration:none;font-weight:600;font-size:0.9rem;display:inline-flex;align-items:center;gap:5px}
        
        .footer{background:rgba(8,16,8,0.9);border-top:1px solid rgba(74,222,128,0.08);padding:3rem 52px 1rem;margin-top:3rem}
        .footer-bottom{text-align:center;padding-top:2rem;border-top:1px solid rgba(255,255,255,0.05);font-size:0.8rem;color:rgba(255,255,255,0.3)}
        
        @media (max-width:768px){
            .navbar{padding:22px 20px;flex-direction:column;gap:1rem}
            .nav-links{flex-wrap:wrap;justify-content:center}
            .hero{padding:2rem 20px}
            .recipes-section{padding:2rem 20px}
            .recipes-grid{grid-template-columns:1fr}
            .language-selector{top:80px;right:15px}
        }
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
        <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="FoodSave">
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
    
    <div class="filters">
        <button class="filter-btn active" data-filter="all">Toutes</button>
        <button class="filter-btn" data-filter="Entrée">Entrées</button>
        <button class="filter-btn" data-filter="Plat">Plats</button>
        <button class="filter-btn" data-filter="Dessert">Desserts</button>
        <button class="filter-btn" data-filter="Soupe">Soupes</button>
    </div>
    
    <div class="recipes-grid" id="recipesGrid">
        <div class="recipe-card" data-category="Soupe">
            <div class="recipe-icon">🥣</div>
            <div class="recipe-content">
                <h3 class="recipe-title">Soupe aux épluchures de légumes</h3>
                <p class="recipe-desc">Ne jetez plus vos épluchures ! Transformez-les en une délicieuse soupe réconfortante.</p>
                <div class="recipe-meta">
                    <span><i class="far fa-clock"></i> 25 min</span>
                    <span><i class="fas fa-coins"></i> ~1€</span>
                </div>
                <div class="recipe-ingredients">
                    <strong>Ingrédients :</strong> Épluchures de carottes, pommes de terre, poireaux, oignon, eau, sel, épices
                </div>
                <a href="#" class="recipe-link"><?php echo $t('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="recipe-card" data-category="Dessert">
            <div class="recipe-icon">🍞</div>
            <div class="recipe-content">
                <h3 class="recipe-title">Pain perdu aux fruits abîmés</h3>
                <p class="recipe-desc">Redonnez vie à votre pain rassis et à vos fruits un peu mous.</p>
                <div class="recipe-meta">
                    <span><i class="far fa-clock"></i> 15 min</span>
                    <span><i class="fas fa-coins"></i> ~2€</span>
                </div>
                <div class="recipe-ingredients">
                    <strong>Ingrédients :</strong> Pain rassis, fruits mous (pommes, bananes), œufs, lait, cannelle
                </div>
                <a href="#" class="recipe-link"><?php echo $t('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="recipe-card" data-category="Plat">
            <div class="recipe-icon">🍝</div>
            <div class="recipe-content">
                <h3 class="recipe-title">Pâtes aux restes de légumes</h3>
                <p class="recipe-desc">Utilisez tous les légumes qui traînent dans votre frigo.</p>
                <div class="recipe-meta">
                    <span><i class="far fa-clock"></i> 20 min</span>
                    <span><i class="fas fa-coins"></i> ~1.50€</span>
                </div>
                <div class="recipe-ingredients">
                    <strong>Ingrédients :</strong> Pâtes, restes de légumes, ail, huile d'olive, parmesan
                </div>
                <a href="#" class="recipe-link"><?php echo $t('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="recipe-card" data-category="Entrée">
            <div class="recipe-icon">🥗</div>
            <div class="recipe-content">
                <h3 class="recipe-title">Salade de fanes de carottes</h3>
                <p class="recipe-desc">Les fanes de carottes se mangent aussi ! Une salade originale et zéro déchet.</p>
                <div class="recipe-meta">
                    <span><i class="far fa-clock"></i> 10 min</span>
                    <span><i class="fas fa-coins"></i> ~0.50€</span>
                </div>
                <div class="recipe-ingredients">
                    <strong>Ingrédients :</strong> Fanes de carottes, vinaigrette, graines de sésame, noix
                </div>
                <a href="#" class="recipe-link"><?php echo $t('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="recipe-card" data-category="Plat">
            <div class="recipe-icon">🍲</div>
            <div class="recipe-content">
                <h3 class="recipe-title">Cake aux fanes de radis</h3>
                <p class="recipe-desc">Un cake salé parfait pour l'apéro ou un pique-nique.</p>
                <div class="recipe-meta">
                    <span><i class="far fa-clock"></i> 40 min</span>
                    <span><i class="fas fa-coins"></i> ~1.50€</span>
                </div>
                <div class="recipe-ingredients">
                    <strong>Ingrédients :</strong> Fanes de radis, farine, œufs, fromage, huile d'olive
                </div>
                <a href="#" class="recipe-link"><?php echo $t('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="recipe-card" data-category="Soupe">
            <div class="recipe-icon">🍜</div>
            <div class="recipe-content">
                <h3 class="recipe-title">Bouillon anti-gaspi</h3>
                <p class="recipe-desc">Un bouillon maison à base d'épluchures et de restes de légumes.</p>
                <div class="recipe-meta">
                    <span><i class="far fa-clock"></i> 45 min</span>
                    <span><i class="fas fa-coins"></i> ~0€</span>
                </div>
                <div class="recipe-ingredients">
                    <strong>Ingrédients :</strong> Épluchures, chutes de légumes, eau, herbes aromatiques
                </div>
                <a href="#" class="recipe-link"><?php echo $t('read_more'); ?> <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        
        <?php if(isset($articles) && !empty($articles)): ?>
            <?php foreach($articles as $article): ?>
            <div class="recipe-card" data-category="<?php echo htmlspecialchars($article['categorie']); ?>">
                <div class="recipe-icon">🍳</div>
                <div class="recipe-content">
                    <h3 class="recipe-title"><?php echo htmlspecialchars($article['titre']); ?></h3>
                    <p class="recipe-desc"><?php echo htmlspecialchars(substr($article['resume'], 0, 100)) . '...'; ?></p>
                    <a href="index.php?action=detail&id=<?php echo $article['id']; ?>" class="recipe-link">
                        <?php echo $t('read_more'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
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

<script>
const filterBtns = document.querySelectorAll('.filter-btn');
const recipeCards = document.querySelectorAll('.recipe-card');

filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const filter = btn.getAttribute('data-filter');
        
        recipeCards.forEach(card => {
            if (filter === 'all' || card.getAttribute('data-category') === filter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>

</body>
</html>