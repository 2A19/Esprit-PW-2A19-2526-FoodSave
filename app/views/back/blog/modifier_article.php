<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Modifier un article</title>
    
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        
        .form-container{max-width:800px;width:100%;margin:0 auto;position:relative;z-index:1}
        .form-card{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.15);border-radius:24px;padding:2rem;backdrop-filter:blur(20px)}
        .form-title{font-size:24px;font-weight:700;color:#fbbf24;margin-bottom:6px}
        .form-sub{font-size:13px;color:rgba(255,255,255,0.45);margin-bottom:24px}
        
        .form-group{margin-bottom:20px}
        label{display:block;font-size:13px;font-weight:600;color:rgba(255,255,255,0.7);margin-bottom:8px}
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
        textarea{resize:vertical;min-height:120px}
        input[type="file"]{padding:10px}
        .current-image{max-width:200px;margin-top:10px;border-radius:12px}
        
        .btn-save{padding:12px 24px;background:linear-gradient(135deg,#fbbf24,#e0a800);border:none;border-radius:50px;color:#333;font-size:14px;font-weight:700;cursor:pointer;transition:all 0.2s}
        .btn-save:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(251,191,36,0.5)}
        .btn-annuler{display:inline-block;margin-left:10px;padding:12px 24px;background:rgba(108,117,125,0.2);border:1px solid rgba(108,117,125,0.3);border-radius:50px;color:#fff;text-decoration:none;font-size:14px;font-weight:600;transition:all 0.2s}
        .btn-annuler:hover{background:rgba(108,117,125,0.4);transform:translateY(-1px)}
        
        .error-global{background:rgba(239,68,68,0.12);color:#f87171;padding:12px;border-radius:12px;margin-bottom:1rem;display:none;border:1px solid rgba(239,68,68,0.2)}
        .error-message{color:#f87171;font-size:12px;margin-top:5px}
        
        small{color:rgba(255,255,255,0.35);font-size:12px}
    </style>
</head>
<body>

<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div>
</div>

<div class="form-container">
    <div class="form-card">
        <div class="form-title">✏️ Modifier l'article</div>
        <div class="form-sub">Modifiez le contenu de votre article</div>
        
        <div id="erreurGlobal" class="error-global">
            ⚠️ Veuillez corriger les erreurs dans le formulaire
        </div>
        
        <form id="editArticleForm" action="index.php?action=editArticle" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
            
            <div class="form-group">
                <label class="required">Titre</label>
                <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($article['titre']); ?>">
            </div>
            
            <div class="form-group">
                <label class="required">Catégorie</label>
                <select id="categorie" name="categorie">
                    <option value="Astuces" <?php echo $article['categorie'] == 'Astuces' ? 'selected' : ''; ?>>🥕 Astuces</option>
                    <option value="Recettes" <?php echo $article['categorie'] == 'Recettes' ? 'selected' : ''; ?>>🍲 Recettes</option>
                    <option value="Conseils" <?php echo $article['categorie'] == 'Conseils' ? 'selected' : ''; ?>>💡 Conseils</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Résumé</label>
                <textarea id="resume" name="resume" rows="2"><?php echo htmlspecialchars($article['resume']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="required">Contenu</label>
                <textarea id="contenu" name="contenu" rows="8"><?php echo htmlspecialchars($article['contenu']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Image actuelle</label>
                <?php if($article['image']): ?>
                    <img src="/FoodSave/public/uploads/<?php echo $article['image']; ?>" class="current-image">
                <?php else: ?>
                    <p style="color:rgba(255,255,255,0.5);">Aucune image</p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Nouvelle image (laisser vide pour garder l'actuelle)</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Formats acceptés : JPG, PNG, GIF, WEBP (max 2 Mo)</small>
            </div>
            
            <!-- ===== NOUVEAU : CHAMP VIDÉO YOUTUBE ===== -->
            <div class="form-group">
                <label>🎬 Vidéo YouTube actuelle</label>
                <?php if(!empty($article['video_url'])): ?>
                    <p style="color:rgba(255,255,255,0.7); margin-bottom:5px;"><?php echo htmlspecialchars($article['video_url']); ?></p>
                <?php else: ?>
                    <p style="color:rgba(255,255,255,0.5);">Aucune vidéo</p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Nouvelle vidéo YouTube (laisser vide pour garder l'actuelle)</label>
                <input type="text" id="video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=XXXXXXXXXXX" value="<?php echo htmlspecialchars($article['video_url'] ?? ''); ?>">
                <small>Formats acceptés : https://youtu.be/... ou https://www.youtube.com/watch?v=...</small>
            </div>
            
            <div class="form-group">
                <label>Statut</label>
                <select id="statut" name="statut">
                    <option value="publié" <?php echo $article['statut'] == 'publié' ? 'selected' : ''; ?>>📢 Publié</option>
                    <option value="brouillon" <?php echo $article['statut'] == 'brouillon' ? 'selected' : ''; ?>>📝 Brouillon</option>
                </select>
            </div>
            
            <button type="submit" class="btn-save">💾 Enregistrer les modifications</button>
            <a href="index.php?action=adminArticles" class="btn-annuler">Annuler</a>
        </form>
    </div>
</div>

<script src="/FoodSave/public/js/validationA.js"></script>
<script src="/FoodSave/public/js/modifier_article.js"></script>

</body>
</html>