// FoodSave - Validation JavaScript

class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.errors = {};
        this.setupForm();
    }

    setupForm() {
        if (!this.form) return;

        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.validateForm();
            if (Object.keys(this.errors).length === 0) {
                this.form.submit();
            }
        });

        // Validation en temps réel
        const inputs = this.form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
            input.addEventListener('change', () => {
                this.validateField(input);
            });
        });
    }

    validateForm() {
        this.errors = {};
        const inputs = this.form.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
            this.validateField(input);
        });

        this.displayErrors();
    }

    validateField(input) {
        const fieldName = input.name;
        const fieldValue = input.value.trim();
        const fieldType = input.type;

        // Réinitialiser les erreurs pour ce champ
        delete this.errors[fieldName];

        // Valider selon le type de champ
        if (input.hasAttribute('required') && fieldValue === '') {
            this.errors[fieldName] = `Le champ ${input.labels[0]?.textContent || fieldName} est requis`;
        }

        switch (fieldType) {
            case 'email':
                if (fieldValue && !this.isValidEmail(fieldValue)) {
                    this.errors[fieldName] = 'L\'email n\'est pas valide';
                }
                break;

            case 'password':
                if (fieldValue && fieldValue.length < 8) {
                    this.errors[fieldName] = 'Le mot de passe doit contenir au moins 8 caractères';
                } else if (fieldValue && !this.isValidPassword(fieldValue)) {
                    this.errors[fieldName] = 'Le mot de passe doit contenir une majuscule, une minuscule et un chiffre';
                }
                break;

            case 'text':
                if (input.name === 'prenom' || input.name === 'nom') {
                    if (fieldValue && fieldValue.length < 2) {
                        this.errors[fieldName] = `Ce champ doit contenir au moins 2 caractères`;
                    } else if (fieldValue && !this.isValidName(fieldValue)) {
                        this.errors[fieldName] = `Ce champ contient des caractères invalides`;
                    }
                } else if (fieldValue && fieldValue.length < 2) {
                    this.errors[fieldName] = `Ce champ doit contenir au least 2 caractères`;
                }
                break;

            case 'tel':
                if (fieldValue && !this.isValidPhone(fieldValue)) {
                    this.errors[fieldName] = 'Le numéro de téléphone n\'est pas valide';
                }
                break;

            case 'number':
                if (fieldValue && isNaN(fieldValue)) {
                    this.errors[fieldName] = 'Ce champ doit être un nombre';
                }
                break;
        }

        // Afficher/masquer les erreurs du champ
        this.displayFieldError(input);
    }

    displayFieldError(input) {
        const formGroup = input.closest('.form-group');
        if (!formGroup) return;

        // Supprimer les erreurs précédentes
        const previousError = formGroup.querySelector('.error');
        if (previousError) previousError.remove();

        // Ajouter ou supprimer la classe error
        if (this.errors[input.name]) {
            formGroup.classList.add('error');
            const errorEl = document.createElement('div');
            errorEl.className = 'error';
            errorEl.textContent = this.errors[input.name];
            formGroup.appendChild(errorEl);
        } else {
            formGroup.classList.remove('error');
        }
    }

    displayErrors() {
        if (Object.keys(this.errors).length === 0) return;

        // Créer ou afficher le conteneur d'erreurs
        let errorContainer = this.form.querySelector('.form-errors');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'alert alert-error';
            this.form.insertBefore(errorContainer, this.form.firstChild);
        }

        let errorHTML = '<strong>Erreurs de validation :</strong><ul>';
        Object.values(this.errors).forEach(error => {
            errorHTML += `<li>${error}</li>`;
        });
        errorHTML += '</ul>';

        errorContainer.innerHTML = errorHTML;
        errorContainer.style.display = 'block';
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPassword(password) {
        // Au moins une majuscule, une minuscule et un chiffre
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/;
        return passwordRegex.test(password);
    }

    isValidName(name) {
        // Permet les lettres, espaces, tirets et accents
        const nameRegex = /^[a-zA-ZÀ-ÿ\s\'-]+$/;
        return nameRegex.test(name);
    }

    isValidPhone(phone) {
        // Format français ou international
        const phoneRegex = /^[\d\s\+\-\(\)]{10,}$/;
        return phoneRegex.test(phone);
    }
}

// Initialiser les validateurs au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    // Gérer les champs de formulaires dynamiques
    // (Le formulaire n'a plus de champs conditionnels après la suppression de company_name/type)

    // Initialiser les validateurs pour tous les formulaires disponibles
    const forms = ['loginForm', 'registerForm', 'profileForm', 'editProfileForm', 'editUserForm'];
    forms.forEach(formId => {
        if (document.getElementById(formId)) {
            new FormValidator(formId);
        }
    });
});
