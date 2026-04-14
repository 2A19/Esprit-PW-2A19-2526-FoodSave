<section class="hero-banner">
    <div class="hero-inner">
        <div>
            <h1>Partagez vos meilleures idées contre le gaspillage</h1>
            <p>Publiez des recettes, astuces et questions pour inspirer la communauté FoodSave.</p>
        </div>
        <a href="index.php?action=create-post" class="btn btn-primary hero-cta">+ Créer un Post</a>
    </div>
</section>

<div class="posts-container">
    <div class="posts-header">
        <h2>Forum FoodSave 🌱</h2>
    </div>

    <div class="filters">
        <form method="GET" class="filter-form">
            <input type="hidden" name="action" value="posts">
            <select name="category" id="category">
                <option value="">-- Toutes les catégories --</option>
                <option value="Recettes">🍳 Recettes</option>
                <option value="Astuces">💡 Astuces</option>
                <option value="Questions">❓ Questions</option>
                <option value="Conseils">📋 Conseils</option>
                <option value="Autre">🔖 Autre</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
        </form>
    </div>

    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <p>Aucun post trouvé. Soyez le premier à créer un post! 🚀</p>
        </div>
    <?php else: ?>
        <div class="posts-list">
            <?php foreach ($posts as $post): ?>
                <div class="post-card entity-card">
                    <div class="post-header">
                        <h3>
                            <a href="index.php?action=view-post&id=<?php echo $post['id_post']; ?>">
                                <?php echo htmlspecialchars($post['titre']); ?>
                            </a>
                        </h3>
                        <span class="category-badge category-<?php echo strtolower($post['categorie']); ?>">
                            <?php echo htmlspecialchars($post['categorie']); ?>
                        </span>
                    </div>

                    <div class="post-meta">
                        <span class="author">👤 Utilisateur #<?php echo $post['id_utilisateur']; ?></span>
                        <span class="date">📅 <?php echo date('d/m/Y H:i', strtotime($post['date_creation'])); ?></span>
                    </div>

                    <div class="post-content">
                        <p><?php echo htmlspecialchars(substr($post['contenu'], 0, 200)) . '...'; ?></p>
                    </div>

                    <div class="post-actions">
                        <a href="index.php?action=view-post&id=<?php echo $post['id_post']; ?>" class="btn btn-info">
                            💬 Voir la discussion
                        </a>
                        <a href="index.php?action=edit-post&id=<?php echo $post['id_post']; ?>" class="btn btn-warning">
                            ✏️ Modifier
                        </a>
                        <a href="index.php?action=delete-post&id=<?php echo $post['id_post']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr ?');">
                            🗑️ Supprimer
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
