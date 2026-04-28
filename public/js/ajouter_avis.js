// ajouter-avis.js - Version corrigée

document.addEventListener('DOMContentLoaded', function() {
    
    console.log("JS chargé - ajouter-avis.js");
    
    const formulaire = document.getElementById('avisForm');
    
    if (!formulaire) {
        console.error("Formulaire avec id='avisForm' non trouvé !");
        return;
    }
    
    console.log("Formulaire trouvé, validation activée");
    
    // Récupération des éléments
    const contenuTextarea = document.getElementById('contenu');
    const noteInputs = document.querySelectorAll('input[name="note"]');
    
    // ========== VALIDATION EN TEMPS RÉEL ==========
    
    if (contenuTextarea) {
        contenuTextarea.addEventListener('input', function() {
            const longueur = this.value.length;
            const longueurSansEspaces = this.value.trim().length;
            
            console.log("Longueur totale:", longueur, "Longueur sans espaces:", longueurSansEspaces);
            
            if (this.value.trim() === '') {
                afficherErreur(this, "Veuillez écrire votre avis");
            } else if (longueurSansEspaces < 5) {
                afficherErreur(this, "Votre avis doit contenir au moins 5 caractères (actuellement: " + longueurSansEspaces + ")");
            } else if (longueur > 500) {
                afficherErreur(this, "Votre avis ne doit pas dépasser 500 caractères (actuellement: " + longueur + ")");
            } else {
                effacerErreur(this);
            }
        });
    }
    
    // ========== VALIDATION AVANT SOUMISSION ==========
    
    formulaire.addEventListener('submit', function(event) {
        
        console.log("Tentative de soumission du formulaire");
        
        let estValide = true;
        let messagesErreur = [];
        
        // Récupérer la note sélectionnée
        let noteValue = null;
        for (let i = 0; i < noteInputs.length; i++) {
            if (noteInputs[i].checked) {
                noteValue = noteInputs[i].value;
                break;
            }
        }
        
        // Récupérer le contenu
        const contenuValue = contenuTextarea ? contenuTextarea.value : '';
        const longueurTotale = contenuValue.length;
        const longueurSansEspaces = contenuValue.trim().length;
        
        console.log("Note sélectionnée:", noteValue);
        console.log("Contenu:", contenuValue);
        console.log("Longueur totale:", longueurTotale, "Longueur sans espaces:", longueurSansEspaces);
        
        // Validation de la note
        if (!noteValue) {
            messagesErreur.push("❌ Veuillez sélectionner une note (1 à 5 étoiles)");
            estValide = false;
        }
        
        // Validation du contenu
        if (!contenuValue || contenuValue.trim() === '') {
            messagesErreur.push("❌ Veuillez écrire votre avis");
            estValide = false;
            if (contenuTextarea) {
                afficherErreur(contenuTextarea, "Veuillez écrire votre avis");
            }
        } 
        else if (longueurSansEspaces < 5) {
            messagesErreur.push("❌ Votre avis doit contenir au moins 5 caractères (actuellement: " + longueurSansEspaces + ")");
            estValide = false;
            if (contenuTextarea) {
                afficherErreur(contenuTextarea, "Votre avis doit contenir au moins 5 caractères");
            }
        }
        else if (longueurTotale > 500) {
            messagesErreur.push("❌ Votre avis ne doit pas dépasser 500 caractères (actuellement: " + longueurTotale + ")");
            estValide = false;
            if (contenuTextarea) {
                afficherErreur(contenuTextarea, "Votre avis ne doit pas dépasser 500 caractères");
            }
        }
        else {
            if (contenuTextarea) {
                effacerErreur(contenuTextarea);
            }
        }
        
        // Si erreurs, on bloque l'envoi
        if (!estValide) {
            event.preventDefault();
            event.stopPropagation();
            
            // Afficher un message d'erreur global
            const erreurGlobal = document.getElementById('erreurGlobal');
            if (erreurGlobal) {
                erreurGlobal.style.display = 'block';
                erreurGlobal.innerHTML = '⚠️ Veuillez corriger les erreurs :<br>- ' + messagesErreur.join('<br>- ');
            } else {
                alert(messagesErreur.join('\n'));
            }
            
            console.log("Formulaire bloqué - erreurs:", messagesErreur);
        } else {
            console.log("Formulaire valide, envoi autorisé");
        }
    });
});

// ========== FONCTIONS UTILITAIRES ==========

function afficherErreur(element, message) {
    if (!element) return;
    
    // Supprimer l'ancienne erreur
    const ancienneErreur = element.parentElement.querySelector('.error-message');
    if (ancienneErreur) {
        ancienneErreur.remove();
    }
    
    // Créer la nouvelle erreur
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.8rem';
    errorDiv.style.marginTop = '5px';
    element.parentElement.appendChild(errorDiv);
    
    // Styliser le champ en erreur
    element.style.borderColor = '#dc3545';
    element.style.backgroundColor = '#fff8f8';
}

function effacerErreur(element) {
    if (!element) return;
    
    const errorDiv = element.parentElement.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
    element.style.borderColor = '#ddd';
    element.style.backgroundColor = 'white';
}