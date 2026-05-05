<?php
// Démarrer la session (pour la langue)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// TEMPORAIRE : Forcer la connexion (à supprimer après test)
$_SESSION['user_id'] = 1;

// Charger le traducteur
require_once 'C:/xampp/htdocs/FoodSave/app/core/Translator.php';

// Initialiser le traducteur
$translator = Translator::getInstance();
$lang = $translator->getCurrentLang();

// Fonction de traduction
$t = function($key) use ($translator) {
    return $translator->translate($key);
};

if(!isset($article) || !isset($avis) || !isset($nbAvis) || !isset($noteMoyenne)) {
    header('Location: index.php?action=blog');
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - <?php echo $t('reviews'); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:"DM Sans",sans-serif;background:#0d1f14;color:#e8f5e9;overflow-x:hidden}
        .bg-mesh{position:fixed;inset:0;pointer-events:none;z-index:0}
        .glow-1{position:absolute;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(34,197,94,0.18) 0%,transparent 70%);top:-80px;right:-60px;animation:driftA 8s ease-in-out infinite alternate}
        .glow-2{position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(16,185,129,0.12) 0%,transparent 70%);bottom:0;left:-80px;animation:driftB 10s ease-in-out infinite alternate}
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
        .btn-ghost{padding:8px 20px;border:1px solid rgba(74,222,128,0.35);border-radius:50px;background:transparent;color:#4ade80;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit}
        .btn-ghost:hover{background:rgba(74,222,128,0.08)}
        .btn-fill{padding:8px 22px;border:none;border-radius:50px;background:#16a34a;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;box-shadow:0 0 20px rgba(22,163,74,0.45)}
        .btn-fill:hover{background:#15803d;transform:translateY(-1px)}
        
        .avis-container{position:relative;z-index:5;max-width:900px;margin:0 auto;padding:2rem}
        .btn-back{color:#4ade80;text-decoration:none;margin-bottom:1rem;display:inline-block;font-weight:500}
        .btn-back:hover{text-decoration:underline}
        
        .avis-stats{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:20px;padding:1.5rem;text-align:center;margin-bottom:2rem}
        .note-moyenne{font-size:2.5rem;font-weight:800;color:#4ade80}
        .avis-note{color:#ffc107;font-size:1rem;margin-top:0.5rem}
        
        .avis-list{display:flex;flex-direction:column;gap:1rem}
        .avis-card{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:20px;padding:1.5rem;transition:all 0.3s ease}
        .avis-card:hover{background:rgba(74,222,128,0.05);transform:translateY(-3px)}
        
        .avis-author{display:flex;align-items:center;gap:1rem;margin-bottom:1rem}
        .avatar{width:45px;height:45px;background:linear-gradient(135deg,#4caf50,#ff6b35);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;color:#fff}
        .author-info h4{color:#fff;margin-bottom:0.25rem;font-size:1rem}
        .author-info .date{font-size:0.7rem;color:rgba(255,255,255,0.4)}
        
        .avis-stars{color:#ffc107;margin-bottom:0.5rem;font-size:0.9rem}
        .avis-content{color:rgba(255,255,255,0.7);line-height:1.6;margin-top:0.5rem}
        
        .btn-modifier{display:inline-block;margin-top:12px;color:#fbbf24;text-decoration:none;font-size:0.8rem;font-weight:500;transition:all 0.2s}
        .btn-modifier:hover{color:#ffc107;text-decoration:underline}
        
        .btn-avis{padding:10px 20px;background:#16a34a;color:#fff;border:none;border-radius:50px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;transition:all 0.2s}
        .btn-avis:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(22,163,74,0.3)}
        
        .empty-avis{text-align:center;padding:3rem;color:rgba(255,255,255,0.5)}
        .empty-avis i{font-size:3rem;margin-bottom:1rem;opacity:0.5}
        
        .footer{background:rgba(8,16,8,0.9);border-top:1px solid rgba(74,222,128,0.08);padding:3rem 52px 1rem;margin-top:3rem}
        .footer-bottom{text-align:center;padding-top:2rem;border-top:1px solid rgba(255,255,255,0.05);font-size:0.8rem;color:rgba(255,255,255,0.3)}
        
        @media (max-width:768px){
            .navbar{padding:22px 20px;flex-direction:column;gap:1rem}
            .nav-links{flex-wrap:wrap;justify-content:center}
            .avis-container{padding:1rem}
        }
    </style>
</head>
<body>

<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div>
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
        <li><a href="index.php?action=recettes"><?php echo $t('nav_recipes'); ?></a></li>
    </ul>
    <div class="nav-btns">
        <button class="btn-ghost" onclick="location.href='index.php?action=login'"><?php echo $t('nav_login'); ?></button>
        <button class="btn-fill" onclick="location.href='index.php?action=register'"><?php echo $t('nav_register'); ?></button>
    </div>
</nav>

<div class="avis-container">
    <a href="index.php?action=detail&id=<?php echo $article['id']; ?>" class="btn-back">
        ← <?php echo $t('back_to_article'); ?>
    </a>
    
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem">
        <h1 style="font-size:1.8rem;color:#fff">⭐ <?php echo $t('reviews'); ?></h1>
        <a href="index.php?action=addAvisForm&article_id=<?php echo $article['id']; ?>" class="btn-avis">
            <i class="fas fa-pen"></i> <?php echo $t('write_review'); ?>
        </a>
    </div>
    
    <div class="avis-stats">
        <div class="note-moyenne"><?php echo $noteMoyenne; ?> / 5</div>
        <div class="avis-note">
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
        <div style="color:rgba(255,255,255,0.5);margin-top:0.5rem"><?php echo $t('based_on'); ?> <?php echo $nbAvis; ?> <?php echo $t('reviews_count'); ?></div>
    </div>
    
    <div class="avis-list">
        <?php if(empty($avis)): ?>
            <div class="empty-avis">
                <i class="fas fa-comment-dots"></i>
                <p><?php echo $t('no_reviews'); ?></p>
                <p style="font-size:0.9rem;margin-top:0.5rem">Soyez le premier à donner votre avis !</p>
            </div>
        <?php else: ?>
            <?php foreach($avis as $a): ?>
            <div class="avis-card">
                <div class="avis-author">
                    <div class="avatar">
                        <?php echo strtoupper(substr($a['user_name'], 0, 1)); ?>
                    </div>
                    <div class="author-info">
                        <h4><?php echo htmlspecialchars($a['user_name']); ?></h4>
                        <div class="date">📅 <?php echo date('d/m/Y', strtotime($a['created_at'])); ?></div>
                    </div>
                </div>
                <div class="avis-stars">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php if($i <= $a['note']): ?>
                            <i class="fas fa-star"></i>
                        <?php else: ?>
                            <i class="far fa-star"></i>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <div class="avis-content">
                    "<?php echo nl2br(htmlspecialchars($a['contenu'])); ?>"
                </div>
                
                <?php if($user_id !== null && isset($a['user_id']) && $a['user_id'] == $user_id): ?>
                <div>
                    <a href="index.php?action=editUserAvis&id=<?php echo $a['id']; ?>" class="btn-modifier">
                        <i class="fas fa-edit"></i> <?php echo $t('edit_review'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="footer-bottom">
        <p>© 2025 FoodSave - <?php echo $t('footer_copyright'); ?></p>
    </div>
</footer>

</body>
</html>