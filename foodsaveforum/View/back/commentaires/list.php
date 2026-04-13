<div class="admin-commentaires-container">
    <div class="admin-header-section">
        <h2>💬 Gestion des Commentaires</h2>
        <div class="admin-stats">
            <div class="stat-box">
                <span class="stat-number"><?php echo count($commentaires); ?></span>
                <span class="stat-label">Commentaires Total</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><?php echo count(array_filter($commentaires, fn($c) => $c['statue'] === 'actif')); ?></span>
                <span class="stat-label">Actifs</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><?php echo count(array_filter($commentaires, fn($c) => $c['statue'] === 'banni')); ?></span>
                <span class="stat-label">Bannis</span>
            </div>
        </div>
    </div>

    <?php if (empty($commentaires)): ?>
        <div class="empty-state">
            <p>Aucun commentaire trouvé.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Contenu</th>
                    <th>Post ID</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commentaires as $commentaire): ?>
                    <tr class="<?php echo $commentaire['statue'] === 'banni' ? 'row-banned' : ''; ?>">
                        <td>#<?php echo $commentaire['id_commentaire']; ?></td>
                        <td><?php echo htmlspecialchars(substr($commentaire['contenu'], 0, 50)); ?>...</td>
                        <td>
                            <a href="admin.php?action=view-post&id=<?php echo $commentaire['id_post']; ?>">
                                #<?php echo $commentaire['id_post']; ?>
                            </a>
                        </td>
                        <td>User #<?php echo $commentaire['id_utilisateur']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($commentaire['date_publication'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $commentaire['statue']; ?>">
                                <?php echo $commentaire['statue']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="admin.php?action=view-commentaire&id=<?php echo $commentaire['id_commentaire']; ?>" class="btn-small btn-info">👁️ Voir</a>
                                
                                <?php if ($commentaire['statue'] === 'actif'): ?>
                                    <a href="admin.php?action=ban-commentaire&id=<?php echo $commentaire['id_commentaire']; ?>" class="btn-small btn-danger" onclick="return confirm('Bannir ce commentaire ?');">
                                        🚫 Bannir
                                    </a>
                                <?php else: ?>
                                    <a href="admin.php?action=unban-commentaire&id=<?php echo $commentaire['id_commentaire']; ?>" class="btn-small btn-success">
                                        ✅ Débannir
                                    </a>
                                <?php endif; ?>
                                
                                <a href="admin.php?action=delete-commentaire&id=<?php echo $commentaire['id_commentaire']; ?>" class="btn-small btn-danger" onclick="return confirm('Supprimer définitivement ?');">
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
