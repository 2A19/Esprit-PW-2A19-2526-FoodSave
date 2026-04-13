<div class="create-post-container">
    <h2>✍️ Créer un nouveau Post</h2>

    <form method="POST" action="index.php?action=store-post" class="form-post">
        <div class="form-group">
            <label for="titre">Titre du Post *</label>
            <input 
                type="text" 
                id="titre" 
                name="titre" 
                class="form-control" 
                placeholder="Ex: Comment conserver les légumes plus longtemps ?"
                required 
                maxlength="255"
                data-validate="required|maxlength:255"
            >
            <small>Maximum 255 caractères</small>
        </div>

        <div class="form-group">
            <label for="categorie">Catégorie *</label>
            <select id="categorie" name="categorie" class="form-control" required data-validate="required">
                <option value="">-- Sélectionnez une catégorie --</option>
                <option value="Recettes">🍳 Recettes</option>
                <option value="Astuces">💡 Astuces</option>
                <option value="Questions">❓ Questions</option>
                <option value="Conseils">📋 Conseils</option>
                <option value="Autre">🔖 Autre</option>
            </select>
        </div>

        <div class="form-group">
            <label for="contenu">Contenu du Post *</label>
            <textarea 
                id="contenu" 
                name="contenu" 
                class="form-control" 
                rows="8" 
                placeholder="Écrivez votre message ici..."
                required
                minlength="10"
                data-validate="required|minlength:10"
            ></textarea>
            <small>Minimum 10 caractères</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">📤 Publier le Post</button>
            <a href="index.php?action=posts" class="btn btn-secondary">Annuler</a>
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
