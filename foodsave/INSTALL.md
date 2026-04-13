# Guide d'Installation - FoodSave

## ⚙️ Étapes d'Installation Complètes

### ÉTAPE 1 : Préparation de l'Environnement

#### 1.1 Vérifier XAMPP
- Téléchargez XAMPP depuis https://www.apachefriends.org/
- Installez-le dans `C:\xampp\`
- Assurez-vous d'avoir Apache et MySQL sélectionnés

#### 1.2 Démarrer les Services
1. Ouvrez le **XAMPP Control Panel**
2. Cliquez sur **Start** pour **Apache**
3. Cliquez sur **Start** pour **MySQL**

Vous devriez voir :
```
Apache: Running
MySQL: Running
```

---

### ÉTAPE 2 : Préparer les Fichiers du Projet

#### 2.1 Créer le Dossier Projet
1. Ouvrez l'explorateur de fichiers
2. Allez à `C:\xampp\htdocs\`
3. Créez un dossier nommé `foodsave`

#### 2.2 Copier les Fichiers
Assurez-vous que tous les fichiers du projet sont dans `C:\xampp\htdocs\foodsave\`

Structure finale :
```
C:\xampp\htdocs\foodsave\
├── Controller/
│   └── UserController.php
├── Model/
│   └── User.php
├── View/
├── config/
├── assets/
├── index.php
├── admin.php
└── database_setup.sql
```

---

### ÉTAPE 3 : Configuration de la Base de Données

#### Option A : Via phpMyAdmin (Recommandé pour les Débutants)

1. **Ouvrir phpMyAdmin**
   - Allez à http://localhost/phpmyadmin
   - Vous devriez voir l'interface phpMyAdmin

2. **Importer la Base de Données**
   - Cliquez sur l'onglet **"Importer"**
   - Cliquez sur **"Choisir un fichier"**
   - Sélectionnez `database_setup.sql` depuis `C:\xampp\htdocs\foodsave\`
   - Laissez le jeu de caractères sur **"utf8mb4_unicode_ci"**
   - Cliquez sur **"Importer"**

3. **Vérifier la Création**
   - Vous devriez voir un message de succès
   - Dans la liste de gauche, vous devriez voir **"foodsave_db"**

#### Option B : Via le Terminal MySQL

1. **Ouvrir l'Invite de Commande / PowerShell**
2. **Naviguer vers MySQL**
   ```bash
   cd C:\xampp\mysql\bin
   ```

3. **Exécuter le Script**
   ```bash
   mysql -u root -p < C:\xampp\htdocs\foodsave\database_setup.sql
   ```
   
4. **Quand demandé, laisser le mot de passe vide et appuyer sur Entrée**

---

### ÉTAPE 4 : Vérifier la Configuration

#### 4.1 Vérifier la Connexion à la Base de Données

1. Ouvrez le fichier `config/Database.php`
2. Vérifiez que les paramètres sont corrects :

```php
private $host = 'localhost';      // ✅ Correct
private $db_name = 'foodsave_db'; // ✅ Correct
private $user = 'root';           // ✅ Correct
private $password = '';           // ✅ Correct (vide par défaut)
```

#### 4.2 Vérifier les Permissions des Fichiers

Pour Windows, vous n'avez généralement rien à faire. Pour Linux/Mac :

```bash
chmod 755 foodsave/
chmod 755 foodsave/config/
chmod 755 foodsave/View/
```

---

### ÉTAPE 5 : Lancer l'Application

#### 5.1 Accéder au FrontOffice

1. Ouvrez votre navigateur web
2. Allez à : **http://localhost/foodsave/index.php**
3. Vous devriez voir la page de **Login**

#### 5.2 Accéder au BackOffice (Admin)

1. Ouvrez votre navigateur web
2. Allez à : **http://localhost/foodsave/admin.php**
3. Vous serez redirigé à la page de login
4. Connectez-vous avec le compte admin

---

### ÉTAPE 6 : Premier Accès

#### Compte Administrateur Par Défaut

```
Email : admin@foodsave.com
Mot de passe : Admin123456
```

**Procédure :**
1. Allez à http://localhost/foodsave/index.php
2. Cliquez sur "Connexion"
3. Entrez les identifiants ci-dessus
4. Vous accédez à l'interface Admin

#### Créer un Compte Utilisateur

1. Cliquez sur "S'inscrire"
2. Remplissez les informations :
   - Prénom : Jean
   - Nom : Dupont
   - Email : jean@example.com
   - Mot de passe : Jean12345 (min 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre)
   - Type : Utilisateur particulier
3. Cliquez sur "S'inscrire"
4. Vous pouvez maintenant vous connecter

#### Comptes Test Disponibles

```
1. Admin
   Email : admin@foodsave.com
   Mot de passe : Admin123456
   
