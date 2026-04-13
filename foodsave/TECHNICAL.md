# Documentation Technique - FoodSave

## 🏗️ Architecture Globale

FoodSave suit le pattern **MVC (Model-View-Controller)** en PHP Orienté Objet.

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENT (Navigateur)                  │
│                    (HTML/CSS/JavaScript)                     │
└─────────────────────────────────────────────────────────────┘
                              │
                         HTTP/HTTPS
                              │
┌─────────────────────────────────────────────────────────────┐
│                     SERVER (Apache/PHP)                      │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │  Routeur (index.php / admin.php)                   │    │
│  │  - Reçoit les requêtes                             │    │
│  │  - Achemine vers le bon Controller                 │    │
│  └────────────────────┬────────────────────────────────┘    │
│                       │                                       │
│  ┌────────────────────▼────────────────────────────────┐    │
│  │  Controller (UserController.php)                   │    │
│  │  - Traite la logique métier                        │    │
│  │  - Appelle les Models                              │    │
│  │  - Charge les Views                                │    │
│  └────────┬─────────────────────────────────┬─────────┘    │
│           │                                 │                │
│  ┌────────▼──────────┐     ┌────────────────▼─────┐       │
│  │ Model (User.php)  │     │ View (HTML Files)     │       │
│  │ - Validation      │     │ - Affichage           │       │
│  │ - CRUD            │     │ - Formulaires         │       │
│  │ - BD (PDO)        │     │ - Messages            │       │
│  └────────┬──────────┘     └───────────────────────┘       │
│           │                                                  │
│  ┌────────▼──────────────────────────────────────────┐    │
│  │  Database (MySQL - foodsave_db)                  │    │
│  │  - Table: users                                   │    │
│  └───────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

---

## 📂 Arborescence des Fichiers

```
foodsave/
│
├── 📄 index.php                    # Point d'entrée FrontOffice
├── 📄 admin.php                    # Point d'entrée BackOffice
├── 📄 home.html                    # Page d'accueil
│
├── 📁 config/
│   └── 📄 Database.php            # Configuration PDO + classe Database
│
├── 📁 Model/
│   └── 📄 User.php                # Modèle User (validation + CRUD)
│
├── 📁 Controller/
│   └── 📄 UserController.php      # Contrôleur User (logique métier)
│
├── 📁 View/
│   ├── 📁 Front/
│   │   └── 📁 user/
│   │       ├── 📄 login.html              # Page de connexion
│   │       ├── 📄 register.html           # Page d'inscription
│   │       ├── 📄 dashboard.html          # Tableau de bord utilisateur
│   │       ├── 📄 profile.html            # Profil utilisateur
│   │       └── 📄 edit_profile.html       # Modification du profil
│   │
│   └── 📁 Back/
│       └── 📁 user/
│           ├── 📄 admin_dashboard.html    # Tableau de bord admin
│           ├── 📄 users_list.html         # Liste des utilisateurs
│           ├── 📄 user_details.html       # Détails utilisateur
│           └── 📄 edit_user.html          # Édition utilisateur (admin)
│
├── 📁 assets/
│   ├── 📁 css/
│   │   └── 📄 style.css            # Styles globaux (charte graphique)
│   ├── 📁 js/
│   │   └── 📄 validation.js        # Validations côté client
│   └── 📁 images/
│       └── (Images, logos, etc.)
│
├── 📄 database_setup.sql           # Script d'initialisation BD
├── 📄 README.md                    # Documentation générale
├── 📄 INSTALL.md                   # Guide d'installation
├── 📄 API.md                       # Documentation des routes
└── 📄 TECHNICAL.md                 # Cette documentation

```

---

## 🔌 Flux de Requête - Exemple: Connexion Utilisateur

```
1. Client clique sur "Se connecter"
   └─ Form submit vers index.php?action=handleLogin

2. index.php reçoit la requête
   └─ Appelle UserController::handleLogin()

3. UserController::handleLogin()
   ├─ Récupère email et password du POST
   ├─ Crée instance de User
   └─ Appelle User::login($email, $password)

4. User::login() (Model)
   ├─ Prépare requête SQL (PDO)
   ├─ Cherche l'utilisateur par email
   ├─ Vérifie le mot de passe (password_verify)
   └─ Retourne les données utilisateur ou false

5. UserController traite le résultat
   ├─ Si succès
   │   ├─ Crée la session $_SESSION['user_*']
   │   └─ Redirection vers dashboard
   └─ Si erreur
       ├─ Stocke les erreurs
       └─ R-affiche la page login

6. Client reçoit la réponse
   └─ Affichage du tableau de bord ou des erreurs
```

---

## 🔐 Classe Database (config/Database.php)

La classe `Database` gère la connexion à MySQL en utilisant **PDO** (PHP Data Objects).

### Code Clé

