// modifier-article.js - Validation du formulaire de modification d'article

// Attendre que la page soit chargée
document.addEventListener('DOMContentLoaded', function() {
    
    // Récupérer les éléments du formulaire
    const formulaire = document.getElementById('editArticleForm');
    const titreInput = document.getElementById('titre');
    const categorieSelect = document.getElementById('categorie');
    const contenuTextarea = document.getElementById('contenu');
    const imageInput = document.getElementById('image');
    
    // ========== VALIDATION EN TEMPS RÉEL ==========
    
    // Validation du titre
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
    
    // Validation du contenu
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
    
    // Validation de la catégorie
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
    
    // Validation de l'image (si une nouvelle image est sélectionnée)
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const resultat = validerImage(this.files[0]);
                if (!resultat.valide) {
                    afficherErreur(this, resultat.message);
                } else {
                    effacerErreur(this);
                }
            }
        });
    }
    
    // ========== VALIDATION AVANT SOUMISSION ==========
    
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
            
            // Valider la nouvelle image (si l'utilisateur en a sélectionné une)
            if (imageInput && imageInput.files.length > 0) {
                const imageResultat = validerImage(imageInput.files[0]);
                if (!imageResultat.valide) {
                    afficherErreur(imageInput, imageResultat.message);
                    estValide = false;
                }
            }
            
            // Si le formulaire n'est pas valide, on bloque l'envoi
            if (!estValide) {
                e.preventDefault();
                
                // Afficher un message d'erreur général
                const erreurGlobal = document.getElementById('erreurGlobal');
                if (erreurGlobal) {
                    erreurGlobal.style.display = 'block';
                    // Faire défiler jusqu'à l'erreur
                    erreurGlobal.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    alert('❌ Veuillez corriger les erreurs dans le formulaire');
                }
            }
        });
    }
    
    // ========== NETTOYAGE DES ERREURS AU CHARGEMENT ==========
    
    // Effacer les erreurs sur les champs déjà valides au chargement de la page
    if (titreInput && titreInput.value) {
        const resultat = validerTitre(titreInput.value);
        if (resultat.valide) {
            effacerErreur(titreInput);
        }
    }
    
    if (contenuTextarea && contenuTextarea.value) {
        const resultat = validerContenu(contenuTextarea.value);
        if (resultat.valide) {
            effacerErreur(contenuTextarea);
        }
    }
    
    if (categorieSelect && categorieSelect.value) {
        const resultat = validerCategorie(categorieSelect.value);
        if (resultat.valide) {
            effacerErreur(categorieSelect);
        }
    }
});