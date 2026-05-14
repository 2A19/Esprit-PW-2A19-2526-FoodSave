<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Ajouter un article</title>
    
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
        .form-title{font-size:24px;font-weight:700;color:#4ade80;margin-bottom:6px}
        .form-sub{font-size:13px;color:rgba(255,255,255,0.45);margin-bottom:24px}
        
        .form-group{margin-bottom:20px}
        label{display:block;font-size:13px;font-weight:600;color:rgba(255,255,255,0.7);margin-bottom:8px}
        .required:after{content:" *";color:#f87171}
        
        /* ===== CORRECTION DES CHAMPS POUR MEILLEURE LISIBILITÉ ===== */
        input, textarea, select {
            width:100%;
            padding:12px 15px;
            background-color: #1a2a1a !important;
            border:1px solid #4ade80 !important;
            border-radius:12px;
            color:#ffffff !important;
            font-size:14px;
            font-family:inherit;
            transition:all 0.2s
        }
        
        input::placeholder, textarea::placeholder {
            color: #8ba888 !important;
            opacity: 1;
        }
        
        input:focus, textarea:focus, select:focus {
            outline:none;
            background-color: #0d1f14 !important;
            border-color: #fbbf24 !important;
            box-shadow:0 0 0 3px rgba(74,222,128,0.2)
        }
        
        select option {
            background-color: #1a2a1a !important;
            color: #ffffff !important;
        }
        
        textarea {
            resize:vertical;
            min-height:120px;
        }
        
        input[type="file"] {
            background-color: transparent !important;
            border: 1px dashed #4ade80 !important;
            color: #ffffff !important;
        }
        
        input[type="file"]::file-selector-button {
            background-color: #16a34a !important;
            color: white !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 8px 16px !important;
            cursor: pointer !important;
        }
        
        .btn-submit{padding:12px 24px;background:linear-gradient(135deg,#16a34a,#15803d);border:none;border-radius:50px;color:#fff;font-size:14px;font-weight:700;cursor:pointer;transition:all 0.2s}
        .btn-submit:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(22,163,74,0.5)}
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
        <div class="form-title">➕ Ajouter un article</div>
        <div class="form-sub">Créez un nouvel article pour le blog</div>
        
        <div id="erreurGlobal" class="error-global">
            ⚠️ Veuillez corriger les erreurs dans le formulaire
        </div>
        
        <form id="articleForm" action="admin.php?action=addArticle" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="required">Titre</label>
                <input type="text" id="titre" name="titre" placeholder="Titre de l'article">
            </div>
            
            <div class="form-group">
                <label class="required">Catégorie</label>
                <select id="categorie" name="categorie">
                    <option value="">-- Sélectionner --</option>
                    <option value="Astuces">🥕 Astuces</option>
                    <option value="Recettes">🍲 Recettes</option>
                    <option value="Conseils">💡 Conseils</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Résumé</label>
                <textarea id="resume" name="resume" rows="2" placeholder="Court résumé de l'article..."></textarea>
            </div>
            
            <div class="form-group">
                <label class="required">Contenu</label>
                <textarea id="contenu" name="contenu" rows="8" placeholder="Contenu complet de l'article..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Formats acceptés : JPG, PNG, GIF, WEBP (max 2 Mo)</small>
            </div>
            
            <div class="form-group">
                <label>🎬 Vidéo YouTube</label>
                <input type="text" id="video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=XXXXXXXXXXX">
                <small>Ajoutez une vidéo YouTube (optionnel). Formats acceptés : https://youtu.be/... ou https://www.youtube.com/watch?v=...</small>
            </div>
            
            <div class="form-group">
                <label>Statut</label>
                <select id="statut" name="statut">
                    <option value="publié">📢 Publié</option>
                    <option value="brouillon">📝 Brouillon</option>
                </select>
            </div>
            
            <button type="submit" class="btn-submit">📤 Publier l'article</button>
            <a href="admin.php?action=adminArticles" class="btn-annuler">Annuler</a>
        </form>
    </div>
</div>

<script src="/public/js/validationA.js"></script>
<script src="/public/js/ajouter_article.js"></script>

</body>
</html>