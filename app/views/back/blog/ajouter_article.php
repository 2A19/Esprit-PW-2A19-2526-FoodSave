<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Ajouter un article</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/FoodSave/public/assets/css/style.css">
    <style>
        .form-container { max-width: 800px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .required:after { content: " *"; color: #dc3545; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 10px; font-family: 'Poppins', sans-serif; transition: all 0.3s ease; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #4caf50; box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1); }
        button { background: #4caf50; color: white; padding: 10px 20px; border: none; border-radius: 25px; cursor: pointer; font-weight: 600; transition: all 0.3s ease; }
        button:hover { background: #2e7d32; transform: translateY(-2px); }
        .btn-annuler { background: #6c757d; margin-left: 10px; text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 25px; color: white; }
        .btn-annuler:hover { background: #5a6268; transform: translateY(-2px); }
        .error-message { color: #dc3545; font-size: 0.8rem; margin-top: 5px; }
        .error-global { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 10px; margin-bottom: 1rem; display: none; }
    </style>
</head>
<body>

<div class="form-container">
    <h1>➕ Ajouter un article</h1>
    
    <div id="erreurGlobal" class="error-global">
        ⚠️ Veuillez corriger les erreurs dans le formulaire
    </div>
    
    <form id="articleForm" action="index.php?action=addArticle" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="required">Titre</label>
            <input type="text" id="titre" name="titre">
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
            <textarea id="resume" name="resume" rows="2"></textarea>
        </div>
        
        <div class="form-group">
            <label class="required">Contenu</label>
            <textarea id="contenu" name="contenu" rows="8"></textarea>
        </div>
        
        <div class="form-group">
            <label>Image</label>
            <input type="file" id="image" name="image" accept="image/*">
            <small style="color: #666;">Formats acceptés : JPG, PNG, GIF, WEBP (max 2 Mo)</small>
        </div>
        
        <div class="form-group">
            <label>Statut</label>
            <select id="statut" name="statut">
                <option value="publié">📢 Publié</option>
                <option value="brouillon">📝 Brouillon</option>
            </select>
        </div>
        
        <button type="submit">📤 Publier l'article</button>
        <a href="index.php?action=adminArticles" class="btn-annuler">Annuler</a>
    </form>
</div>

<!-- Inclusion des fichiers JavaScript -->
<script src="/FoodSave/public/js/validationA.js"></script>
<script src="/FoodSave/public/js/ajouter_article.js"></script>

</body>
</html>