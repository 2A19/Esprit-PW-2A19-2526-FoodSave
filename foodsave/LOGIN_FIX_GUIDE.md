# 🔧 GUIDE DE FIX - LOGIN AUTHENTICATION

## Problème Identifié

Le système de login ne fonctionnait pas parce que:
- ❌ Les mots de passe n'étaient pas correctement hachés en base de données
- ❌ Le code applicait `htmlspecialchars()` sur le mot de passe, ce qui pouvait causer des problèmes
- ❌ Les tables de base de données n'avaient pas été créées

## ✅ Solutions Appliquées

### 1. **Code Corrigé** ✅
- Modification du fichier `Controller/UserController.php`
  - Suppression de `htmlspecialchars()` sur le mot de passe
  - Ajout de validation des champs vides
  - Ajout de vérification du statut utilisateur
  - Amélioration du message d'erreur

### 2. **Scripts de Débogage Créés**

#### `debug_login.php`
Diagnostic complet du système de login:
```bash
# Vérifier la connexion BD
# Lister les utilisateurs
# Tester les identifiants
# Afficher les erreurs

http://localhost/foodsave/debug_login.php
```

#### `reset_passwords.php`
Réinitialise les mots de passe avec hash correct:
```bash
# Hache correctement les mots de passe
# Crée les utilisateurs s'ils n'existent pas
# Affiche les credentials valides

http://localhost/foodsave/reset_passwords.php
```

## 📋 ÉTAPES À SUIVRE

### Étape 1: Créer la Base de Données ⚠️ IMPORTANT
```bash
# Terminal / Command Prompt
cd c:\xampp\htdocs\foodsave

# Exécuter le script SQL
mysql -u root < database_setup.sql

# OU manuellement dans phpMyAdmin:
# 1. Créer nouvelle DB: foodsave_db
# 2. Importer: database_setup.sql
```

**Test:**
- ✅ Ouvrir phpMyAdmin → foodsave_db
- ✅ Vérifier que les tables existent (user, aliments, listes, etc.)

---

### Étape 2: Réinitialiser les Mots de Passe
```bash
# Via le navigateur:
http://localhost/foodsave/reset_passwords.php

# Vous verrez:
# ✅ admin@foodsave.com: Mot de passe mis à jour
# ✅ user@foodsave.com: Mot de passe mis à jour
# ✅ test@foodsave.com: Mot de passe mis à jour
```

---

### Étape 3: Déboguer (Optionnel)
```bash
http://localhost/foodsave/debug_login.php

# Vérifications:
# ✅ Connexion BD réussie
# ✅ Utilisateurs en BD trouvés
# ✅ TEST CREDENTIALS: LOGIN VALIDE ✓
```

---

### Étape 4: Tester le Login

Ouvrir: `http://localhost/foodsave/index.php?action=login`

**Essayer ces identifiants:**

| Email | Password | Rôle |
|-------|----------|------|
| admin@foodsave.com | Admin@12345 | Admin |
| user@foodsave.com | User@12345 | User |
| test@foodsave.com | Test@12345 | User |

---

## 🚀 RÉSUMÉ QUICK FIX

```bash
# 1. Créer BD (Terminal)
mysql -u root < database_setup.sql

# 2. Réinitialiser passwords (Navigateur)
http://localhost/foodsave/reset_passwords.php

# 3. Tester login
http://localhost/foodsave/index.php?action=login
```

---

## 💡 EXPLICATIONS TECHNIQUES

### Pourquoi ça ne marchait pas?

1. **Mots de passe en texte brut**: Si les mots de passe n'étaient pas hachés, `password_verify()` retournerait false
   
2. **htmlspecialchars() sur le password**: Convertit les caractères spéciaux, ce qui peut corrompre le hash. Exemple:
   ```
   Password brut: "Pass&word"
   Après htmlspecialchars(): "Pass&amp;word"  ← Différent!
   ```

3. **Tables n'existent pas**: Si `database_setup.sql` n'était pas exécuté, les tables n'existaient pas en BD

### Comment fonctione `password_verify()`?

```php
// Stockage (hachage)
$password_hash = password_hash('User@12345', PASSWORD_BCRYPT);
// Résultat: $2y$10$1qxZMUJ3B8ZwQb8g.Kz6XeJa8fJd8KqXjKfYvUwQ9Yl8hK5YO2Wry

// Vérification (login)
$is_valid = password_verify('User@12345', $password_hash);  // true
$is_valid = password_verify('wrong', $password_hash);       // false
```

---

## 🆘 SI TOUJOURS PAS DE LOGIN

### Vérifications:

1. **Base de données créée?**
   ```bash
   mysql -u root
   SHOW DATABASES;  # Vérifier foodsave_db existe
   USE foodsave_db;
   SHOW TABLES;     # Vérifier user table existe
   SELECT * FROM user;  # Vérifier les données
   ```

2. **Fichier config/Database.php correct?**
   ```php
   // Vérifier:
   // - Host: localhost
   // - Database: foodsave_db
   // - User: root
   // - Password: (vide pour XAMPP)
   ```

3. **Exécuter le script de debug:**
   ```bash
   http://localhost/foodsave/debug_login.php
   # Chercher les ✅ (succès) ou ❌ (erreurs)
   ```

---

## 📝 FICHIERS MODIFIÉS

✅ `Controller/UserController.php` - Fonction handleLogin() corrigée
✅ `reset_passwords.php` - Nouveau script pour hacher les mots de passe
✅ `debug_login.php` - Nouveau script de diagnostic

---

**✨ Une fois ces étapes complétées, le login devrait fonctionner correctement!**
