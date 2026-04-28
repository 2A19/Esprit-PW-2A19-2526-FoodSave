// validationA.js - Fonctions de validation réutilisables

// Valider que le champ n'est pas vide
function estVide(valeur) {
    return valeur.trim() === '';
}

// Valider la longueur minimale
function longueurMinimale(valeur, min) {
    return valeur.trim().length >= min;
}

// Valider la longueur maximale
function longueurMaximale(valeur, max) {
    return valeur.trim().length <= max;
}

// Afficher une erreur
function afficherErreur(element, message) {
    const errorDiv = element.parentElement.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '0.8rem';
        errorDiv.style.marginTop = '5px';
    } else {
        const newError = document.createElement('div');
        newError.className = 'error-message';
        newError.textContent = message;
        newError.style.color = '#dc3545';
        newError.style.fontSize = '0.8rem';
        newError.style.marginTop = '5px';
        element.parentElement.appendChild(newError);
    }
    element.style.borderColor = '#dc3545';
}

// Effacer l'erreur
function effacerErreur(element) {
    const errorDiv = element.parentElement.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
    element.style.borderColor = '#ddd';
}

// Valider le titre
function validerTitre(titre) {
    if (estVide(titre)) {
        return { valide: false, message: "Le titre est obligatoire" };
    }
    if (!longueurMinimale(titre, 3)) {
        return { valide: false, message: "Le titre doit contenir au moins 3 caractères" };
    }
    if (!longueurMaximale(titre, 200)) {
        return { valide: false, message: "Le titre ne doit pas dépasser 200 caractères" };
    }
    return { valide: true, message: "" };
}

// Valider le contenu
function validerContenu(contenu) {
    if (estVide(contenu)) {
        return { valide: false, message: "Le contenu est obligatoire" };
    }
    if (!longueurMinimale(contenu, 10)) {
        return { valide: false, message: "Le contenu doit contenir au moins 10 caractères" };
    }
    return { valide: true, message: "" };
}

// Valider la catégorie
function validerCategorie(categorie) {
    const categoriesValides = ['Astuces', 'Recettes', 'Conseils'];
    if (!categoriesValides.includes(categorie)) {
        return { valide: false, message: "Veuillez sélectionner une catégorie valide" };
    }
    return { valide: true, message: "" };
}

// Valider l'image (optionnel)
function validerImage(file) {
    if (file && file.size > 0) {
        const extensionsValides = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        const extension = file.name.split('.').pop().toLowerCase();
        if (!extensionsValides.includes(extension)) {
            return { valide: false, message: "Format d'image non autorisé (JPG, PNG, GIF, WEBP)" };
        }
        if (file.size > 2 * 1024 * 1024) { // 2MB
            return { valide: false, message: "L'image ne doit pas dépasser 2 Mo" };
        }
    }
    return { valide: true, message: "" };
}