<?php require_once __DIR__ . '/../../helpers/media_embed.php'; ?>

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
            <div class="rich-content"><?php echo renderContentWithEmbeds($data['post']['contenu']); ?></div>
            <?php if (!empty($data['post']['audio_path'])): ?>
                <div class="audio-player-wrap">
                    <audio controls preload="none">
                        <source src="<?php echo htmlspecialchars($data['post']['audio_path']); ?>">
                        Votre navigateur ne supporte pas le lecteur audio.
                    </audio>
                </div>
            <?php endif; ?>
        </div>

        <div class="post-reactions">
            <div class="reactions-group">
                <button class="btn-reaction btn-like <?php echo ($data['post']['user_reaction'] ?? null) === 'like' ? 'active' : ''; ?>" 
                        data-post-id="<?php echo $data['post']['id_post']; ?>" 
                        data-type="like"
                        title="J'aime">
                    👍 <span class="reaction-count"><?php echo $data['post']['likes_stats']['likes']; ?></span>
                </button>
                <button class="btn-reaction btn-dislike <?php echo ($data['post']['user_reaction'] ?? null) === 'dislike' ? 'active' : ''; ?>" 
                        data-post-id="<?php echo $data['post']['id_post']; ?>" 
                        data-type="dislike"
                        title="Je n'aime pas">
                    👎 <span class="reaction-count"><?php echo $data['post']['likes_stats']['dislikes']; ?></span>
                </button>
            </div>
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
                            <div class="rich-content"><?php echo renderContentWithEmbeds($commentaire['contenu']); ?></div>
                            <?php if (!empty($commentaire['audio_path'])): ?>
                                <div class="audio-player-wrap">
                                    <audio controls preload="none">
                                        <source src="<?php echo htmlspecialchars($commentaire['audio_path']); ?>">
                                        Votre navigateur ne supporte pas le lecteur audio.
                                    </audio>
                                </div>
                            <?php endif; ?>
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
            <form method="POST" action="index.php?action=store-comment" class="form-comment" enctype="multipart/form-data">
                <input type="hidden" name="id_post" value="<?php echo $data['post']['id_post']; ?>">

                <div class="form-group">
                    <label for="contenu">Votre commentaire</label>
                    <textarea 
                        id="contenu" 
                        name="contenu" 
                        class="form-control" 
                        rows="4" 
                        placeholder="Écrivez votre réponse..."
                        minlength="3"
                    ></textarea>
                    <small>Optionnel si vous ajoutez un message vocal.</small>
                </div>

                <div class="form-group">
                    <label>🎙️ Message vocal</label>
                    <div class="voice-recorder" id="voice-recorder-comment">
                        <div class="recorder-idle">
                            <button type="button" class="btn-record" data-recorder="voice-recorder-comment" title="Démarrer l'enregistrement">
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
                            <button type="button" class="btn-stop-record" data-recorder="voice-recorder-comment">⏹ Arrêter</button>
                        </div>
                        <div class="recorder-preview" style="display:none;">
                            <audio class="recorder-audio-preview" controls></audio>
                            <div class="recorder-preview-actions">
                                <button type="button" class="btn-discard-record" data-recorder="voice-recorder-comment">🗑 Recommencer</button>
                            </div>
                        </div>
                        <input type="file" id="audio_message" name="audio_message" class="recorder-hidden-input" accept="audio/*" style="display:none;">
                    </div>
                    <small>Enregistrez directement depuis votre navigateur (max 10MB).</small>
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
