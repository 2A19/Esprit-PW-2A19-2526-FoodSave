<div class="admin-commentaire-detail">
    <h2>Détail du Commentaire</h2>

    <div class="comment-detail-box">
        <div class="detail-meta">
            <p><strong>ID Commentaire:</strong> #<?php echo $_GET['id'] ?? 'N/A'; ?></p>
            <p><strong>ID Post:</strong> <a href="admin.php?action=view-post&id=<?php echo $commentaire['id_post'] ?? ''; ?>">#<?php echo $commentaire['id_post'] ?? 'N/A'; ?></a></p>
            <p><strong>Auteur:</strong> Utilisateur #<?php echo $commentaire['id_utilisateur'] ?? 'N/A'; ?></p>
            <p><strong>Date:</strong> <?php echo isset($commentaire['date_publication']) ? date('d/m/Y H:i', strtotime($commentaire['date_publication'])) : 'N/A'; ?></p>
            <p><strong>Statut:</strong> <span class="status-badge status-<?php echo $commentaire['statue'] ?? 'actif'; ?>"><?php echo $commentaire['statue'] ?? 'actif'; ?></span></p>
        </div>

        <div class="detail-content">
            <h4>Contenu du commentaire:</h4>
            <p><?php echo nl2br(htmlspecialchars($commentaire['contenu'] ?? '')); ?></p>
        </div>
    </div>

    <div class="admin-actions">
        <?php if (($commentaire['statue'] ?? 'actif') === 'actif'): ?>
            <a href="admin.php?action=ban-commentaire&id=<?php echo $_GET['id'] ?? ''; ?>" class="btn btn-danger" onclick="return confirm('Bannir ce commentaire ?');">
                🚫 Bannir ce Commentaire
            </a>
        <?php else: ?>
            <a href="admin.php?action=unban-commentaire&id=<?php echo $_GET['id'] ?? ''; ?>" class="btn btn-success">
                ✅ Débannir ce Commentaire
            </a>
        <?php endif; ?>

        <a href="admin.php?action=delete-commentaire&id=<?php echo $_GET['id'] ?? ''; ?>" class="btn btn-danger" onclick="return confirm('Supprimer définitivement ce commentaire ?');">
            🗑️ Supprimer Définitivement
        </a>

        <a href="admin.php?action=commentaires" class="btn btn-secondary">Retour à la liste</a>
    </div>
</div>

<style>
.comment-detail-box {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid var(--color-primary);
}
</style>
