// validationAvis.js - Fonctions de validation pour les avis

// Valider que le champ n'est pas vide
function estVide(valeur) {
    return valeur.trim() === '';
}

// Valider la longueur minimale
function longueurMinimale(valeur, min) {
    return valeur.trim().length >= min;
}

// Valider la note (1 à 5)
function noteValide(note) {
    return note >= 1 && note <= 5;
}

// Afficher une erreur
function afficherErreur(element, message) {
    // Supprimer l'ancienne erreur si elle existe
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
    
    // Ajouter une classe pour styliser le champ en erreur
    element.style.borderColor = '#dc3545';
    element.style.backgroundColor = '#fff8f8';
}

// Effacer l'erreur
function effacerErreur(element) {
    const errorDiv = element.parentElement.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
    element.style.borderColor = '#e8e0c8';
    element.style.backgroundColor = '#ffffff';
}

// Valider le contenu d'un avis
function validerContenuAvis(contenu) {
    if (estVide(contenu)) {
        return { valide: false, message: "Veuillez écrire votre avis" };
    }
    if (!longueurMinimale(contenu, 5)) {
        return { valide: false, message: "Votre avis doit contenir au moins 5 caractères" };
    }
    if (!longueurMinimale(contenu, 500)) {
        return { valide: false, message: "Votre avis ne doit pas dépasser 500 caractères" };
    }
    return { valide: true, message: "" };
}

// Valider la note
function validerNote(noteValue) {
    if (!noteValue) {
        return { valide: false, message: "Veuillez sélectionner une note" };
    }
    if (!noteValide(parseInt(noteValue))) {
        return { valide: false, message: "La note doit être comprise entre 1 et 5" };
    }
    return { valide: true, message: "" };
}