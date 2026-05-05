<div class="create-post-container">
    <h2>✍️ Créer un nouveau Post</h2>

    <form method="POST" action="index.php?action=store-post" class="form-post" enctype="multipart/form-data">
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
            <label for="contenu">Contenu du Post</label>
            <textarea 
                id="contenu" 
                name="contenu" 
                class="form-control" 
                rows="8" 
                placeholder="Écrivez votre message ici..."
                minlength="10"
            ></textarea>
            <small>Optionnel si vous ajoutez un message vocal.</small>
        </div>

        <div class="form-group">
            <label>🎙️ Message vocal</label>
            <div class="voice-recorder" id="voice-recorder-post">
                <div class="recorder-idle">
                    <button type="button" class="btn-record" data-recorder="voice-recorder-post" title="Démarrer l'enregistrement">
                        <span class="mic-icon">🎤</span>
                        <span class="btn-record-label">Enregistrer un message vocal</span>
                    </button>
                </div>
                <div class="recorder-active" style="display:none;">
                    <div class="recording-indicator">
                        <span class="rec-dot"></span>
                        <span class="rec-timer">0:00</span>
                        <span class="rec-label">Enregistrement en cours…</span>
                    </div>
                    <div class="recorder-waveform"><canvas class="waveform-canvas" width="260" height="40"></canvas></div>
                    <button type="button" class="btn-stop-record" data-recorder="voice-recorder-post">⏹ Arrêter</button>
                </div>
                <div class="recorder-preview" style="display:none;">
                    <audio class="recorder-audio-preview" controls></audio>
                    <div class="recorder-preview-actions">
                        <button type="button" class="btn-discard-record" data-recorder="voice-recorder-post">🗑 Recommencer</button>
                    </div>
                </div>
                <!-- Hidden file input populated by JS -->
                <input type="file" id="audio_message" name="audio_message" class="recorder-hidden-input" accept="audio/*" style="display:none;">
            </div>
            <small>Enregistrez directement depuis votre navigateur (max 10MB).</small>
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
