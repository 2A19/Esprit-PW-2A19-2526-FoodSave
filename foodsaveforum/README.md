# 🌱 FoodSave Forum - Module CRUD Complet

## 📋 Vue d'ensemble

Ce module implémente un système complet de **CRUD (Create, Read, Update, Delete)** pour le forum FoodSave, avec:
- **FrontOffice**: Utilisateurs peuvent créer, lire, modifier et supprimer leurs posts et commentaires
- **BackOffice**: Administrateurs peuvent gérer, bannir ou supprimer tous les posts et commentaires

## 📁 Structure du Projet

```
foodsaveforum/
├── Model/
│   ├── Database.php          # Classe pour gérer la connexion BD
│   ├── PostModel.php         # Modèle pour les posts
│   └── CommentaireModel.php  # Modèle pour les commentaires
│
├── Controller/
│   ├── PostController.php        # Logique métier pour les posts
│   └── CommentaireController.php # Logique métier pour les commentaires
│
├── View/
│   ├── layouts/
│   │   ├── frontend.php      # Template principal FrontOffice
│   │   └── backend.php       # Template principal BackOffice
│   │
│   ├── front/
│   │   ├── posts/
│   │   │   ├── list.php      # Liste des posts
│   │   │   ├── create.php    # Créer un post
│   │   │   ├── edit.php      # Modifier un post
│   │   │   └── view.php      # Voir un post avec commentaires
│   │   │
│   │   └── commentaires/
│   │       └── edit.php      # Modifier un commentaire
│   │
│   └── back/
│       ├── posts/
│       │   ├── list.php      # Gérer tous les posts
│       │   ├── view.php      # Voir détails d'un post
│       │   └── dashboard.php # Dashboard admin
│       │
│       └── commentaires/
│           ├── list.php      # Gérer tous les commentaires
│           └── view.php      # Voir détails d'un commentaire
│
├── public/
│   └── assets/
│       ├── css/
│       │   └── style.css     # Styles avec charte graphique FoodSave
│       ├── js/
│       │   └── script.js     # Validation client et fonctionnalités JS
│       └── images/           # Images et ressources
│
├── index.php                 # Point d'entrée FrontOffice (routeur)
└── admin.php                # Point d'entrée BackOffice (routeur)
```

## 🗄️ Schéma Base de Données

### Table `posts`
```sql
CREATE TABLE posts (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    categorie VARCHAR(100),
    statue VARCHAR(20) DEFAULT 'actif',
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur)
);
```

### Table `commentaires`
```sql
CREATE TABLE commentaires (
    id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_post INT NOT NULL,
    id_utilisateur INT NOT NULL,
    statue VARCHAR(20) DEFAULT 'actif',
    FOREIGN KEY (id_post) REFERENCES posts(id_post),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur)
);
```

## 🚀 Fonctionnalités

### FrontOffice (index.php)

| Action | URL | Description |
|--------|-----|-------------|
| Voir tous les posts | `index.php?action=posts` | Affiche la liste des posts |
| Filtrer par catégorie | `index.php?action=posts&category=Recettes` | Filtre les posts par catégorie |
| Créer un post | `index.php?action=create-post` | Formulaire de création |
| Voir un post | `index.php?action=view-post&id=1` | Affiche le post et ses commentaires |
| Modifier un post | `index.php?action=edit-post&id=1` | Formulaire de modification (propriétaire seulement) |
| Supprimer un post | `index.php?action=delete-post&id=1` | Supprime le post (propriétaire seulement) |
| Ajouter un commentaire | POST à `action=store-comment` | Ajoute un commentaire au post |
| Modifier un commentaire | `index.php?action=edit-comment&id=1` | Formulaire de modification (propriétaire seulement) |
| Supprimer un commentaire | `index.php?action=delete-comment&id=1` | Supprime le commentaire (propriétaire seulement) |

### BackOffice (admin.php)

