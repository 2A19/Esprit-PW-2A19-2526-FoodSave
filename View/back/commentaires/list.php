<div class="content-header">
    <h1><i class="fas fa-comments"></i> Gestion des Messages</h1>
</div>

<!-- Statistiques -->
<div class="stats-cards">
    <div class="stat-card">
        <div class="number"><?php echo count($commentaires); ?></div>
        <h4>Messages Total</h4>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo count(array_filter($commentaires, fn($c) => $c['statue'] === 'actif')); ?></div>
        <h4>Actifs</h4>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo count(array_filter($commentaires, fn($c) => $c['statue'] === 'banni')); ?></div>
        <h4>Bannis</h4>
    </div>
</div>

<?php if (empty($commentaires)): ?>
    <div class="empty-state">
        <p><i class="fas fa-inbox"></i> Aucun commentaire trouvé.</p>
    </div>
<?php else: ?>
    <!-- Tableau des commentaires -->
    <div class="data-table">
        <h3><i class="fas fa-list-ul"></i> Liste des messages</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Contenu</th>
                        <th>Post</th>
                        <th>Auteur</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commentaires as $commentaire): ?>
                        <tr>
                            <td><strong>#<?php echo $commentaire['id_commentaire']; ?></strong></td>
                            <td><?php echo htmlspecialchars(substr($commentaire['contenu'], 0, 50)); ?>...</td>
                            <td>
                                <a href="admin.php?action=view-post&id=<?php echo $commentaire['id_post']; ?>" class="btn-view" style="padding: 2px 8px; font-size: 0.7rem;">
                                    #<?php echo $commentaire['id_post']; ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($commentaire['auteur_nom'] ?: ($commentaire['auteur_email'] ?? 'Utilisateur')); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($commentaire['date_publication'])); ?></td>
                            <td>
                                <?php if ($commentaire['statue'] === 'actif'): ?>
                                    <span class="badge-status">Actif</span>
                                <?php else: ?>
                                    <span class="badge-status danger">Banni</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="admin.php?action=view-commentaire&id=<?php echo $commentaire['id_commentaire']; ?>" class="btn-view"><i class="fas fa-eye"></i> Voir</a>
                                
                                <?php if ($commentaire['statue'] === 'actif'): ?>
                                    <a href="admin.php?action=ban-commentaire&id=<?php echo $commentaire['id_commentaire']; ?>" class="btn-delete" onclick="return confirm('Bannir ce commentaire ?');"><i class="fas fa-ban"></i> Bannir</a>
                                <?php else: ?>
                                    <a href="admin.php?action=unban-commentaire&id=<?php echo $commentaire['id_commentaire']; ?>" class="btn-validate"><i class="fas fa-check"></i> Débannir</a>
                                <?php endif; ?>
                                
                                <a href="admin.php?action=delete-commentaire&id=<?php echo $commentaire['id_commentaire']; ?>" class="btn-delete" onclick="return confirm('Supprimer définitivement ce message ?');"><i class="fas fa-trash-alt"></i> Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
