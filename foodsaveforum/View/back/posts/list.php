<div class="admin-posts-container">
    <div class="admin-header-section">
        <h2>📋 Gestion des Posts</h2>
        <div class="admin-stats">
            <div class="stat-box">
                <span class="stat-number"><?php echo count($posts); ?></span>
                <span class="stat-label">Posts Total</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><?php echo count(array_filter($posts, fn($p) => $p['statue'] === 'actif')); ?></span>
                <span class="stat-label">Posts Actifs</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><?php echo count(array_filter($posts, fn($p) => $p['statue'] === 'banni')); ?></span>
                <span class="stat-label">Posts Bannis</span>
            </div>
        </div>
    </div>

    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <p>Aucun post trouvé.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr class="<?php echo $post['statue'] === 'banni' ? 'row-banned' : ''; ?>">
                        <td>#<?php echo $post['id_post']; ?></td>
                        <td><?php echo htmlspecialchars(substr($post['titre'], 0, 30)); ?></td>
                        <td><span class="category-badge"><?php echo $post['categorie']; ?></span></td>
                        <td>User #<?php echo $post['id_utilisateur']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($post['date_creation'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $post['statue']; ?>">
                                <?php echo $post['statue']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="admin.php?action=view-post&id=<?php echo $post['id_post']; ?>" class="btn-small btn-info">👁️ Voir</a>
                                
                                <?php if ($post['statue'] === 'actif'): ?>
                                    <a href="admin.php?action=ban-post&id=<?php echo $post['id_post']; ?>" class="btn-small btn-danger" onclick="return confirm('Bannir ce post ?');">
                                        🚫 Bannir
                                    </a>
                                <?php else: ?>
                                    <a href="admin.php?action=unban-post&id=<?php echo $post['id_post']; ?>" class="btn-small btn-success">
                                        ✅ Débannir
                                    </a>
                                <?php endif; ?>
                                
                                <a href="admin.php?action=delete-post&id=<?php echo $post['id_post']; ?>" class="btn-small btn-danger" onclick="return confirm('Supprimer définitivement ?');">
                                    🗑️ Supprimer
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
