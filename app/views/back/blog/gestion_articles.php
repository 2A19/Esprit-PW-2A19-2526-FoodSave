<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Admin : Gestion des articles</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/FoodSave/public/assets/css/style.css">
    <style>
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 250px;
            background: #EDE8D0;
            border-right: 2px solid #d4c9a8;
            padding: 1rem;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
        }
        .sidebar a.active { background: #4caf50; color: white; border-radius: 10px; }
        .main-content { flex: 1; padding: 2rem; }
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-card .number { font-size: 2rem; font-weight: bold; color: #4caf50; }
        .btn-add {
            background: #4caf50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-collapse: collapse;
        }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
        .btn-edit, .btn-delete {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.8rem;
        }
        .btn-edit { background: #ffc107; color: #333; }
        .btn-delete { background: #dc3545; color: white; }
        .badge {
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .badge.publie { background: #4caf50; color: white; }
        .badge.brouillon { background: #ff6b35; color: white; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 10px; margin-bottom: 1rem; }
    </style>
</head>
<body>

<div class="admin-container">
    <div class="sidebar">
        <h3>FoodSave Admin</h3>
        <a href="index.php?action=adminArticles" class="active">📝 Articles</a>
        <a href="#">👥 Utilisateurs</a>
        <a href="#">⭐ Avis</a>
    </div>
    
    <div class="main-content">
        <h1>Gestion des articles</h1>
        
        <?php if(isset($_GET['success'])): ?>
            <?php if($_GET['success'] == 'created'): ?>
                <div class="success">✅ Article créé avec succès !</div>
            <?php elseif($_GET['success'] == 'updated'): ?>
                <div class="success">✅ Article modifié avec succès !</div>
            <?php elseif($_GET['success'] == 'deleted'): ?>
                <div class="success">✅ Article supprimé avec succès !</div>
            <?php endif; ?>
        <?php endif; ?>
        
        <a href="index.php?action=addArticleForm" class="btn-add">+ Nouvel article</a>
        
        <div class="stats-cards">
            <div class="stat-card"><div class="number"><?php echo $totalArticles; ?></div>Total articles</div>
            <div class="stat-card"><div class="number"><?php echo $totalPublished; ?></div>Publiés</div>
            <div class="stat-card"><div class="number"><?php echo $totalDrafts; ?></div>Brouillons</div>
            <div class="stat-card"><div class="number"><?php echo $totalViews; ?></div>Vues totales</div>
        </div>
        
        <table>
            <thead>
                <tr><th>ID</th><th>Titre</th><th>Catégorie</th><th>Statut</th><th>Date</th><th>Vues</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach($articles as $article): ?>
                <tr>
                    <td><?php echo $article['id']; ?></td>
                    <td><?php echo htmlspecialchars($article['titre']); ?></td>
                    <td><?php echo $article['categorie']; ?></td>
                    <td><span class="badge <?php echo $article['statut'] == 'publié' ? 'publie' : 'brouillon'; ?>"><?php echo $article['statut']; ?></span></td>
                    <td><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></td>
                    <td><?php echo $article['vue']; ?></td>
                    <td>
                        <a href="index.php?action=editArticleForm&id=<?php echo $article['id']; ?>" class="btn-edit">Modifier</a>
                        <a href="index.php?action=deleteArticle&id=<?php echo $article['id']; ?>" class="btn-delete" onclick="return confirm('Supprimer cet article ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>