```php
class Database {
    // Configuration
    private $host = 'localhost';
    private $db_name = 'foodsave_db';
    private $user = 'root';
    private $password = '';
    
    // Connexion PDO
    private $connection;

    // Créer la connexion
    public function connect() {
        try {
            $this->connection = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->user,
                $this->password
            );
            // Mode erreur strict
            $this->connection->setAttribute(
                PDO::ATTR_ERRMODE, 
                PDO::ERRMODE_EXCEPTION
            );
        } catch (PDOException $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
        return $this->connection;
    }
}
```

### Utilisation

```php
// Dans le Model
$database = new Database();
$this->db = $database->connect();

// Requête préparée
$query = 'SELECT * FROM users WHERE email = :email';
$stmt = $this->db->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

---

## 👤 Classe User (Model/User.php)

La classe `User` gère la logique métier des utilisateurs.

### Propriétés Principales

```php
public $id;              // ID de l'utilisateur
public $firstname;       // Prénom
public $lastname;        // Nom
public $email;          // Email
public $password;       // Mot de passe (hashé en BD)
public $role;           // 'user' ou 'admin'
public $type;           // 'individual' ou 'startup'
public $company_name;   // Nom entreprise (startup)
public $errors = [];    // Erreurs de validation
```

### Méthodes Principales

#### `validate()`
Valide les données selon les règles métier.

```php
public function validate() {
    // Valide prénom, nom, email, password, type, company_name
    // Retourne true/false
    // Remplit $this->errors en cas d'erreur
}
```

#### `create()`
Crée un nouvel utilisateur après validation.

```php
public function create() {
    if (!$this->validate()) return false;
    
    // Hash du mot de passe
    $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    
    // Insertion en BD via PDO
    // Retourne true/false
}
```

#### `login($email, $password)`
Authentifie l'utilisateur.

```php
public function login($email, $password) {
    // Cherche l'utilisateur par email
    // Vérifie le mot de passe
    // Retourne les données de l'utilisateur ou false
}
```

#### `getAll()`, `getById($id)`
Récupère les utilisateurs de la BD.

#### `update($id)`, `delete($id)`
Mises à jour et suppressions.

#### `changeRole($id, $role)`
Change le rôle d'un utilisateur (admin).

---

## 🎮 Classe UserController (Controller/UserController.php)

Le contrôleur orchestre les interactions entre les Models et les Views.

### Structure

```php
class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // Méthodes pour chaque action
    public function login()
    public function handleLogin()
    public function register()
    public function handleRegister()
    public function dashboard()
    public function profile()
    public function editProfile()
    public function handleEditProfile()
    
    // Admin
    public function adminDashboard()
    public function usersList()
    public function userDetails()
    public function editUser()
    public function handleEditUser()
    public function changeUserRole()
    public function deleteUser()
    
    public function logout()
}
```

### Pattern Utilisé

#### Affichage Simple
```php
public function login() {
    include '../View/Front/user/login.html';
}
```

#### Traitement POST
```php
public function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?action=login');
        exit;
    }

    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    $user = $this->userModel->login($email, $password);

    if ($user) {
        // Créer la session
        session_start();
        $_SESSION['user_id'] = $user['id'];
        // ...
        header('Location: index.php?action=dashboard');
    } else {
        // Afficher les erreurs
        $errors = $this->userModel->errors;
        include '../View/Front/user/login.html';
    }
}
```

---

## 🛡️ Sécurité

### 1. **Injection SQL** ✅ Prévention

```php
// ❌ DANGEREUX (Variables interpolées)
$query = "SELECT * FROM users WHERE email = '$email'";

// ✅ SÛRE (Requête paramétrée PDO)
$query = "SELECT * FROM users WHERE email = :email";
$stmt = $this->db->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();
```

### 2. **XSS (Cross-Site Scripting)** ✅ Prévention

```php
// ❌ DANGEREUX
echo $_POST['firstname'];  // Script malveillant peut s'exécuter

// ✅ SÛRE
echo htmlspecialchars($_POST['firstname']);
```

### 3. **Mots de Passe** ✅ Sécurisé

```php
// Hash du mot de passe lors de l'inscription
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Vérification lors du login
$isValid = password_verify($password, $hashedPassword);
```

### 4. **Sessions** ✅ Protégées

```php
// Vérifier si l'utilisateur est connecté
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}

// Vérifier si l'utilisateur est admin
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: index.php?action=login');
    exit;
}
```

### 5. **CORS/CSRF** ⚠️ À Améliorer

Pour un projet production, ajouter des tokens CSRF :

```php
// Générer un token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Vérifier le token dans les formulaires POST
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Token CSRF invalide');
}
```

---

## 📊 Base de Données

### Schéma Table `users`

```sql
CREATE TABLE users (
    id                INT PRIMARY KEY AUTO_INCREMENT,
    firstname         VARCHAR(100) NOT NULL,
    lastname          VARCHAR(100) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    password          VARCHAR(255) NOT NULL,
    role              ENUM('user', 'admin') DEFAULT 'user',
    type              ENUM('individual', 'startup') DEFAULT 'individual',
    company_name      VARCHAR(255) NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
);
```

### Indices

Les index optimisent les requêtes fréquentes :
- `email` : Recherche rapide lors du login/inscription
- `role` : Filtrage admin/user
- `type` : Filtrage individual/startup
- `created_at` : Tri par date

---

## 🎨 Styles CSS (assets/css/style.css)

### Variables CSS Principales

```css
:root {
    --color-primary-green: #4CAF50;
    --color-primary-orange: #FFA726;
    --color-white: #FFFFFF;
    --color-light-gray: #F5F5F5;
    --color-dark-gray: #333333;
}
```

### Architecture CSS

1. **Réinitialisation** : Reset des styles par défaut
2. **Typographie** : Polices, tailles de texte
3. **Composants** : Boutons, formulaires, alertes
4. **Mises en page** : Grid, flexbox
5. **Responsive** : Media queries pour mobile

### Design Responsive

```css
/* Desktop first */
@media (max-width: 768px) {
    /* Styles pour tablette */
}

