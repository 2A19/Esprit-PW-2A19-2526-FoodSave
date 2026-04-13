<div class="edit-post-container">
    <h2>✏️ Modifier le Post</h2>

    <form method="POST" action="index.php?action=update-post" class="form-post">
        <input type="hidden" name="id_post" value="<?php echo $post['id_post']; ?>">

        <div class="form-group">
            <label for="titre">Titre du Post *</label>
            <input 
                type="text" 
                id="titre" 
                name="titre" 
                class="form-control" 
                value="<?php echo htmlspecialchars($post['titre']); ?>"
                required 
                maxlength="255"
                data-validate="required|maxlength:255"
            >
            <small>Maximum 255 caractères</small>
        </div>

        <div class="form-group">
            <label for="categorie">Catégorie *</label>
            <select id="categorie" name="categorie" class="form-control" required data-validate="required">
                <option value="Recettes" <?php echo $post['categorie'] === 'Recettes' ? 'selected' : ''; ?>>🍳 Recettes</option>
                <option value="Astuces" <?php echo $post['categorie'] === 'Astuces' ? 'selected' : ''; ?>>💡 Astuces</option>
                <option value="Questions" <?php echo $post['categorie'] === 'Questions' ? 'selected' : ''; ?>>❓ Questions</option>
                <option value="Conseils" <?php echo $post['categorie'] === 'Conseils' ? 'selected' : ''; ?>>📋 Conseils</option>
                <option value="Autre" <?php echo $post['categorie'] === 'Autre' ? 'selected' : ''; ?>>🔖 Autre</option>
            </select>
        </div>

        <div class="form-group">
            <label for="contenu">Contenu du Post *</label>
            <textarea 
                id="contenu" 
                name="contenu" 
                class="form-control" 
                rows="8" 
                required
                minlength="10"
                data-validate="required|minlength:10"
            ><?php echo htmlspecialchars($post['contenu']); ?></textarea>
            <small>Minimum 10 caractères</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
            <a href="index.php?action=view-post&id=<?php echo $post['id_post']; ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
document.querySelector('.form-post').addEventListener('submit', function(e) {
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
