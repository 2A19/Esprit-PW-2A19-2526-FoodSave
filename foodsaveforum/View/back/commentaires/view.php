<div class="content-header">
    <h1><i class="fas fa-comment"></i> Détail du Message #<?php echo $_GET['id'] ?? 'N/A'; ?></h1>
</div>

<div class="data-table">
    <div style="padding: 1rem 0; border-bottom: 1px solid #f0f0f0;">
        <p><strong>Post associé:</strong> <a href="admin.php?action=view-post&id=<?php echo $commentaire['id_post'] ?? ''; ?>" class="btn-view" style="padding: 2px 8px; font-size: 0.7rem;">#<?php echo $commentaire['id_post'] ?? 'N/A'; ?></a></p>
        <p><strong>Auteur:</strong> Utilisateur #<?php echo $commentaire['id_utilisateur'] ?? 'N/A'; ?></p>
        <p><strong>Date:</strong> <?php echo isset($commentaire['date_publication']) ? date('d/m/Y H:i', strtotime($commentaire['date_publication'])) : 'N/A'; ?></p>
        <p><strong>Statut:</strong> 
            <?php if (($commentaire['statue'] ?? 'actif') === 'actif'): ?>
                <span class="badge-status">Actif</span>
            <?php else: ?>
                <span class="badge-status danger">Banni</span>
            <?php endif; ?>
        </p>
    </div>

    <div style="padding: 1.5rem 0;">
        <h4 style="margin-bottom: 1rem;"><i class="fas fa-quote-left"></i> Contenu du message:</h4>
        <p style="line-height: 1.6; color: #555;"><?php echo nl2br(htmlspecialchars($commentaire['contenu'] ?? '')); ?></p>
    </div>
</div>

<div style="display: flex; gap: 1rem; margin-top: 2rem;">
    <?php if (($commentaire['statue'] ?? 'actif') === 'actif'): ?>
        <a href="admin.php?action=ban-commentaire&id=<?php echo $_GET['id'] ?? ''; ?>" class="btn-delete" onclick="return confirm('Bannir ce message ?');"><i class="fas fa-ban"></i> Bannir ce Message</a>
    <?php else: ?>
        <a href="admin.php?action=unban-commentaire&id=<?php echo $_GET['id'] ?? ''; ?>" class="btn-validate"><i class="fas fa-check"></i> Débannir ce Message</a>
    <?php endif; ?>

    <a href="admin.php?action=delete-commentaire&id=<?php echo $_GET['id'] ?? ''; ?>" class="btn-delete" onclick="return confirm('Supprimer définitivement ce message ?');"><i class="fas fa-trash-alt"></i> Supprimer Définitivement</a>

    <a href="admin.php?action=commentaires" style="padding: 8px 20px; border-radius: 50px; border: 1px solid #4caf50; color: #4caf50; text-decoration: none; background: transparent; transition: all 0.3s ease;" onmouseover="this.style.background='#4caf50'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#4caf50';"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
</div>

