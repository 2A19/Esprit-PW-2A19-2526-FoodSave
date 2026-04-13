# FoodSave - Système de Gestion d'Utilisateurs

## À propos
FoodSave est une plateforme web destinée à aider les utilisateurs à réduire le gaspillage alimentaire. Ce projet inclut un système complet de gestion d'utilisateurs avec authentification, CRUD, et interface administrateur.

## Architecture du Projet

Le projet suit l'architecture **MVC (Model-View-Controller)** avec les principes de la **Programmation Orientée Objet** :

```
foodsave/
├── Controller/
│   └── UserController.php      # Contrôleurs de gestion utilisateur
├── Model/
│   └── User.php                # Modèle utilisateur avec validation
├── View/
│   ├── Front/                  # Interface utilisateur
│   │   └── user/
│   │       ├── login.html
│   │       ├── register.html
│   │       ├── dashboard.html
│   │       ├── profile.html
│   │       └── edit_profile.html
│   └── Back/                   # Interface administrateur
│       └── user/
│           ├── admin_dashboard.html
│           ├── users_list.html
│           ├── user_details.html
│           └── edit_user.html
├── config/
│   └── Database.php            # Configuration PDO
├── assets/
│   ├── css/
│   │   └── style.css           # Styles (charte graphique)
│   ├── js/
│   │   └── validation.js       # Validations côté client
│   └── images/                 # Images et ressources
├── index.php                   # Route FrontOffice
├── admin.php                   # Route BackOffice
└── database_setup.sql          # Script d'initialisation BD
```

## Configuration Requise

- **Serveur Web** : Apache (XAMPP)
- **PHP** : 7.4+
- **Base de Données** : MySQL
- **Utilisation obligatoire** : PDO

## Installation et Configuration

### 1. Préparer la Base de Données

#### Option A: Via phpMyAdmin

1. Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
2. Allez à l'onglet "Importer"
3. Sélectionnez le fichier `database_setup.sql`
4. Cliquez sur "Importer"

#### Option B: Via le Terminal MySQL

```bash
mysql -u root -p < database_setup.sql
```

### 2. Configuration du Fichier Database.php

Vérifiez les informations de connexion dans `config/Database.php` :

```php
private $host = 'localhost';      // Serveur MySQL
private $db_name = 'foodsave_db'; // Nom de la base
private $user = 'root';           // Utilisateur MySQL
private $password = '';           // Mot de passe MySQL
```

### 3. Placer le Dossier dans XAMPP

Assurez-vous que le dossier `foodsave` est dans :
```
C:\xampp\htdocs\foodsave\
```

### 4. Démarrer XAMPP

1. Ouvrez le XAMPP Control Panel
2. Cliquez sur "Start" pour Apache et MySQL

### 5. Accéder à l'Application

- **FrontOffice** : http://localhost/foodsave/index.php
- **BackOffice** : http://localhost/foodsave/admin.php

## Comptes de Test

### Compte Administrateur
- **Email** : admin@foodsave.com
- **Mot de passe** : Admin123456

### Compte Utilisateur (Particulier)
- **Email** : user@foodsave.com
- **Mot de passe** : User@12345

### Compte Startup
- **Email** : startup@foodsave.com
- **Mot de passe** : Startup123456

## Fonctionnalités Principales

### FrontOffice (Utilisateurs)

✅ **Authentification**
- Login / Logout
- Inscription (Particulier et Startup)
- Validation de saisie côté PHP et client

✅ **Profil Utilisateur**
- Consultation du profil
- Modification des informations
- Gestion du type de compte (particulier/startup)

✅ **Tableau de Bord**
- Vue d'ensemble personnalisée
- Accès rapide aux fonctionnalités

### BackOffice (Administration)

✅ **Gestion Complète des Utilisateurs**
- Liste des utilisateurs avec pagination
- Recherche et filtrage
- Consultation des détails
- Modification des informations
- Gestion des rôles (user/admin)
- Suppression d'utilisateurs

✅ **Tableau de Bord Administrateur**
- Statistiques globales
- Résumé des utilisateurs
- Actions rapides

## Validation des Données

### Validation Côté PHP (UserController & Model)

La validation est effectuée en PHP pour garantir la sécurité :

- **Prénom/Nom** : Min 2 caractères, caractères valides
- **Email** : Format valide, unicité vérifiée
- **Mot de passe** : Min 8 caractères, majuscule + minuscule + chiffre
- **Type** : individual ou startup
- **Entreprise** : Requis si type = startup

