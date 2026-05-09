<div class="content-header">
    <h1><i class="fas fa-file-alt"></i> Détail du Sujet #<?php echo $post['id_post']; ?></h1>
</div>

<div class="data-table">
    <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem;"><i class="fas fa-heading"></i> <?php echo htmlspecialchars($post['titre']); ?></h3>
    
    <div style="padding: 1rem 0; border-bottom: 1px solid #f0f0f0;">
        <p><strong>Catégorie:</strong> <span class="badge-status"><?php echo htmlspecialchars($post['categorie']); ?></span></p>
        <p><strong>Auteur:</strong> <?php echo htmlspecialchars($post['auteur_nom'] ?: ($post['auteur_email'] ?? 'Utilisateur')); ?></p>
        <p><strong>Date de création:</strong> <?php echo date('d/m/Y H:i', strtotime($post['date_creation'])); ?></p>
        <p><strong>Statut:</strong> 
            <?php if ($post['statue'] === 'actif'): ?>
                <span class="badge-status">Actif</span>
            <?php else: ?>
                <span class="badge-status danger">Banni</span>
            <?php endif; ?>
        </p>
    </div>

    <div style="padding: 1.5rem 0;">
        <h4 style="margin-bottom: 1rem;"><i class="fas fa-quote-left"></i> Contenu:</h4>
        <p style="line-height: 1.6; color: #555;"><?php echo nl2br(htmlspecialchars($post['contenu'])); ?></p>
    </div>
</div>

<div style="display: flex; gap: 1rem; margin-top: 2rem;">
    <?php if ($post['statue'] === 'actif'): ?>
        <a href="admin.php?action=ban-post&id=<?php echo $post['id_post']; ?>" class="btn-delete" onclick="return confirm('Bannir ce sujet ?');"><i class="fas fa-ban"></i> Bannir ce Sujet</a>
    <?php else: ?>
        <a href="admin.php?action=unban-post&id=<?php echo $post['id_post']; ?>" class="btn-validate"><i class="fas fa-check"></i> Débannir ce Sujet</a>
    <?php endif; ?>

    <a href="admin.php?action=delete-post&id=<?php echo $post['id_post']; ?>" class="btn-delete" onclick="return confirm('Supprimer définitivement ce sujet ? Cette action ne peut pas être annulée.');"><i class="fas fa-trash-alt"></i> Supprimer Définitivement</a>

    <a href="admin.php?action=posts" style="padding: 8px 20px; border-radius: 50px; border: 1px solid #4caf50; color: #4caf50; text-decoration: none; background: transparent; transition: all 0.3s ease;" onmouseover="this.style.background='#4caf50'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#4caf50';"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
</div>
