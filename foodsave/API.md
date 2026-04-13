# Documentation des Routes - FoodSave

## 📍 Routes FrontOffice (index.php)

### Authentification

#### `GET /index.php?action=login`
**Description** : Affiche la page de connexion
**Méthode** : GET
**Session requise** : Non
**Réponse** : Page HTML login.html

```
URL: http://localhost/foodsave/index.php?action=login
```

---

#### `POST /index.php?action=handleLogin`
**Description** : Traite la soumission du formulaire de connexion
**Méthode** : POST
**Paramètres POST** :
- `email` (string, required) : Adresse email de l'utilisateur
- `password` (string, required) : Mot de passe

**Validation** :
- Email valide et existant
- Mot de passe correct

**Réponse** :
- ✅ Succès : Redirection vers dashboard (user) ou admin (admin)
- ❌ Erreur : Affichage des erreurs sur la page login

```
URL: http://localhost/foodsave/index.php?action=handleLogin
Method: POST
Content-Type: application/x-www-form-urlencoded

Body:
email=user@example.com&password=Password123
```

---

#### `GET /index.php?action=register`
**Description** : Affiche la page d'inscription
**Méthode** : GET
**Session requise** : Non
**Réponse** : Page HTML register.html

```
URL: http://localhost/foodsave/index.php?action=register
```

---

#### `POST /index.php?action=handleRegister`
**Description** : Traite l'inscription d'un nouvel utilisateur
**Méthode** : POST
**Paramètres POST** :
- `firstname` (string, required) : Prénom (min 2 caractères)
- `lastname` (string, required) : Nom (min 2 caractères)
- `email` (string, required) : Email unique et valide
- `password` (string, required) : Mot de passe (min 8 caractères)
- `type` (string, required) : 'individual' ou 'startup'
- `company_name` (string, optional) : Requis si type='startup'

**Validation** : Voir Model\User::validate()

**Réponse** :
- ✅ Succès : Redirection vers login avec message succès
- ❌ Erreur : Affichage des erreurs sur la page register

```
URL: http://localhost/foodsave/index.php?action=handleRegister
Method: POST

Body:
firstname=Jean&lastname=Dupont&email=jean@example.com&password=Password123&type=individual
```

---

### Dashboard Utilisateur

#### `GET /index.php?action=dashboard`
**Description** : Affiche le tableau de bord utilisateur
**Méthode** : GET
**Session requise** : Oui (user ou admin non-admin)
**Réponse** : Page HTML dashboard.html

```
URL: http://localhost/foodsave/index.php?action=dashboard
```

---

#### `GET /index.php?action=profile`
**Description** : Affiche le profil de l'utilisateur connecté
**Méthode** : GET
**Session requise** : Oui
**Réponse** : Page HTML profile.html

```
URL: http://localhost/foodsave/index.php?action=profile
```

---

#### `GET /index.php?action=editProfile`
**Description** : Affiche le formulaire d'édition du profil
**Méthode** : GET
**Session requise** : Oui
**Réponse** : Page HTML edit_profile.html

```
URL: http://localhost/foodsave/index.php?action=editProfile
```

---

#### `POST /index.php?action=handleEditProfile`
**Description** : Traite la mise à jour du profil utilisateur
**Méthode** : POST
**Session requise** : Oui
**Paramètres POST** :
- `firstname` (string, required)
- `lastname` (string, required)
- `email` (string, required) : Doit être unique
- `type` (string, required)
- `company_name` (string, optional)

**Validation** : Voir Model\User::validate()

**Réponse** :
- ✅ Succès : Mise à jour de la session et redirection vers profil
- ❌ Erreur : Affichage des erreurs sur le formulaire

```
URL: http://localhost/foodsave/index.php?action=handleEditProfile
Method: POST
```

---

#### `GET /index.php?action=logout`
**Description** : Déconnecte l'utilisateur
**Méthode** : GET
**Session requise** : Oui
**Réponse** : Redirection vers login

```
URL: http://localhost/foodsave/index.php?action=logout
```

---

## 🔐 Routes BackOffice (admin.php)

Toutes les routes admin nécessitent :
- **Session active** : Oui
- **Rôle requis** : 'admin'

### Dashboard Admin

#### `GET /admin.php?action=dashboard`
**Description** : Affiche le tableau de bord administrateur
**Réponse** : Page HTML admin_dashboard.html

```
URL: http://localhost/foodsave/admin.php?action=dashboard
```

**Exemple de réponse** :
```html
<!-- Statistiques globales, derniers utilisateurs, etc. -->
```

---

### Gestion des Utilisateurs

#### `GET /admin.php?action=users`
**Description** : Affiche la liste complète des utilisateurs
**Méthode** : GET
**Réponse** : Page HTML users_list.html avec table

```
URL: http://localhost/foodsave/admin.php?action=users
```

**Données affichées** :
- ID, Prénom, Nom, Email, Type, Rôle, Date inscription
- Actions : Détails, Éditer

---

#### `GET /admin.php?action=user_details&id={id}`
**Description** : Affiche les détails d'un utilisateur spécifique
**Méthode** : GET
**Paramètres GET** :
- `id` (integer, required) : ID de l'utilisateur

**Réponse** : Page HTML user_details.html

```
URL: http://localhost/foodsave/admin.php?action=user_details&id=2
```

**Actions disponibles** :
- Changer le rôle
- Éditer les informations
- Supprimer l'utilisateur

---

#### `GET /admin.php?action=edit_user&id={id}`
**Description** : Affiche le formulaire d'édition d'un utilisateur
**Méthode** : GET
**Paramètres GET** :
- `id` (integer, required) : ID de l'utilisateur

**Réponse** : Page HTML edit_user.html