### Validation Côté Client (JavaScript)

Le fichier `assets/js/validation.js` offre :
- Validation en temps réel (blur/change)
- Affichage des erreurs
- Gestion dynamique des champs conditionnels

## Sécurité

✅ **Mesures de Sécurité Implémentées**
- Utilisation de **PDO avec requêtes paramétrées** (prévention SQL Injection)
- **Hachage des mots de passe** avec bcrypt (password_hash/password_verify)
- **Sessions** pour la gestion de l'authentification
- **htmlspecialchars()** pour échapper les sorties (prévention XSS)
- **Contrôle d'accès** basé sur les rôles (RBAC)

## Design et Charte Graphique

La charte graphique FoodSave inclut :

- **Couleurs Principales** :
  - Vert : #4CAF50 (Écologie, nature)
  - Orange : #FFA726 (Énergie, vitalité)
  - Blanc : #FFFFFF (Clarté, pureté)

- **Typographie** : Segoe UI, sans-serif
- **Design Responsive** : Mobile-first, tablette, desktop
- **Accessibilité** : Contraste de couleurs, navigation claire

## Hiérarchie des Rôles

| Rôle | Permissions |
|------|------------|
| **User** | Accès FrontOffice, modification profil personnalisé |
| **Admin** | Accès complet BackOffice, gestion tous utilisateurs, gestion des rôles |

## Arborescence Complète des Fichiers

```
c:\xampp\htdocs\foodsave\
├── Controller/
│   └── UserController.php
├── Model/
│   └── User.php
├── View/
│   ├── Front/
│   │   └── user/
│   │       ├── login.html
│   │       ├── register.html
│   │       ├── dashboard.html
│   │       ├── profile.html
│   │       └── edit_profile.html
│   └── Back/
│       └── user/
│           ├── admin_dashboard.html
│           ├── users_list.html
│           ├── user_details.html
│           └── edit_user.html
├── config/
│   └── Database.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── validation.js
│   └── images/
├── index.php
├── admin.php
├── database_setup.sql
└── README.md
```

## Flux d'Authentification

```
1. Login
   ├─ Saisie email/password
   ├─ Validation PHP
   ├─ Requête DB (PDO)
   ├─ Vérification password_verify()
   ├─ Création session
   └─ Redirection (Admin/User)

2. Register
   ├─ Saisie données (nom, prénom, email, password, type)
   ├─ Validation PHP complète
   ├─ Hachage du mot de passe
   ├─ Insertion DB (PDO)
   ├─ Message succès
   └─ Redirection login

3. CRUD Utilisateur (Admin)
   ├─ Vérification rôle admin
   ├─ Opérations BD (PDO)
   └─ Gestion des sessions
```

## Utilisation de PDO

Tous les accès à la base de données utilisent **PDO** :

```php
// Exemple de requête paramétrée
$query = 'SELECT * FROM users WHERE email = :email';
$stmt = $this->db->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();
```

## Troubleshooting

### Base de Données ne se connecte pas
- Vérifiez que MySQL est démarré dans XAMPP
- Vérifiez les identifiants dans `config/Database.php`
- Assurez-vous que `foodsave_db` a été créée

### Erreur 404 sur les pages
- Vérifiez que les chemins relatifs dans les formulaires sont corrects
- Vérifiez que Apache peut accéder au dossier

### Sessions ne persistent pas
- Assurez-vous que `session_start()` est appelé en haut des fichiers
- Vérifiez les paramètres de session PHP

### Validation JavaScript ne fonctionne pas
- Vérifiez que `assets/js/validation.js` est accessible
- Ouvrez la console du navigateur (F12) pour les erreurs

## Améliorations Futures Possibles

- [ ] Système de récupération de mot de passe (email)
- [ ] Authentification à deux facteurs (2FA)
- [ ] Gestion des produits/liste de courses
- [ ] Système de notifications
- [ ] Dashboard statistiques avancées
- [ ] Export de données (PDF/CSV)
- [ ] API RESTful
- [ ] Tests unitaires (PHPUnit)

## Licence et Droits d'Auteur

Projet FoodSave - 2026
Tous droits réservés.

## Support

Pour toute question ou problème, consultez les commentaires du code ou contactez l'équipe de développement.

---

**Dernière mise à jour** : 11 avril 2026
