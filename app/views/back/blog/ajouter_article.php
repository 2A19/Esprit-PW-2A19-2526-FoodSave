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
        .form-container { max-width: 800px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 20px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 10px; }
        button { background: #4caf50; color: white; padding: 10px 20px; border: none; border-radius: 25px; cursor: pointer; }
    </style>
</head>
<body>

<div class="form-container">
    <h1>➕ Ajouter un article</h1>
    <form action="index.php?action=addArticle" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Titre</label>
            <input type="text" name="titre" required>
        </div>
        <div class="form-group">
            <label>Catégorie</label>
            <select name="categorie">
                <option value="Astuces">🥕 Astuces</option>
                <option value="Recettes">🍲 Recettes</option>
                <option value="Conseils">💡 Conseils</option>
            </select>
        </div>
        <div class="form-group">
            <label>Résumé</label>
            <textarea name="resume" rows="2"></textarea>
        </div>
        <div class="form-group">
            <label>Contenu</label>
            <textarea name="contenu" rows="8" required></textarea>
        </div>
        <div class="form-group">
            <label>Image</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <div class="form-group">
            <label>Statut</label>
            <select name="statut">
                <option value="publié">Publié</option>
                <option value="brouillon">Brouillon</option>
            </select>
        </div>
        <button type="submit">📤 Publier l'article</button>
        <a href="index.php?action=adminArticles">Annuler</a>
    </form>
</div>

</body>
</html>