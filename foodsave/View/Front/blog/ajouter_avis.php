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

if(!isset($article)) {
    header('Location: index.php?action=blog');
    exit;
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - <?php echo $t('write_review'); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:"DM Sans",sans-serif;background:#0d1f14;color:#e8f5e9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
        
        .bg-mesh{position:fixed;inset:0;pointer-events:none;z-index:0}
        .glow-1{position:absolute;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(34,197,94,0.18) 0%,transparent 70%);top:-80px;right:-60px;animation:driftA 8s ease-in-out infinite alternate}
        .glow-2{position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(16,185,129,0.12) 0%,transparent 70%);bottom:0;left:-80px;animation:driftB 10s ease-in-out infinite alternate}
        @keyframes driftA{from{transform:translate(0,0)}to{transform:translate(30px,20px)}}
        @keyframes driftB{from{transform:translate(0,0)}to{transform:translate(-20px,-30px)}}
        .grid-lines{position:absolute;inset:0;background-image:linear-gradient(rgba(34,197,94,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(34,197,94,0.04) 1px,transparent 1px);background-size:48px 48px}
        
        .language-selector{position:fixed;top:20px;right:20px;z-index:100;background:rgba(15,31,16,0.85);backdrop-filter:blur(10px);padding:8px 15px;border-radius:30px;border:1px solid rgba(74,222,128,0.2)}
        .language-selector a{text-decoration:none;margin:0 5px;color:rgba(255,255,255,0.6)}
        .language-selector a.active{font-weight:bold;color:#4ade80}
        
        .form-container{max-width:600px;width:100%;margin:0 auto;position:relative;z-index:1}
        .form-card{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.15);border-radius:24px;padding:2rem;backdrop-filter:blur(20px)}
        .form-title{font-size:24px;font-weight:700;color:#4ade80;margin-bottom:6px}
        .form-sub{font-size:13px;color:rgba(255,255,255,0.45);margin-bottom:24px}
        
        .article-info{background:rgba(74,222,128,0.08);padding:12px;border-radius:12px;margin-bottom:20px;text-align:center;border:1px solid rgba(74,222,128,0.15)}
        
        .form-group{margin-bottom:20px}
        label{display:block;font-size:13px;font-weight:600;color:rgba(255,255,255,0.7);margin-bottom:8px}
        
        /* ===== CORRECTION DES CHAMPS POUR MEILLEURE LISIBILITÉ ===== */
        textarea, input, select {
            width:100%;
            padding:12px 15px;
            background-color: #1a2a1a !important;
            border:1px solid #4ade80 !important;
            border-radius:12px;
            color:#ffffff !important;
            font-size:14px;
            font-family:inherit;
            transition:all 0.2s;
            resize:vertical;
            min-height:120px;
        }
        
        textarea::placeholder, input::placeholder {
            color: #8ba888 !important;
            opacity: 1;
        }
        
        textarea:focus, input:focus, select:focus {
            outline:none;
            background-color: #0d1f14 !important;
            border-color: #fbbf24 !important;
            box-shadow:0 0 0 3px rgba(74,222,128,0.2);
        }
        
        select option {
            background-color: #1a2a1a !important;
            color: #ffffff !important;
        }
        
        .rating{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:10px;margin-top:8px;}
        .rating input{display:none}
        .rating label{font-size:28px;color:#ddd;cursor:pointer;transition:all 0.2s}
        .rating input:checked ~ label,.rating label:hover,.rating label:hover ~ label{color:#ffc107}
        
        .btn-submit{padding:12px 24px;background:linear-gradient(135deg,#16a34a,#15803d);border:none;border-radius:50px;color:#fff;font-size:14px;font-weight:700;cursor:pointer;transition:all 0.2s;width:100%}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(22,163,74,0.5)}
        .btn-back{display:inline-block;margin-bottom:1rem;color:#4ade80;text-decoration:none;font-size:14px}
        .btn-back:hover{text-decoration:underline}
        
        .error-global{background:rgba(239,68,68,0.12);color:#f87171;padding:12px;border-radius:12px;margin-bottom:1rem;display:none;border:1px solid rgba(239,68,68,0.2)}
        .error-message{color:#f87171;font-size:12px;margin-top:5px}
        
        .required-star{color:#f87171}
        small{color:rgba(255,255,255,0.35);font-size:12px}
    </style>
</head>
<body>

<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div>
</div>

<div class="language-selector">
    <a href="?lang=fr&action=addAvisForm&article_id=<?php echo $article['id']; ?>" class="<?php echo $lang == 'fr' ? 'active' : ''; ?>">🇫🇷 FR</a>
    <span>|</span>
    <a href="?lang=en&action=addAvisForm&article_id=<?php echo $article['id']; ?>" class="<?php echo $lang == 'en' ? 'active' : ''; ?>">🇬🇧 EN</a>
</div>

<div class="form-container">
    <a href="index.php?action=showAvis&article_id=<?php echo $article['id']; ?>" class="btn-back">
        ← <?php echo $t('back_to_reviews'); ?>
    </a>
    
    <div class="form-card">
        <div class="form-title">⭐ <?php echo $t('write_review'); ?></div>
        <div class="form-sub"><?php echo $t('share_experience'); ?></div>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="error-global" style="display:block;">
                ❌ <?php echo $t('error_occurred'); ?>
            </div>
        <?php endif; ?>
        
        <div id="erreurGlobal" class="error-global">
            ⚠️ <?php echo $t('please_correct_errors'); ?>
        </div>
        
        <div class="article-info">
            <strong><?php echo $t('article'); ?> :</strong> <?php echo htmlspecialchars($article['titre']); ?>
        </div>
        
        <form id="avisForm" action="index.php?action=addAvis" method="POST">
            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
            
            <div class="form-group">
                <label><?php echo $t('your_rating'); ?> <span class="required-star">*</span></label>
                <div class="rating" id="ratingContainer">
                    <input type="radio" name="note" id="star5" value="5"><label for="star5">★</label>
                    <input type="radio" name="note" id="star4" value="4"><label for="star4">★</label>
                    <input type="radio" name="note" id="star3" value="3"><label for="star3">★</label>
                    <input type="radio" name="note" id="star2" value="2"><label for="star2">★</label>
                    <input type="radio" name="note" id="star1" value="1"><label for="star1">★</label>
                </div>
            </div>
            
            <div class="form-group">
                <label><?php echo $t('your_review'); ?> <span class="required-star">*</span></label>
                <textarea id="contenu" name="contenu" rows="5" placeholder="<?php echo $t('share_experience'); ?>"></textarea>
                <small><?php echo $t('min_5_chars'); ?></small>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> <?php echo $t('submit_review'); ?>
            </button>
        </form>
    </div>
</div>

<script src="/public/js/validationAvis.js"></script>
<script src="/public/js/ajouter_avis.js"></script>

</body>
</html>