<div class="content-header">
    <h1><i class="fas fa-newspaper"></i> Gestion des Sujets</h1>
</div>

<!-- Statistiques -->
<div class="stats-cards">
    <div class="stat-card">
        <div class="number"><?php echo count($posts); ?></div>
        <h4>Posts Total</h4>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo count(array_filter($posts, fn($p) => $p['statue'] === 'actif')); ?></div>
        <h4>Posts Actifs</h4>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo count(array_filter($posts, fn($p) => $p['statue'] === 'banni')); ?></div>
        <h4>Posts Bannis</h4>
    </div>
</div>

<div class="filters">
    <form method="GET" class="filter-form">
        <input type="hidden" name="action" value="posts">
        <select name="category" id="category">
            <option value="">-- Toutes les catégories --</option>
            <?php $categoryOptions = ['Recettes', 'Astuces', 'Questions', 'Conseils', 'Autre']; ?>
            <?php foreach ($categoryOptions as $category): ?>
                <option value="<?php echo $category; ?>" <?php echo (($selectedCategory ?? '') === $category) ? 'selected' : ''; ?>>
                    <?php echo $category; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filtrer</button>
    </form>
</div>

<?php if (empty($posts)): ?>
    <div class="empty-state">
        <p><i class="fas fa-inbox"></i> Aucun post trouvé.</p>
    </div>
<?php else: ?>
    <!-- Tableau des posts -->
    <div class="data-table">
        <h3><i class="fas fa-list-ul"></i> Liste des sujets</h3>
        <div class="table-responsive">
            <table>
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
                        <tr>
                            <td><strong>#<?php echo $post['id_post']; ?></strong></td>
                            <td><?php echo htmlspecialchars(substr($post['titre'], 0, 40)); ?></td>
                            <td><span class="badge-status"><?php echo htmlspecialchars($post['categorie']); ?></span></td>
                            <td>User #<?php echo $post['id_utilisateur']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($post['date_creation'])); ?></td>
                            <td>
                                <?php if ($post['statue'] === 'actif'): ?>
                                    <span class="badge-status">Actif</span>
                                <?php else: ?>
                                    <span class="badge-status danger">Banni</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="admin.php?action=view-post&id=<?php echo $post['id_post']; ?>&category=<?php echo urlencode($selectedCategory ?? ''); ?>" class="btn-view"><i class="fas fa-eye"></i> Voir</a>
                                
                                <?php if ($post['statue'] === 'actif'): ?>
                                    <a href="admin.php?action=ban-post&id=<?php echo $post['id_post']; ?>&category=<?php echo urlencode($selectedCategory ?? ''); ?>" class="btn-delete" onclick="return confirm('Bannir ce post ?');"><i class="fas fa-ban"></i> Bannir</a>
                                <?php else: ?>
                                    <a href="admin.php?action=unban-post&id=<?php echo $post['id_post']; ?>&category=<?php echo urlencode($selectedCategory ?? ''); ?>" class="btn-validate"><i class="fas fa-check"></i> Débannir</a>
                                <?php endif; ?>
                                
                                <a href="admin.php?action=delete-post&id=<?php echo $post['id_post']; ?>&category=<?php echo urlencode($selectedCategory ?? ''); ?>" class="btn-delete" onclick="return confirm('Supprimer définitivement ce sujet ?');"><i class="fas fa-trash-alt"></i> Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
