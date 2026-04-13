# 🧪 Checklist de Test - FoodSave

## ✅ Avant de Commencer

- [ ] XAMPP est installé et lancé
- [ ] Apache est en cours d'exécution
- [ ] MySQL est en cours d'exécution
- [ ] Fichiers copié da ns `C:\xampp\htdocs\foodsave\`
- [ ] Base de données importée (`database_setup.sql`)

---

## 🔐 Tests d'Authentification

### Test 1 : Page de Login
- [ ] Allez à http://localhost/foodsave/index.php?action=login
- [ ] Page s'affiche correctement
- [ ] Logo FoodSave visible
- [ ] Formulaire présent avec email et password
- [ ] Lien vers inscription accessible

### Test 2 : Login Valide
- [ ] Email : admin@foodsave.com
- [ ] Mot de passe : Admin123456
- [ ] Cliquez "Se connecter"
- [ ] ✅ Redirection vers http://localhost/foodsave/admin.php?action=dashboard
- [ ] Dashboard admin s'affiche

### Test 3 : Login Invalide - Email Incorrect
- [ ] Email : wrong@example.com
- [ ] Mot de passe : Admin123456
- [ ] Cliquez "Se connecter"
- [ ] ❌ Message d'erreur : "Email ou mot de passe incorrect"
- [ ] Restez sur la page de login

### Test 4 : Login Invalide - Mot de Passe Incorrect
- [ ] Email : admin@foodsave.com
- [ ] Mot de passe : WrongPassword
- [ ] Cliquez "Se connecter"
- [ ] ❌ Message d'erreur : "Email ou mot de passe incorrect"
- [ ] Restez sur la page de login

### Test 5 : Validations du Formulaire Login

**Test Email Vide**
- [ ] Laissez email vide
- [ ] Entrez mot de passe valide
- [ ] Cliquez "Se connecter"
- [ ] ❌ Erreur affichée

**Test Mot de Passe Vide**
- [ ] Entrez email valide
- [ ] Laissez mot de passe vide
- [ ] Cliquez "Se connecter"
- [ ] ❌ Erreur affichée

---

## 📝 Tests d'Inscription

### Test 6 : Page d'Inscription
- [ ] Allez à http://localhost/foodsave/index.php?action=register
- [ ] Formulaire d'inscription s'affiche
- [ ] Champs présents : Prénom, Nom, Email, Mot de passe, Type
- [ ] Sélection Type affiche : Particulier et Startup

### Test 7 : Inscription Valide - Particulier
- [ ] Prénom : Jean
- [ ] Nom : Dupont
- [ ] Email : jean.dupont@example.com (unique)
- [ ] Mot de passe : MyPassword123
- [ ] Type : Particulier
- [ ] Cliquez "S'inscrire"
- [ ] ✅ Message de succès "Inscription réussie"
- [ ] Redirection vers login

### Test 8 : Inscription Valide - Startup
- [ ] Prénom : Marie
- [ ] Nom : Martin
- [ ] Email : marie.martin@example.com (unique)
- [ ] Mot de passe : MyPassword123
- [ ] Type : Startup
- [ ] Attendez... champ "Nom d'entreprise" apparaît
- [ ] Entrez : "EcoFood Inc"
- [ ] Cliquez "S'inscrire"
- [ ] ✅ Succès + redirection login

### Test 9 : Validations d'Inscription

**Email Invalide**
- [ ] Email : invalidemail
- [ ] Cliquez "S'inscrire"
- [ ] ❌ Erreur : "L'email n'est pas valide"

**Mot de Passe Faible (< 8 caractères)**
- [ ] Mot de passe : Short1
- [ ] Cliquez "S'inscrire"
- [ ] ❌ Erreur : "Minimum 8 caractères"

**Mot de Passe Sans Majuscule**
- [ ] Mot de passe : password123
- [ ] Cliquez "S'inscrire"
- [ ] ❌ Erreur concernant majuscule

**Mot de Passe Sans Chiffre**
- [ ] Mot de passe : MyPassword
- [ ] Cliquez "S'inscrire"
- [ ] ❌ Erreur concernant chiffre

**Email Déjà Utilisé**
- [ ] Email : admin@foodsave.com
- [ ] Cliquez "S'inscrire"
- [ ] ❌ Erreur : "Cet email est déjà utilisé"

**Prénom/Nom Trop Court**
- [ ] Prénom : A
- [ ] Cliquez "S'inscrire"
- [ ] ❌ Erreur : "au moins 2 caractères"

---

## 👥 Tests FrontOffice - Utilisateur Connecté

### Test 10 : Login avec Compte Utilisateur
- [ ] Email : user@foodsave.com
- [ ] Mot de passe : User@12345
- [ ] Cliquez "Se connecter"
- [ ] ✅ Redirection vers http://localhost/foodsave/index.php?action=dashboard

### Test 11 : Dashboard Utilisateur
- [ ] Titre : "Bienvenue, Jean !"
- [ ] Message de bienvenue visible
- [ ] 6 cartes de fonctionnalités affichées
- [ ] Navigation correcte (Tableau de bord, Profil, Déconnexion)

### Test 12 : Accès au Profil
- [ ] Cliquez sur "Mon profil" dans le menu
- [ ] ✅ Allez à http://localhost/foodsave/index.php?action=profile
- [ ] Informations de l'utilisateur affichées
- [ ] Prénom, Nom, Email, Type, Date création visibles

### Test 13 : Édition du Profil
- [ ] Cliquez sur "Modifier le profil"
- [ ] ✅ Formulaire d'édition s'affiche
- [ ] Tous les champs pré-remplis correctement
- [ ] Modifiez le prénom : "Justin"
- [ ] Modifiez le nom : "Dupree"
- [ ] Cliquez "Enregistrer les modifications"
- [ ] ✅ Message succès
- [ ] Retour à la page profil
- [ ] Modifications sauvegardées
- [ ] Message "Proil mis à jour avec succès !"

### Test 14 : Édition - Changement de Type
- [ ] Allez à l'édition du profil
- [ ] Changez type à "Startup"
- [ ] ✅ Champ "Nom d'entreprise" apparaît
- [ ] Entrez : "My Eco Startup"
- [ ] Enregistrez
- [ ] ✅ Modifications sauvegardées

### Test 15 : Édition - Validation
- [ ] Édition du profil
- [ ] Videz l'email
- [ ] Cliquez Enregistrer
- [ ] ❌ Erreur email requis
- [ ] Entrez email invalide (sans @)
- [ ] Cliquez Enregistrer
- [ ] ❌ Erreur format email

### Test 16 : Déconnexion
- [ ] Cliquez "Déconnexion" dans le menu
- [ ] ✅ Redirection vers login
- [ ] Cliquez "Se connecter"
- [ ] ❌ S'il s'agit d'une page différente, il faut se reconnecter

---

## 🔐 Tests BackOffice - Interface Admin

### Test 17 : Accès BackOffice Non-Autorisé
- [ ] Allez à http://localhost/foodsave/admin.php
- [ ] Sans être connecté
- [ ] ❌ Redirection vers login
- [ ] Connectez avec compte user (user@foodsave.com)
- [ ] ❌ Redirection vers login (accès refusé)

### Test 18 : Dashboard Admin
- [ ] Connectez avec : admin@foodsave.com / Admin123456
- [ ] Allez à http://localhost/foodsave/admin.php?action=dashboard
- [ ] ✅ Page admin affichée
- [ ] Titre : "Tableau de Bord Administrateur"
- [ ] Statistiques visibles (nombre d'utilisateurs)
- [ ] Tableau avec utilisateurs récents

### Test 19 : Liste Complète des Utilisateurs
- [ ] Cliquez sur "Gérer les utilisateurs" (ou navbar)
- [ ] ✅ Allez à http://localhost/foodsave/admin.php?action=users
- [ ] Table avec tous les utilisateurs
- [ ] Colonnes : ID, Prénom, Nom, Email, Type, Rôle, Date, Actions
- [ ] Tous les utilisateurs de la BD sont listés
- [ ] Boutons "Détails" et "Éditer" pour chaque ligne

### Test 20 : Détails d'un Utilisateur (Admin)
- [ ] Cliquez "Détails" pour un utilisateur
- [ ] ✅ Page détails s'affiche
- [ ] Information complète de l'utilisateur
- [ ] ID, Rôle, Prénom, Nom, Email, Type, Date
- [ ] Boutton "Changer le Rôle"
- [ ] Bouton "Éditer les informations"
- [ ] Bouton "Supprimer l'utilisateur"

### Test 21 : Changer le Rôle d'un Utilisateur
- [ ] Dans détails d'un utilisateur
- [ ] Sélectionnez "Administrateur"
- [ ] Cliquez "Modifier le rôle"
- [ ] ✅ Message succès : "Rôle modifié"
- [ ] Rôle changé en ADMIN
- [ ] Reclicquez pour le remettre en USER

### Test 22 : Édition d'un Utilisateur (Admin)
- [ ] Cliquez "Éditer les informations"
- [ ] ✅ Formulaire d'édition affichéPrénom : Modifiez pour "Pierre"
- [ ] Cliquez "Enregistrer les modifications"
- [ ] ✅ Message succès
- [ ] Modification sauvegardée
- [ ] Validation fonctionnelle (email invalide = erreur)

### Test 23 : Suppression d'un Utilisateur
- [ ] Dans page détails
- [ ] Cliquez "Supprimer l'utilisateur"
- [ ] ✅ Dialogue de confirmation
- [ ] Confirmez la suppression
- [ ] ✅ Message succès
- [ ] Redirection vers liste utilisateurs
- [ ] Utilisateur ne figure plus dans la liste

### Test 24 : Accès Admin Restreint
- [ ] Déconnectez-vous
- [ ] Connectez avec user@foodsave.com
- [ ] Essayez http://localhost/foodsave/admin.php
- [ ] ❌ Redirection vers login

---

## 🎨 Tests d'Interface et Design

### Test 25 : Design Responsive - Desktop
- [ ] Ouvrez le site sur écran large (1920px)
- [ ] ✅ Layout s'affiche correctement
- [ ] Tous les éléments visibles
- [ ] Navigation accessible

### Test 26 : Design Responsive - Tablet
- [ ] Redimensionnez à 768px
- [ ] ✅ Layout s'adapte
- [ ] Navigation accessible
- [ ] Tableaux restent lisibles

### Test 27 : Design Responsive - Mobile
- [ ] Redimensionnez à 480px
- [ ] ✅ Layout s'adapte
- [ ] Boutons tactiles
- [ ] Texte lisible
- [ ] Pas de débordement

### Test 28 : Charte Graphique
- [ ] Vérifiez le logo FoodSave
- [ ] ✅ Couleur verte (#4CAF50) utilisée
- [ ] ✅ Couleur orange (#FFA726) utilisée
- [ ] ✅ Couleur blanche (#FFFFFF) utilisée
- [ ] Cohérence de la charte partout

### Test 29 : Accessibilité
- [ ] Tous les formulaires ont des labels
- [ ] ✅ Contraste de couleur acceptable
- [ ] ✅ Navigation au clavier fonctionnelle (Tab)
- [ ] ✅ Messages d'erreur clairs

---

## 🔒 Tests de Sécurité

### Test 30 : PDO - Injection SQL
- [ ] Login avec email : ' OR '1'='1
- [ ] ❌ Pas d'accès (PDO protège)
- [ ] Message d'erreur normal

### Test 31 : Validation - XSS
- [ ] Inscription avec prénom : `<script>alert('xss')</script>`
- [ ] ❌ Error validationou script ne s'exécute pas

### Test 32 : Password Hashing
- [ ] Connectez-vous, consultez la BD
- [ ] Dans phpmyadmin, regardez la colonne password
- [ ] ✅ Mot de passe hashé (commence par $2y$)
- [ ] ❌ Pas en clair

### Test 33 : Session Protection
- [ ] Logout
- [ ] Essayez d'accéder directement à dashboard sans session
- [ ] ❌ Redirection vers login

### Test 34 : CSRF (À Améliorer)
- Note : Tokens CSRF pas implémentés (À faire en production)

---

## 🧪 Tests de Validation

### Test 35 : Email Valide
- ✅ user@example.com
- ❌ user@
- ❌ @example.com
- ❌ user.example.com

### Test 36 : Mot de Passe Valide
- ✅ MyPassword123
- ❌ password123 (pas de majuscule)
- ❌ MYPASSWORD (pas de minuscule)
- ❌ Mypassword (pas de chiffre)
- ❌ MyPass1 (< 8 caractères)

### Test 37 : Prénom/Nom
- ✅ Jean
- ✅ Jean-Pierre
- ✅ Müller (accents)
- ❌ J (< 2 caractères)
- ❌ Jean123 (caractères numériques)

### Test 38 : Type Utilisateur
- ✅ individual
- ✅ startup
- ❌ other (invalide)

### Test 39 : Company Name (Startup)
- Type : Particulier → Champ invisible
- ✅ Changez à Startup → Champ visible et obligatoire
- ❌ Vide avec Startup = erreur

---

## 📊 Tests de Base de Données

### Test 40 : Données Persistentes
- [ ] Créez un nouvel utilisateur (registration)
- [ ] Logout
- [ ] Connectez-vous avec ce nouvel utilisateur
- [ ] ✅ Données sauvegardées

### Test 41 : Unicité Email
- [ ] Inscription avec email déjà utilisé
- [ ] ❌ Erreur : "Email déjà utilisé"

### Test 42 : Indices de Performance
- [ ] Dans phpmyadmin
- [ ] Structure table users
- [ ] ✅ Index sur email
- [ ] ✅ Index sur role
- [ ] ✅ Index sur type
- [ ] ✅ Index sur created_at

---

## 🚀 Tests de Performance

### Test 43 : Chargement des Pages
- [ ] Login page : < 1 seconde
- [ ] Dashboard : < 2 secondes
- [ ] Liste utilisateurs : < 2 secondes

### Test 44 : Validation JavaScript
- [ ] Modification en direct (pas d'attente)
- [ ] Erreurs affichées instantanément

---

## 📋 Résumé Final

| Catégorie | Tests | Status |
|-----------|-------|--------|
| Authentification | 5 | ✅ |
| Inscription | 4 | ✅ |
| FrontOffice | 7 | ✅ |
| BackOffice | 8 | ✅ |
| Interface | 5 | ✅ |
| Sécurité | 5 | ✅ |
| Validation | 5 | ✅ |
| BD | 3 | ✅ |
| Performance | 2 | ✅ |
| **TOTAL** | **44 tests** | **✅ COMPLET** |

---

## ✨ Conclusion

Si tous les tests passent avec ✅, FoodSave est prêt pour la production !

**Date de test recommandée** : Avant chaque déploiement  
**Durée moyenne** : 30-45 minutes  
**Ressource** : Cette checklist  

---

Bonne chance ! 🚀
