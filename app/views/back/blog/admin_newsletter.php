<?php
// Démarrer la session pour les messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Admin Newsletter</title>
    
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:"DM Sans",sans-serif;background:#0d1f14;color:#e8f5e9;padding:2rem}
        
        .bg-mesh{position:fixed;inset:0;pointer-events:none;z-index:0}
        .glow-1{position:absolute;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(34,197,94,0.18) 0%,transparent 70%);top:-80px;right:-60px;animation:driftA 8s ease-in-out infinite alternate}
        .glow-2{position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(16,185,129,0.12) 0%,transparent 70%);bottom:0;left:-80px;animation:driftB 10s ease-in-out infinite alternate}
        @keyframes driftA{from{transform:translate(0,0)}to{transform:translate(30px,20px)}}
        @keyframes driftB{from{transform:translate(0,0)}to{transform:translate(-20px,-30px)}}
        .grid-lines{position:absolute;inset:0;background-image:linear-gradient(rgba(34,197,94,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(34,197,94,0.04) 1px,transparent 1px);background-size:48px 48px}
        
        .container{max-width:800px;margin:0 auto;position:relative;z-index:1}
        
        .btn-back{display:inline-flex;align-items:center;gap:8px;margin-bottom:1.5rem;color:#4ade80;text-decoration:none;font-weight:500;transition:all 0.2s}
        .btn-back:hover{transform:translateX(-3px);color:#86efac}
        
        .card{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.15);border-radius:24px;padding:2rem;backdrop-filter:blur(20px)}
        .card h1{font-size:1.8rem;color:#4ade80;margin-bottom:0.5rem;display:flex;align-items:center;gap:10px}
        .card p{color:rgba(255,255,255,0.5);margin-bottom:1.5rem}
        
        .stats-card{background:rgba(74,222,128,0.08);border-radius:16px;padding:1rem;text-align:center;margin-bottom:2rem}
        .stats-number{font-size:2.5rem;font-weight:800;color:#4ade80}
        .stats-label{color:rgba(255,255,255,0.5);font-size:0.8rem;text-transform:uppercase;letter-spacing:1px}
        
        .form-group{margin-bottom:1.5rem}
        label{display:block;margin-bottom:0.5rem;font-weight:600;color:rgba(255,255,255,0.8)}
        .required:after{content:" *";color:#f87171}
        input,textarea,select{
            width:100%;padding:12px 15px;background:rgba(255,255,255,0.05);
            border:1px solid rgba(74,222,128,0.15);border-radius:12px;
            color:#fff;font-size:14px;font-family:inherit;transition:all 0.2s
        }
        input:focus,textarea:focus,select:focus{
            outline:none;border-color:rgba(74,222,128,0.5);background:rgba(74,222,128,0.05);
            box-shadow:0 0 0 3px rgba(74,222,128,0.08)
        }
        textarea{resize:vertical;min-height:150px}
        
        .btn-submit{padding:12px 28px;background:#16a34a;border:none;border-radius:50px;color:#fff;font-size:14px;font-weight:700;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:8px}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(22,163,74,0.5)}
        
        .success{background:rgba(34,197,94,0.15);color:#4ade80;padding:12px;border-radius:12px;margin-bottom:1rem;border:1px solid rgba(34,197,94,0.2);display:flex;align-items:center;gap:10px}
        .error{background:rgba(239,68,68,0.15);color:#f87171;padding:12px;border-radius:12px;margin-bottom:1rem;border:1px solid rgba(239,68,68,0.2);display:flex;align-items:center;gap:10px}
        
        hr{margin:1rem 0;border-color:rgba(255,255,255,0.05)}
        .info-text{font-size:0.75rem;color:rgba(255,255,255,0.35);margin-top:0.5rem}
    </style>
</head>
<body>

<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div>
</div>

<div class="container">
    <a href="index.php?action=adminArticles" class="btn-back">
        <i class="fas fa-arrow-left"></i> Retour à l'administration
    </a>
    
    <div class="card">
        <h1>
            <i class="fas fa-envelope" style="color: #4ade80;"></i> 
            Envoyer une newsletter
        </h1>
        <p>Envoyez un email à tous vos abonnés</p>
        
        <div class="stats-card">
            <div class="stats-number"><?php echo $subscribersCount ?? 0; ?></div>
            <div class="stats-label">abonné(s) actif(s)</div>
        </div>
        
        <?php if(isset($_SESSION['newsletter_success'])): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i> 
                <?php echo $_SESSION['newsletter_success']; unset($_SESSION['newsletter_success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['newsletter_error'])): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> 
                <?php echo $_SESSION['newsletter_error']; unset($_SESSION['newsletter_error']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="index.php?action=sendNewsletter">
            <div class="form-group">
                <label class="required">Sujet</label>
                <input type="text" name="sujet" required placeholder="Ex: Nouvel article sur FoodSave">
            </div>
            
            <div class="form-group">
                <label class="required">Message</label>
                <textarea name="message" rows="6" required placeholder="Découvrez notre dernier article..."></textarea>
                <div class="info-text">Le message sera envoyé tel quel. Vous pouvez utiliser du texte simple ou du HTML.</div>
            </div>
            
            <div class="form-group">
                <label>Article associé (optionnel)</label>
                <select name="article_id">
                    <option value="">-- Aucun article --</option>
                    <?php if(isset($articles) && !empty($articles)): ?>
                        <?php foreach($articles as $a): ?>
                            <option value="<?php echo $a['id']; ?>"><?php echo htmlspecialchars($a['titre']); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="info-text">Si vous sélectionnez un article, un bouton "Lire l'article" sera ajouté dans l'email.</div>
            </div>
            
            <hr>
            
            <button type="submit" class="btn-submit" onclick="return confirm('Êtes-vous sûr de vouloir envoyer cette newsletter à tous les abonnés ?')">
                <i class="fas fa-paper-plane"></i> Envoyer la newsletter
            </button>
        </form>
    </div>
</div>

</body>
</html>