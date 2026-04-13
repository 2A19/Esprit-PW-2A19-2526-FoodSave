# 🚀 Quick Start Guide - FoodSave

## ⚡ Démarrage Rapide

### 1️⃣ Installation (5 minutes)

```bash
1. Téléchargez foodsave/ et placez dans C:\xampp\htdocs\

2. Ouvrez XAMPP Control Panel
   - Start Apache
   - Start MySQL

3. Ouvrez phpmyadmin
   - Import database_setup.sql
   - ✅ Database créée

4. Test
   - Navigateur: http://localhost/foodsave/index.php
   - ✅ Page login affichée
```

**Configuration complète en 5 minutes** ✨

---

## 🔐 Accès Rapides

### Comptes Test

| Type | Email | Mot de Passe |
|------|-------|-------------|
| **Admin** | admin@foodsave.com | Admin123456 |
| **User** | user@foodsave.com | User@12345 |
| **Startup** | startup@foodsave.com | Startup123456 |

### URLs Principales

```
FrontOffice : http://localhost/foodsave/index.php
BackOffice  : http://localhost/foodsave/admin.php
Home        : http://localhost/foodsave/home.html
phpMyAdmin  : http://localhost/phpmyadmin
```

---

## 📖 Documentation Principale

### 📚 Fichiers à Lire (par ordre)

1. **README.md** (Vue d'ensemble)
2. **INSTALL.md** (Installation détaillée)
3. **API.md** (Routes et endpoints)
4. **TECHNICAL.md** (Architecture)
5. **TESTING_CHECKLIST.md** (Tests)

### ⚙️  Fichiers Techniques

```
config/Database.php        # Configuration BD (PDO)
Model/User.php            # Logique métier + validation
Controller/UserController.php # Routage des actions
index.php / admin.php      # Points d'entrée
```

---

## ✅ Fonctionnalités

### FrontOffice (Users)

```
✅ Login / Logout
✅ Inscription (Individual/Startup)
✅ Profil (Consultation/Modification)
✅ Tableau de Bord
✅ Validation complète
```

### BackOffice (Admin)

```
✅ Dashboard Admin
✅ Liste Utilisateurs
✅ Détails Utilisateur
✅ Édition Utilisateur
✅ Gestion des Rôles
✅ Suppression Utilisateur
```

---

## 🎨 Design

- **Vert** : #4CAF50 (Primary)
- **Orange** : #FFA726 (Secondary)
- **Blanc** : #FFFFFF (Base)
- **Responsive** : Mobile, Tablet, Desktop
- **Validation** : JS + PHP

---

## 🔧 Fichiers Clés à Connaître

### Si vous modifiez le design
→ `assets/css/style.css`

### Si vous ajoutez une validation
→ `Model/User.php` + `assets/js/validation.js`

### Si vous ajoutez une route
→ `index.php` / `admin.php` + `Controller/UserController.php`

### Si vous modifiez l'UI
→ `View/Front/user/*.html` ou `View/Back/user/*.html`

---

## 🐛 Dépannage Rapide

| Problème | Solution |
|----------|----------|
| Base de données ne répond | Redémarrer MySQL dans XAMPP |
| CSS/JS ne chargent | Vérifier les chemins dans les HTML |
| Session perdue | Vérifier session_start() |
| 404 Not Found | Vérifier l'URL |
| Validation bloque tout | Lire Model\User.php |

---

## 💾 Sauvegarde BD

```bash
# Exporter la BD
mysqldump -u root foodsave_db > backup.sql

# Importer
mysql -u root foodsave_db < backup.sql
```

---

## 📊 Structure BD

### Table: users

```sql
id              INT
firstname       VARCHAR(100)
lastname        VARCHAR(100)
email           VARCHAR(150) UNIQUE
password        VARCHAR(255) -- BCRYPT
role            ENUM('user','admin')
type            ENUM('individual','startup')
company_name    VARCHAR(255)
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

---

## 🔐 Sécurité - Points Clés

✅ **PDO Preparé SQL** - Protection SQL injection
✅ **Password Bcrypt** - Mots de passe sécurisés
✅ **htmlspecialchars()** - Protection XSS
✅ **Session** - Authentification sûre
✅ **Validation** - Côté serveur stricte

---

## 🎯 Point de Départ Recommandé

### 1. Premier Test (2 minutes)
```
1. Marrez XAMPP
2. Allez à http://localhost/foodsave/index.php
3. Login : admin@foodsave.com / Admin123456
```

### 2. Explorer FrontOffice (10 minutes)
```
1. S'inscrire en tant que particulier
2. Voir le dashboard
3. Modifier le profil
```

### 3. Tester BackOffice (10 minutes)
```
1. Logout et re-login en admin
2. Admin.php → Users list
3. Tester édition / suppression
```

### 4. Lire la Documentation (15 minutes)
```
Lire les fichiers *.md pour comprendre l'architecture
```

---

## 📈 Prochaines Étapes

### Améliorations Possibles (Bonus)

1. **Email Verification** - Confirmer l'email
2. **Password Recovery** - Récupération mot de passe
3. **2FA** - Authentification à deux facteurs
4. **API REST** - Pour une app mobile
5. **Admin Log** - Historique des actions
6. **Backup Auto** - Sauvegarde automatique BD
7. **Search** - Rechercher utilisateurs
8. **Export CSV** - Exporter données admin

---

## 🎓 Ressources Externes

- **PDO** : https://www.php.net/manual/en/class.pdo.php
- **Password** : https://www.php.net/manual/en/function.password-hash.php
- **Sessions** : https://www.php.net/manual/en/ref.session.php
- **HTML Forms** : https://developer.mozilla.org/en-US/docs/Learn/Forms
- **CSS** : https://developer.mozilla.org/en-US/docs/Web/CSS
- **JavaScript** : https://developer.mozilla.org/en-US/docs/Web/JavaScript

---

## 📞 Support Rapide

Besoin d'aide ?

1. **Consultez l'index**
   - Chaque fichier *.md explique une partie

2. **Regardez le code**
   - Commentaires intégrés dans le PHP/JS

3. **Testez avec la checklist**
   - TESTING_CHECKLIST.md guide les tests

4. **Vérifiez la validation**
   - Model/User.php contient toutes les règles

---

## ✨ Vous Êtes Prêt !

L'application FoodSave est **complètement fonctionnelle** et prête à être utilisée.

```
POINTS CLÉS :
✅ Architecture MVC respect
✅ POO implémentée
✅ PDO pour BD
✅ Validation complète (PHP + JS)
✅ Sécurité intégrée
✅ Charte graphique respectée
✅ Documentation complète
✅ Prêt pour production
```

---

**Bienvenue dans FoodSave !** 🌍

Réduisez le gaspillage alimentaire avec nous ! 🍎