@media (max-width: 480px) {
    /* Styles pour mobile */
}
```

---

## 🚀 JavaScript (assets/js/validation.js)

### Classe FormValidator

Valide les formulaires côté client en temps réel.

```javascript
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.errors = {};
        this.setupForm();
    }

    setupForm() {
        // Validation au submit
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.validateForm();
            if (Object.keys(this.errors).length === 0) {
                this.form.submit();
            }
        });

        // Validation en temps réel (blur/change)
        const inputs = this.form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
        });
    }

    validateField(input) {
        // Validation selon le type de champ
        // Email, password, text, tel, number
    }
}
```

---

## 📱 Responsive Design

L'application est optimisée pour 3 breakpoints :

| Taille | Usage | Breakpoint |
|--------|-------|------------|
| Desktop | Écrans larges | > 768px |
| Tablet | Tablettes | 481px - 768px |
| Mobile | Smartphones | < 480px |

---

## 🔄 Cycle de Vie d'une Requête

### 1. Requête Reçue
```
HTTP POST /index.php?action=handleLogin
```

### 2. Routage (index.php)
```php
$action = $_GET['action'];
switch ($action) {
    case 'handleLogin':
        $controller->handleLogin();
}
```

### 3. Traitement (Controller)
```php
public function handleLogin() {
    // Valider les données POST
    // Appeler le Model
    // Gérer le résultat
    // Charger une View ou rediriger
}
```

### 4. Logique Métier (Model)
```php
public function login($email, $password) {
    // Accès à la BD via PDO
    // Vérification des données
    // Retour au Controller
}
```

### 5. Affichage (View)
```php
include '../View/Front/user/login.html';
```

### 6. Réponse HTTP
```
HTTP 200 ou 302 (redirect)
Content-Type: text/html; charset=utf-8
```

---

## 🧪 Tests Manuels

### Test de Login

1. **Credentials valides**
   - Email: admin@foodsave.com
   - Password: Admin123456
   - Résultat: ✅ Redirection dashboard

2. **Credentials invalides**
   - Email: admin@foodsave.com
   - Password: WrongPassword
   - Résultat: ❌ Erreur affichée

3. **Email non existant**
   - Email: nonexistent@example.com
   - Password: Password123
   - Résultat: ❌ Erreur affichée

### Test de Registration

1. Inscription valide
2. Email déjà existant
3. Mot de passe faible
4. Type startup sans nom (erreur)

---

## 📈 Performance

### Optimisations Implémentées

1. **Indices BD** : Sur email, role, type, created_at
2. **CSS Minifié** : Réduire la taille des fichiers
3. **JavaScript Minifié** : Réduire la latence
4. **Lazy Loading** : Images chargées à la demande

### À Améliorer

- [ ] Caching (Redis)
- [ ] Compression GZIP
- [ ] CDN pour les assets statiques
- [ ] Pagination pour les listes
- [ ] Requêtes précompiléesPreparée

---

## 📚 Ressources Supplémentaires

- **PDO** : https://www.php.net/manual/en/class.pdo.php
- **Password Hash** : https://www.php.net/manual/en/function.password-hash.php
- **HTML5 Forms** : https://developer.mozilla.org/en-US/docs/Learn/Forms
- **CSS Grid** : https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Grid_Layout
- **JavaScript Forms** : https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Forms

---

## 📝 Notes de Développement

### Conventions de Coding

- **Noms de classe** : PascalCase (User, Database)
- **Noms de méthode** : camelCase (handleLogin, getUserById)
- **Noms de constante** : UPPER_CASE (DB_HOST, MAX_ATTEMPTS)
- **Indentation** : 4 espaces

### Commentaires

```php
/**
 * Description de la méthode
 * 
 * @param type $param Description du paramètre
 * @return type Description du retour
 */
public function methodName($param) {
    // Logique...
}
```

### Gestion des Erreurs

```php
try {
    // Code qui peut lever une exception
} catch (PDOException $e) {
    // Gestion de l'erreur
    error_log($e->getMessage());
}
```

---

Dernière mise à jour: 11 avril 2026  
Maintenant vous avez une documentation technique complète ! 🎉
