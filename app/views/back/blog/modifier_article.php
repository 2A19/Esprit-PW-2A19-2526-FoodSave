<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Modifier un article</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/FoodSave/public/assets/css/style.css">
    <style>
        .form-container { max-width: 800px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 20px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 10px; }
        button { background: #ffc107; color: #333; padding: 10px 20px; border: none; border-radius: 25px; cursor: pointer; }
        .current-image { max-width: 200px; margin-top: 10px; }
    </style>
</head>
<body>

<div class="form-container">
    <h1>✏️ Modifier l'article</h1>
    <form action="index.php?action=editArticle" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
        
        <div class="form-group">
            <label>Titre</label>
            <input type="text" name="titre" value="<?php echo htmlspecialchars($article['titre']); ?>" required>
        </div>
        <div class="form-group">
            <label>Catégorie</label>
            <select name="categorie">
                <option value="Astuces" <?php echo $article['categorie'] == 'Astuces' ? 'selected' : ''; ?>>🥕 Astuces</option>
                <option value="Recettes" <?php echo $article['categorie'] == 'Recettes' ? 'selected' : ''; ?>>🍲 Recettes</option>
                <option value="Conseils" <?php echo $article['categorie'] == 'Conseils' ? 'selected' : ''; ?>>💡 Conseils</option>
            </select>
        </div>
        <div class="form-group">
            <label>Résumé</label>
            <textarea name="resume" rows="2"><?php echo htmlspecialchars($article['resume']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Contenu</label>
            <textarea name="contenu" rows="8" required><?php echo htmlspecialchars($article['contenu']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Image actuelle</label>
            <?php if($article['image']): ?>
                <img src="/FoodSave/public/uploads/<?php echo $article['image']; ?>" class="current-image">
            <?php else: ?>
                <p>Aucune image</p>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label>Nouvelle image (laisser vide pour garder l'actuelle)</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <div class="form-group">
            <label>Statut</label>
            <select name="statut">
                <option value="publié" <?php echo $article['statut'] == 'publié' ? 'selected' : ''; ?>>Publié</option>
                <option value="brouillon" <?php echo $article['statut'] == 'brouillon' ? 'selected' : ''; ?>>Brouillon</option>
            </select>
        </div>
        <button type="submit">💾 Enregistrer les modifications</button>
        <a href="index.php?action=adminArticles">Annuler</a>
    </form>
</div>

</body>
</html>