```
URL: http://localhost/foodsave/admin.php?action=edit_user&id=2
```

---

#### `POST /admin.php?action=handleEditUser`
**Description** : Traite la mise à jour d'un utilisateur (admin)
**Méthode** : POST
**Paramètres POST** :
- `id` (integer, required)
- `firstname` (string, required)
- `lastname` (string, required)
- `email` (string, required)
- `type` (string, required)
- `company_name` (string, optional)

**Validation** : Voir Model\User::validate()

**Réponse** :
- ✅ Succès : Redirection vers user_details
- ❌ Erreur : Affichage des erreurs

```
URL: http://localhost/foodsave/admin.php?action=handleEditUser
Method: POST

Body:
id=2&firstname=Jean&lastname=Dupont&email=jean@example.com&type=individual
```

---

#### `POST /admin.php?action=changeUserRole`
**Description** : Change le rôle d'un utilisateur
**Méthode** : POST
**Paramètres POST** :
- `id` (integer, required)
- `role` (string, required) : 'user' ou 'admin'

**Validation** :
- Role doit être 'user' ou 'admin'

**Réponse** :
- ✅ Succès : Redirection vers user_details
- ❌ Erreur : Redirection avec message d'erreur

```
URL: http://localhost/foodsave/admin.php?action=changeUserRole
Method: POST

Body:
id=2&role=admin
```

---

#### `POST /admin.php?action=deleteUser`
**Description** : Supprime un utilisateur
**Méthode** : POST
**Paramètres POST** :
- `id` (integer, required)

**Réponse** :
- ✅ Succès : Redirection vers users_list
- ❌ Erreur : Redirection avec message d'erreur

```
URL: http://localhost/foodsave/admin.php?action=deleteUser
Method: POST

Body:
id=2
```

**Attention** : Cette action est irréversible

---

## 📊 Sessions et Variables

### Variables de Session Après Login

```php
$_SESSION['user_id']        // ID de l'utilisateur
$_SESSION['user_firstname'] // Prénom
$_SESSION['user_lastname']  // Nom
$_SESSION['user_email']     // Email
$_SESSION['user_role']      // 'user' ou 'admin'
$_SESSION['user_type']      // 'individual' ou 'startup'
```

### Variables de Session Messages

```php
$_SESSION['success'] // Message de succès
$_SESSION['error']   // Message d'erreur
```

---

## 🔒 Contrôle d'Accès

### Routes Protégées

| Route | Requis | Description |
|-------|--------|-------------|
| login | - | Accessible sans auth |
| register | - | Accessible sans auth |
| dashboard | User | Profil utilisateur requis |
| profile | User | Profil utilisateur requis |
| editProfile | User | Profil utilisateur requis |
| admin/* | Admin | Uniquement administrateurs |

---

## 📝 Codes HTTP

| Code | Signification |
|------|---------------|
| 200 | Succès |
| 302 | Redirection (Login success, etc.) |
| 400 | Validation échouée |
| 401 | Non authentifié |
| 403 | Accès refusé (pas admin) |
| 404 | Ressource non trouvée |
| 500 | Erreur serveur |

---

## 🧪 Exemples de Requêtes

### Example 1 : Login

```bash
curl -X POST http://localhost/foodsave/index.php?action=handleLogin \
  -d "email=admin@foodsave.com&password=Admin123456"
```

### Example 2 : Créer un compte

```bash
curl -X POST http://localhost/foodsave/index.php?action=handleRegister \
  -d "firstname=Jean&lastname=Dupont&email=jean@example.com&password=Password123&type=individual"
```

### Example 3 : Modifier son profil

```bash
curl -X POST http://localhost/foodsave/index.php?action=handleEditProfile \
  -d "firstname=Jean&lastname=Dupont&email=jean@example.com&type=individual" \
  -b "PHPSESSID=xxxxx"
```

### Example 4 : Lister les utilisateurs (admin)

```bash
curl http://localhost/foodsave/admin.php?action=users \
  -b "PHPSESSID=xxxxx"
```

---

## 📋 Validation Côté Serveur

Voir [Model/User.php](Model/User.php) pour les règles de validation complètes.

### Règles de Validation

#### Email
- Format valide(filter_var avec FILTER_VALIDATE_EMAIL)
- Doit être unique dans la BD
- Pas de doublon sauf lors de l'édition du propre profil

#### Mot de passe
- Minimum 8 caractères
- Au moins une majuscule
- Au moins une minuscule
- Au moins un chiffre
- Hachage avec bcrypt (password_hash)

#### Prénom/Nom
- Minimum 2 caractères
- Caractères autorisés : lettres, espaces, tirets, accents
- Regex : `/^[a-zA-ZÀ-ÿ\s\'-]+$/`

#### Type
- Valeur : 'individual' ou 'startup'

#### Nom Entreprise
- Requis si type = 'startup'
- Minimum 2 caractères

---

## 🔐 Sécurité

- **Toutes les entrées utilisateur** sont filtrées avec `htmlspecialchars()`
- **Toutes les requêtes BD** utilisent PDO avec requêtes paramétrées
- **Les mots de passe** sont hachés avec `password_hash()` / `password_verify()`
- **Les sessions** protègent l'accès aux routes admin
- **CSRF** : À implémenter pour les formulaires critiques

---

## 📞 Erreurs Communes

### 500 Internal Server Error
- Vérifiez la BD est connectée
- Vérifiez la syntaxe PHP
- Consultez les logs d'erreur d'Apache

### 302 Redirection (En boucle)
- Session non créée
- Authentification échouée
- Redirection manuellement vers login

### 404 Not Found
- L'action n'existe pas
- Vérifiez l'URL
- Vérifiez les fichiers View

---

Dernière mise à jour: 11 avril 2026
