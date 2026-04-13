<div class="edit-comment-container">
    <h2>✏️ Modifier un commentaire</h2>

    <form method="POST" action="index.php?action=update-comment" class="form-comment">
        <input type="hidden" name="id_commentaire" value="<?php echo $commentaire['id_commentaire']; ?>">
        <input type="hidden" name="id_post" value="<?php echo $commentaire['id_post']; ?>">

        <div class="form-group">
            <label for="contenu">Commentaire *</label>
            <textarea 
                id="contenu" 
                name="contenu" 
                class="form-control" 
                rows="6" 
                required
                minlength="3"
                data-validate="required|minlength:3"
            ><?php echo htmlspecialchars($commentaire['contenu']); ?></textarea>
            <small>Minimum 3 caractères</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
            <a href="index.php?action=view-post&id=<?php echo $commentaire['id_post']; ?>" class="btn btn-secondary">Annuler</a>
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
