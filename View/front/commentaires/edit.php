<div class="edit-comment-container">
    <h2>✏️ Modifier un commentaire</h2>

    <form method="POST" action="index.php?action=update-comment" class="form-comment" enctype="multipart/form-data">
        <input type="hidden" name="id_commentaire" value="<?php echo $commentaire['id_commentaire']; ?>">
        <input type="hidden" name="id_post" value="<?php echo $commentaire['id_post']; ?>">

        <div class="form-group">
            <label for="contenu">Commentaire</label>
            <textarea 
                id="contenu" 
                name="contenu" 
                class="form-control" 
                rows="6" 
                minlength="3"
            ><?php echo htmlspecialchars($commentaire['contenu']); ?></textarea>
            <small>Optionnel si vous ajoutez un message vocal.</small>
        </div>

        <div class="form-group">
            <label for="audio_message">Message vocal (audio)</label>
            <?php if (!empty($commentaire['audio_path'])): ?>
                <div class="audio-player-wrap">
                    <audio controls preload="none">
                        <source src="<?php echo htmlspecialchars($commentaire['audio_path']); ?>">
                        Votre navigateur ne supporte pas le lecteur audio.
                    </audio>
                </div>
            <?php endif; ?>
            <input
                type="file"
                id="audio_message"
                name="audio_message"
                class="form-control"
                accept="audio/*"
                capture="user"
            >
            <small>Laisser vide pour conserver l'audio actuel.</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
            <a href="index.php?action=view&id=<?php echo $commentaire['id_post']; ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
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
