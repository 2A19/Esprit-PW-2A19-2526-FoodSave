<div class="post-view-container">
    <div class="post-detail">
        <div class="post-header">
            <h1><?php echo htmlspecialchars($data['post']['titre']); ?></h1>
            <span class="category-badge category-<?php echo strtolower($data['post']['categorie']); ?>">
                <?php echo htmlspecialchars($data['post']['categorie']); ?>
            </span>
        </div>

        <div class="post-meta">
            <span class="author">👤 Utilisateur #<?php echo $data['post']['id_utilisateur']; ?></span>
            <span class="date">📅 <?php echo date('d/m/Y à H:i', strtotime($data['post']['date_creation'])); ?></span>
        </div>

        <div class="post-content">
            <p><?php echo nl2br(htmlspecialchars($data['post']['contenu'])); ?></p>
        </div>

        <div class="post-actions">
            <a href="index.php?action=edit-post&id=<?php echo $data['post']['id_post']; ?>" class="btn btn-warning">
                ✏️ Modifier
            </a>
            <a href="index.php?action=delete-post&id=<?php echo $data['post']['id_post']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr ?');">
                🗑️ Supprimer
            </a>
            <a href="index.php?action=posts" class="btn btn-secondary">Retour au forum</a>
        </div>
    </div>

    <div class="comments-section">
        <h2>💬 Commentaires (<?php echo count($data['commentaires']); ?>)</h2>

        <?php if (empty($data['commentaires'])): ?>
            <p class="no-comments">Aucun commentaire pour le moment. Soyez le premier! 👇</p>
        <?php else: ?>
            <div class="comments-list">
                <?php foreach ($data['commentaires'] as $commentaire): ?>
                    <div class="comment-card entity-card">
                        <div class="comment-header">
                            <strong>Utilisateur #<?php echo $commentaire['id_utilisateur']; ?></strong>
                            <span class="comment-date">📅 <?php echo date('d/m/Y H:i', strtotime($commentaire['date_publication'])); ?></span>
                        </div>
                        <div class="comment-content">
                            <p><?php echo nl2br(htmlspecialchars($commentaire['contenu'])); ?></p>
                        </div>
                        <div class="comment-actions">
                            <a href="index.php?action=edit-comment&id=<?php echo $commentaire['id_commentaire']; ?>" class="btn-small btn-warning">
                                ✏️ Modifier
                            </a>
                            <a href="index.php?action=delete-comment&id=<?php echo $commentaire['id_commentaire']; ?>" class="btn-small btn-danger" onclick="return confirm('Êtes-vous sûr ?');">
                                🗑️ Supprimer
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="add-comment-section">
            <h3>Ajouter un commentaire</h3>
            <form method="POST" action="index.php?action=store-comment" class="form-comment">
                <input type="hidden" name="id_post" value="<?php echo $data['post']['id_post']; ?>">

                <div class="form-group">
                    <label for="contenu">Votre commentaire *</label>
                    <textarea 
                        id="contenu" 
                        name="contenu" 
                        class="form-control" 
                        rows="4" 
                        placeholder="Écrivez votre réponse..."
                        required
                        minlength="3"
                        data-validate="required|minlength:3"
                    ></textarea>
                    <small>Minimum 3 caractères</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">📤 Publier le commentaire</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelector('.form-comment').addEventListener('submit', function(e) {
    if (!validateForm(this)) {
        e.preventDefault();
    }
});

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('[data-validate]');
    
    inputs.forEach(input => {
        const rules = input.dataset.validate.split('|');
        
        for (let rule of rules) {
            if (rule === 'required' && !input.value.trim()) {
                showError(input, 'Ce champ est requis');
                isValid = false;
            }
            if (rule.startsWith('minlength:')) {
                const min = parseInt(rule.split(':')[1]);
                if (input.value.length < min) {
                    showError(input, `Minimum ${min} caractères`);
                    isValid = false;
                }
            }
            if (rule.startsWith('maxlength:')) {
                const max = parseInt(rule.split(':')[1]);
                if (input.value.length > max) {
                    showError(input, `Maximum ${max} caractères`);
                    isValid = false;
                }
            }
        }
    });
    
    return isValid;
}

function showError(element, message) {
    let errorDiv = element.nextElementSibling;
    if (!errorDiv || !errorDiv.classList.contains('error-message')) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        element.parentNode.insertBefore(errorDiv, element.nextSibling);
    }
    errorDiv.textContent = message;
}
</script>
