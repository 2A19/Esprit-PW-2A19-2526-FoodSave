// ajouter-article.js - Validation du formulaire d'ajout d'article

// Attendre que la page soit chargée
document.addEventListener('DOMContentLoaded', function() {
    
    // Récupérer les éléments du formulaire
    const formulaire = document.getElementById('articleForm');
    const titreInput = document.getElementById('titre');
    const categorieSelect = document.getElementById('categorie');
    const contenuTextarea = document.getElementById('contenu');
    const imageInput = document.getElementById('image');
    
    // Validation en temps réel (quand l'utilisateur tape)
    if (titreInput) {
        titreInput.addEventListener('input', function() {
            const resultat = validerTitre(this.value);
            if (!resultat.valide) {
                afficherErreur(this, resultat.message);
            } else {
                effacerErreur(this);
            }
        });
    }
    
    if (contenuTextarea) {
        contenuTextarea.addEventListener('input', function() {
            const resultat = validerContenu(this.value);
            if (!resultat.valide) {
                afficherErreur(this, resultat.message);
            } else {
                effacerErreur(this);
            }
        });
    }
    
    if (categorieSelect) {
        categorieSelect.addEventListener('change', function() {
            const resultat = validerCategorie(this.value);
            if (!resultat.valide) {
                afficherErreur(this, resultat.message);
            } else {
                effacerErreur(this);
            }
        });
    }
    
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const resultat = validerImage(this.files[0]);
            if (!resultat.valide) {
                afficherErreur(this, resultat.message);
            } else {
                effacerErreur(this);
            }
        });
    }
    
    // Validation avant soumission du formulaire
    if (formulaire) {
        formulaire.addEventListener('submit', function(e) {
            let estValide = true;
            
            // Valider le titre
            const titre = titreInput.value;
            const titreResultat = validerTitre(titre);
            if (!titreResultat.valide) {
                afficherErreur(titreInput, titreResultat.message);
                estValide = false;
            }
            
            // Valider la catégorie
            const categorie = categorieSelect.value;
            const categorieResultat = validerCategorie(categorie);
            if (!categorieResultat.valide) {
                afficherErreur(categorieSelect, categorieResultat.message);
                estValide = false;
            }
            
            // Valider le contenu
            const contenu = contenuTextarea.value;
            const contenuResultat = validerContenu(contenu);
            if (!contenuResultat.valide) {
                afficherErreur(contenuTextarea, contenuResultat.message);
                estValide = false;
            }
            
            // Valider l'image (optionnel)
            const image = imageInput.files[0];
            if (image) {
                const imageResultat = validerImage(image);
                if (!imageResultat.valide) {
                    afficherErreur(imageInput, imageResultat.message);
                    estValide = false;
                }
            }
            
            // Si le formulaire n'est pas valide, on bloque l'envoi
            if (!estValide) {
                e.preventDefault();
                const erreurGlobal = document.getElementById('erreurGlobal');
                if (erreurGlobal) {
                    erreurGlobal.style.display = 'block';
                } else {
                    alert('Veuillez corriger les erreurs dans le formulaire');
                }
            }
        });
    }
});