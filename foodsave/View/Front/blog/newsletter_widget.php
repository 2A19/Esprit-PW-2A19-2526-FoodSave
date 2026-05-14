<!-- Widget Newsletter pour Front Office -->
<div class="newsletter-widget" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(74,222,128,0.15); border-radius: 20px; padding: 1.5rem; margin: 2rem 0; text-align: center;">
    <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 0.5rem;">
        <i class="fas fa-envelope" style="color: #4ade80; font-size: 1.8rem;"></i>
        <h3 style="color: #fff; margin: 0;">📧 Newsletter</h3>
    </div>
    <p style="color: rgba(255,255,255,0.5); font-size: 0.85rem; margin-bottom: 1rem;">
        Recevez nos nouveaux articles directement dans votre boîte mail
    </p>
    
    <form id="newsletterForm" style="display: flex; gap: 10px; max-width: 400px; margin: 0 auto; flex-wrap: wrap;">
        <input type="email" id="newsletter_email" placeholder="Votre email" required 
               style="flex: 2; min-width: 200px; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(74,222,128,0.15); border-radius: 50px; color: #fff; font-family: inherit;">
        <input type="text" id="newsletter_nom" placeholder="Votre nom (optionnel)" 
               style="flex: 1; min-width: 150px; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(74,222,128,0.15); border-radius: 50px; color: #fff; font-family: inherit;">
        <button type="submit" style="padding: 12px 24px; background: #16a34a; border: none; border-radius: 50px; color: #fff; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.2s;">
            S'abonner
        </button>
    </form>
    <div id="newsletterMessage" style="margin-top: 12px; font-size: 0.8rem;"></div>
</div>

<script>
document.getElementById('newsletterForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const email = document.getElementById('newsletter_email').value;
    const nom = document.getElementById('newsletter_nom').value;
    const messageDiv = document.getElementById('newsletterMessage');
    
    // Désactiver le bouton le temps de l'envoi
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('index.php?action=newsletterSubscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email=' + encodeURIComponent(email) + '&nom=' + encodeURIComponent(nom)
        });
        const data = await response.json();
        
        if(data.success) {
            messageDiv.innerHTML = '<span style="color: #4ade80;"><i class="fas fa-check-circle"></i> ✓ ' + data.message + '</span>';
            document.getElementById('newsletter_email').value = '';
            document.getElementById('newsletter_nom').value = '';
            setTimeout(() => { messageDiv.innerHTML = ''; }, 5000);
        } else {
            messageDiv.innerHTML = '<span style="color: #f87171;"><i class="fas fa-exclamation-triangle"></i> ⚠️ ' + data.message + '</span>';
        }
    } catch(error) {
        messageDiv.innerHTML = '<span style="color: #f87171;"><i class="fas fa-exclamation-triangle"></i> ⚠️ Erreur, veuillez réessayer</span>';
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});
</script>