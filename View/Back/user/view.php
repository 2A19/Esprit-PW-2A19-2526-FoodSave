<div class="admin-post-detail">
    <h2>Détail du Post #<?php echo $post['id_post']; ?></h2>

    <div class="post-detail-box">
        <h3><?php echo htmlspecialchars($post['titre']); ?></h3>
        
        <div class="detail-meta">
            <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($post['categorie']); ?></p>
            <p><strong>Auteur:</strong> Utilisateur #<?php echo $post['id_utilisateur']; ?></p>
            <p><strong>Date de création:</strong> <?php echo date('d/m/Y H:i', strtotime($post['date_creation'])); ?></p>
            <p><strong>Statut:</strong> <span class="status-badge status-<?php echo $post['statue']; ?>"><?php echo $post['statue']; ?></span></p>
        </div>

        <div class="detail-content">
            <h4>Contenu:</h4>
            <p><?php echo nl2br(htmlspecialchars($post['contenu'])); ?></p>
        </div>
    </div>

    <div class="admin-actions">
        <?php if ($post['statue'] === 'actif'): ?>
            <a href="admin.php?action=ban-post&id=<?php echo $post['id_post']; ?>" class="btn btn-danger" onclick="return confirm('Bannir ce post ?');">
                🚫 Bannir ce Post
            </a>
        <?php else: ?>
            <a href="admin.php?action=unban-post&id=<?php echo $post['id_post']; ?>" class="btn btn-success">
                ✅ Débannir ce Post
            </a>
        <?php endif; ?>

        <a href="admin.php?action=delete-post&id=<?php echo $post['id_post']; ?>" class="btn btn-danger" onclick="return confirm('Supprimer définitivement ce post ? Cette action ne peut pas être annulée.');">
            🗑️ Supprimer Définitivement
        </a>

        <a href="admin.php?action=posts" class="btn btn-secondary">Retour à la liste</a>
    </div>
</div>