2. Utilisateur Particulier
   Email : user@foodsave.com
   Mot de passe : User@12345
   
3. Startup
   Email : startup@foodsave.com
   Mot de passe : Startup123456
```

---

### ÉTAPE 7 : Test des Fonctionnalités

#### Test du FrontOffice

- [ ] Login avec un compte utilisateur
- [ ] Voir le tableau de bord
- [ ] Consulter le profil
- [ ] Modifier le profil
- [ ] Tester la validation des formulaires
- [ ] Se déconnecter

#### Test du BackOffice

- [ ] Login avec le compte admin
- [ ] Voir le tableau de bord admin
- [ ] Accéder à la liste des utilisateurs
- [ ] Consulter les détails d'un utilisateur
- [ ] Modifier un utilisateur
- [ ] Changer le rôle d'un utilisateur
- [ ] Supprimer un utilisateur

#### Test de la Validation

- [ ] Essayez de soumettre un formulaire vide
- [ ] Entrez un email invalide
- [ ] Entrez un mot de passe court
- [ ] Entrez un prénom avec des caractères spéciaux
- [ ] Testez le changement de type (particulier/startup)

---

### ÉTAPE 8 : Troubleshooting

#### Problème : "Connection refused" ou erreur de base de données

**Solutions :**
1. Vérifiez que MySQL est démarré dans XAMPP
2. Vérifiez les identifiants dans `config/Database.php`
3. Vérifiez que la base de données `foodsave_db` existe dans phpMyAdmin
4. Utilisez phpMyAdmin pour tester la connexion

#### Problème : Erreur 404 ou page non trouvée

**Solutions :**
1. Vérifiez l'URL : http://localhost/foodsave/index.php
2. Vérifiez que le dossier foodsave est bien dans `C:\xampp\htdocs\`
3. Redémarrez Apache

#### Problème : Fichiers CSS/JS non chargés

**Solutions :**
1. Vérifiez les chemins relatifs dans les fichiers HTML
2. Vérifiez que les fichiers CSS/JS existent dans `assets/css/` et `assets/js/`
3. Ouvrez la console du navigateur (F12) pour voir les erreurs 404

#### Problème : Sessions ne persistent pas

**Solutions :**
1. Assurez-vous que `session_start()` est appelé en haut de chaque fichier PHP
2. Vérifiez que les cookies sont activés dans votre navigateur
3. Vérifiez les permissions du dossier de sessions PHP

#### Problème : Validation JavaScript ne fonctionne pas

**Solutions :**
1. Ouvrez la console du navigateur (F12)
2. Vérifiez qu'il n'y a pas d'erreurs JavaScript
3. Vérifiez que `validation.js` est correctement chargé
4. Vérifiez que les IDs des formulaires correspondent dans les fichiers HTML

---

## 📋 Checklist d'Installation

- [ ] XAMPP installé et Services démarrés
- [ ] Dossier foodsave créé dans `C:\xampp\htdocs\`
- [ ] Tous les fichiers copiés
- [ ] Base de données importée avec `database_setup.sql`
- [ ] Configuration Database.php vérifiée
- [ ] FrontOffice accessible (http://localhost/foodsave/index.php)
- [ ] BackOffice accessible (http://localhost/foodsave/admin.php)
- [ ] Login avec compte admin fonctionne
- [ ] Inscription nouvel utilisateur fonctionne
- [ ] Modification profil fonctionne
- [ ] Validation des formulaires fonctionne
- [ ] Gestion admin (liste, édition, suppression) fonctionne

---

## 🎯 Prochaines Étapes

Après l'installation réussie :

1. Testez tous les comptes disponibles
2. Créez de nouveaux utilisateurs pour tester
3. Testez les validations des formulaires
4. Testez l'interface administrateur
5. Explorez les fonctionnalités du code

---

## 📞 Support Rapide

En cas de problème, consultez :
- Le fichier `README.md` pour plus de détails
- Les commentaires dans le code source
- La console du navigateur (F12) pour les erreurs

---

**Bonne installation ! 🚀**