| Action | URL | Description |
|--------|-----|-------------|
| Dashboard | `admin.php?action=dashboard` | Page d'accueil admin |
| Gérer posts | `admin.php?action=posts` | Liste tous les posts (y compris bannis) |
| Voir un post | `admin.php?action=view-post&id=1` | Détails du post |
| Bannir un post | `admin.php?action=ban-post&id=1` | Bannit le post (masqué du FO) |
| Débannir un post | `admin.php?action=unban-post&id=1` | Débannit le post |
| Supprimer un post | `admin.php?action=delete-post&id=1` | Supprime définitivement |
| Gérer commentaires | `admin.php?action=commentaires` | Liste tous les commentaires |
| Voir un commentaire | `admin.php?action=view-commentaire&id=1` | Détails du commentaire |
| Bannir un commentaire | `admin.php?action=ban-commentaire&id=1` | Bannit le commentaire |
| Débannir un commentaire | `admin.php?action=unban-commentaire&id=1` | Débannit le commentaire |
| Supprimer un commentaire | `admin.php?action=delete-commentaire&id=1` | Supprime définitivement |

## 🔐 Validation & Sécurité

### Validation Côté Serveur
- **Posts**: Titre (1-255), Contenu (min 10 caractères)
- **Commentaires**: Contenu (3-2000 caractères)
- Nettoyage HTML avec `htmlspecialchars()`
- Protection contre les modifications non autorisées

### Validation Côté Client
- Validation JavaScript dans `public/assets/js/script.js`
- Attributs `data-validate` sur les inputs
- Messages d'erreur en temps réel

### Sécurité pratiquées
- ✅ Fuite d'informations sensibles prévenue
- ✅ Utilisateurs ne peuvent modifier/supprimer que leurs contenus
- ✅ PDO prepared statements contre les injections SQL

## 🎨 Charte Graphique FoodSave

**Couleurs principales:**
- 🟢 Vert (Écologie): `#4CAF50`
- 🟠 Orange (Énergie): `#FFA726`
- ⚪ Blanc: `#FFFFFF`
- ⚫ Gris: `#F5F5F5`

**Catégories de posts avec badges:**
- 🍳 Recettes (Vert clair)
- 💡 Astuces (Orange clair)
- ❓ Questions (Bleu)
- 📋 Conseils (Rose)
- 🔖 Autre (Mauve)

## 📝 Utilisation

### 1. Configuration BD

Mettez à jour `Model/Database.php` avec vos credentials:

```php
private $host = 'localhost';
private $db_name = 'foodsave_forum';
private $user = 'root';
private $password = '';
```

### 2. Accéder au Forum

- **FrontOffice**: `http://localhost/foodsaveforum/index.php`
- **BackOffice**: `http://localhost/foodsaveforum/admin.php`

### 3. Authentification

Le système utilise une session simple (à améliorer en prod):

```php
$_SESSION['user_id'] = 1;
$_SESSION['is_admin'] = true;
```

## ✨ Caractéristiques Clés

### CRUD Complet ✅
- ✅ **Create**: Créer posts et commentaires
- ✅ **Read**: Lister et visualiser
- ✅ **Update**: Modifier les contenus
- ✅ **Delete**: Supprimer les contenus

### Contrôle Saisie ✅
- ✅ Validation formulaires HTML
- ✅ Validation JavaScript
- ✅ Validation serveur PHP
- ✅ Messages d'erreur clairs

### Templates Intégrés ✅
- ✅ Layout FrontOffice responsive
- ✅ Layout BackOffice avec stats
- ✅ Cartes de posts élégantes
- ✅ Tableau de gestion admin

### Bannissement ✅
- ✅ L'admin peut bannir les posts (masqués du FO)
- ✅ L'admin peut bannir les commentaires
- ✅ Débannissement possible
- ✅ Suppression définitive option

## 🔧 Améliorations Futures

- [ ] Système d'authentification complet
- [ ] Rôles et permissions plus granulaires
- [ ] Pagination des listes
- [ ] Recherche et filtres avancés
- [ ] Notifications
- [ ] Modération intelligente
- [ ] Système de notation/votes
- [ ] API RESTful

## 📞 Notes

- Le système est conçu pour les sessions PHP
- À adapter pour une vraie authentification utilisateur
- Tests recommandés avant production

---

**Créé pour FoodSave | Module Forum par [Vous] | 2026** 🌿
