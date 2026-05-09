# 📊 FoodSave - Jointures et Relations Base de Données

## 🏗️ Structure Complète de la Base de Données

```
┌─────────────────────────────────────────────────────────┐
│                   STRUCTURE DES TABLES                   │
└─────────────────────────────────────────────────────────┘

┌──────────────────┐
│      USER        │
├──────────────────┤
│ id (PK)          │
│ nom              │
│ prenom           │
│ email (UNIQUE)   │
│ password         │
│ telephone        │
│ date_naissance   │
│ role             │
│ statut           │
│ date_inscription │
└──────────────────┘
       │
       │ 1:N
       └─────────────────────────┐
                                  │
                    ┌─────────────────────────┐
                    │      LISTES (1:N)       │
                    ├─────────────────────────┤
                    │ id (PK)                 │
                    │ user_id (FK → user)     │
                    │ titre                   │
                    │ type                    │
                    │ statut                  │
                    │ date_creation           │
                    │ date_modification       │
                    └─────────────────────────┘
                             │
                             │ 1:N
                             └──────────────────┐
                                                 │
                        ┌────────────────────────────────────┐
                        │   ARTICLES_LISTE (N:N bridge)      │
                        ├────────────────────────────────────┤
                        │ id (PK)                            │
                        │ liste_id (FK → listes)             │
                        │ aliment_id (FK → aliments)         │
                        │ quantite                           │
                        │ unite                              │
                        │ statut                             │
                        │ date_ajout                         │
                        └────────────────────────────────────┘
                                    │
                                    │
        ┌───────────────────────────┴───────────────────────────┐
        │                                                        │
        └─────────────┬──────────────────────┬─────────────┐
                      │                      │             │
    ┌─────────────────────────┐   ┌──────────────────────┐ │
    │     ALIMENTS (FK)       │   │   RECETTES           │ │
    ├─────────────────────────┤   ├──────────────────────┤ │
    │ id (PK)                 │   │ id (PK)              │ │
    │ nom                     │   │ titre                │ │
    │ categorie_id (FK)       │   │ description          │ │
    │ description             │   │ temps_preparation    │ │
    │ calories_100g           │   │ temps_cuisson        │ │
    │ conservation_jours      │   │ portions             │ │
    │ date_creation           │   │ difficulte           │ │
    └─────────────────────────┘   └──────────────────────┘
         │                                  │
         │ N:1                              │ 1:N
         └──────────┬───────────────────────┘
                    │
         ┌──────────────────────────────────┐
         │  INGREDIENTS_RECETTE (N:N)       │
         ├──────────────────────────────────┤
         │ id (PK)                          │
         │ recette_id (FK → recettes)       │
         │ aliment_id (FK → aliments)       │
         │ quantite                         │
         │ unite                            │
         └──────────────────────────────────┘

         
    ┌─────────────────────────┐
    │    CATEGORIES           │
    ├─────────────────────────┤
    │ id (PK)                 │
    │ nom                     │
    │ description             │
    │ created_at              │
    └─────────────────────────┘
         ▲
         │
         │ N:1 (FK)
         │
    ┌─────────────────────────┐
    │      ALIMENTS           │
    └─────────────────────────┘
```

---

## 🔗 Types de Jointures Implémentées

### 1. **LEFT JOIN Simple** (User + Listes)
```sql
SELECT u.*, COUNT(l.id) as nombre_listes
FROM user u
LEFT JOIN listes l ON u.id = l.user_id
GROUP BY u.id
```
**Usage**: Afficher tous les utilisateurs avec le nombre de listes

**Contrôleur**: `UserController::listUsers()`

---

### 2. **INNER JOIN + LEFT JOIN** (Listes + Articles + Aliments)
```sql
SELECT l.*, al.id, a.nom, c.nom as categorie
FROM listes l
INNER JOIN user u ON l.user_id = u.id
LEFT JOIN articles_liste al ON l.id = al.liste_id
LEFT JOIN aliments a ON al.aliment_id = a.id
LEFT JOIN categories c ON a.categorie_id = c.id
WHERE l.id = ?
```
**Usage**: Récupérer une liste avec tous ses articles détaillés

**Contrôleur**: `ListeController::getListeDetailsWithArticles()`

---

### 3. **INNER JOIN + LEFT JOIN** (Recettes + Ingredients + Aliments)
```sql
SELECT r.*, ir.id, a.nom, c.nom, ir.quantite
FROM recettes r
LEFT JOIN ingredients_recette ir ON r.id = ir.recette_id
LEFT JOIN aliments a ON ir.aliment_id = a.id
LEFT JOIN categories c ON a.categorie_id = c.id
WHERE r.id = ?
```
**Usage**: Récupérer une recette avec tous ses ingrédients

**Contrôleur**: `RecetteController::getRecetteWithIngredients()`

---

### 4. **Multi-JOIN avec GROUP BY** (Aliments les plus utilisés)
```sql
SELECT a.id, a.nom, c.nom as categorie,
       COUNT(al.id) as nombre_utilisations,
       COUNT(DISTINCT l.user_id) as nombre_utilisateurs
FROM aliments a
LEFT JOIN categories c ON a.categorie_id = c.id
LEFT JOIN articles_liste al ON a.id = al.aliment_id
LEFT JOIN listes l ON al.liste_id = l.id
GROUP BY a.id
ORDER BY nombre_utilisations DESC
LIMIT 10
```
**Usage**: Afficher les aliments les plus utilisés

**Contrôleur**: `UserController::getTopAliments()`

---

### 5. **Complex JOIN avec CASE** (Statistiques utilisateur)
```sql
SELECT u.id, u.prenom,
       COUNT(DISTINCT l.id) as total_listes,
       COUNT(DISTINCT CASE WHEN al.statut = 'achete' THEN al.id END) as articles_achetes,
       COUNT(DISTINCT CASE WHEN l.type = 'courses' THEN l.id END) as listes_courses
FROM user u
LEFT JOIN listes l ON u.id = l.user_id AND l.statut = 'active'
LEFT JOIN articles_liste al ON l.id = al.liste_id
WHERE u.id = ?
GROUP BY u.id
```
**Usage**: Obtenir les statistiques complètes d'un utilisateur

**Contrôleur**: `UserController::getUserStatistics()`

---

### 6. **Full System Statistics** (All tables)
```sql
SELECT COUNT(DISTINCT u.id) as total_utilisateurs,
       COUNT(DISTINCT l.id) as total_listes,
       COUNT(DISTINCT r.id) as total_recettes,
       COUNT(DISTINCT a.id) as total_aliments
FROM user u
LEFT JOIN listes l ON u.id = l.user_id
LEFT JOIN recettes r ON 1=1
LEFT JOIN aliments a ON 1=1
```
**Usage**: Dashboard admin - Statistiques globales

**Contrôleur**: `UserController::getSystemStatistics()`

---

### 7. **LEFT JOIN avec LEFT JOIN** (Aliments par catégorie)
```sql
SELECT a.id, a.nom, a.categorie_id, c.nom as categorie_nom,
       a.description, a.calories_100g
FROM aliments a
LEFT JOIN categories c ON a.categorie_id = c.id
WHERE a.categorie_id = ?
ORDER BY a.nom
```
**Usage**: Afficher les aliments d'une catégorie

**Contrôleur**: `AlimentController::getAlimentsByCategory()`

---

## 📋 Récapitulatif des Jointures par Contrôleur

### **UserController** (Avec jointures)
```php
1. listUsers()                    // LEFT JOIN listes, articles
2. getUserStatistics()            // LEFT JOIN listes, articles + GROUP BY
3. getSystemStatistics()          // LEFT JOINs multiples
4. getTopAliments()               // LEFT JOIN articles + catégories
5. getCompleteUserData()          // Multi-JOIN complexe
```

### **AlimentController** (Avec jointures)
```php
1. getAllAliments()               // LEFT JOIN categories
2. getAlimentsByCategory()        // LEFT JOIN categories
3. getAlimentById()               // LEFT JOIN categories
```

### **ListeController** (Avec jointures)
```php
1. getListesByUser()              // INNER JOIN user, LEFT JOIN articles
2. getListeDetailsWithArticles()  // INNER JOIN user, LEFT JOINs multiples
```

### **RecetteController** (Avec jointures)
```php
1. getAllRecettes()               // LEFT JOIN ingredients_recette
2. getRecetteWithIngredients()    // LEFT JOINs multiples
3. getRecetteByDifficulte()       // LEFT JOIN ingredients_recette
```

---

## 🎯 Cas d'Usage et Performances

### ✅ **Cas d'Usage Optimisés**

#### 1. **Afficher les listes d'un utilisateur**
```php
$controller = new ListeController();
$listes = $controller->getListesByUser($user_id);
// Retourne: id, titre, nombre_articles, type, statut
```

#### 2. **Voir le détail d'une liste avec tous les articles**
```php
$controller = new ListeController();
$liste = $controller->getListeDetailsWithArticles($liste_id);
// Retourne: liste + articles + aliments + catégories
```

#### 3. **Dashboard utilisateur - Statistiques**
```php
$controller = new UserController();
$stats = $controller->getUserStatistics($user_id);
// Retourne: total_listes, articles_achetes, listes_courses, etc.
```

#### 4. **Admin - Vue d'ensemble du système**
```php
$controller = new UserController();
$users = $controller->listUsers();        // Avec stats par utilisateur
$system_stats = $controller->getSystemStatistics();
$top_aliments = $controller->getTopAliments(10);
```

### ⚡ **Optimisations Appliquées**

- **INDEX sur les colonnes de jointure**: `user.id`, `listes.user_id`, `aliments.categorie_id`
- **GROUP BY efficient**: Regroupement par clé primaire
- **COUNT DISTINCT**: Évite les doublons dans les comptages
- **Prepared Statements**: Protection SQL injection
- **CASE WHEN dans GROUP BY**: Comptages conditionnels sans sous-requêtes

---

## 🔍 Exemples de Requêtes Avancées

### Exemple 1: Recettes correspondant aux ingrédients d'une liste
```sql
SELECT DISTINCT r.id, r.titre, COUNT(ir.id) as ingredients_disponibles
FROM recettes r
INNER JOIN ingredients_recette ir ON r.id = ir.recette_id
INNER JOIN articles_liste al ON ir.aliment_id = al.aliment_id
WHERE al.liste_id = ?
GROUP BY r.id
ORDER BY ingredients_disponibles DESC
```

### Exemple 2: Aliments à renouveler prochainement
```sql
SELECT DISTINCT a.id, a.nom, al.date_ajout,
       a.conservation_jours,
       DATE_ADD(al.date_ajout, INTERVAL a.conservation_jours DAY) as date_limite
FROM articles_liste al
INNER JOIN aliments a ON al.aliment_id = a.id
INNER JOIN listes l ON al.liste_id = l.id
WHERE l.user_id = ? AND al.statut != 'consomme'
ORDER BY date_limite ASC
```

### Exemple 3: Impact environnemental utilisateur
```sql
SELECT u.id, u.prenom, u.nom,
       COUNT(DISTINCT al.id) as total_articles_conserves,
       SUM(a.conservation_jours) as jours_conservation_total
FROM user u
LEFT JOIN listes l ON u.id = l.user_id
LEFT JOIN articles_liste al ON l.id = al.liste_id AND al.statut = 'consomme'
LEFT JOIN aliments a ON al.aliment_id = a.id
WHERE u.id = ?
GROUP BY u.id
```

---

## 📝 Notes d'Implémentation

✅ **Fait**:
- Structure complète avec 7 tables
- Relations définies via clés étrangères (FK)
- Jointures LEFT/INNER JOIN implémentées
- GROUP BY avec COUNT DISTINCT
- Prepared statements pour la sécurité
- Indexes sur les colonnes clés

🎯 **À Améliorer**:
- [ ] Ajouter des vues (VIEWs) SQL pour les requêtes complexes
- [ ] Cache des statistiques fréquentes
- [ ] Indexation composée pour les jointures multiples
- [ ] Triggers pour la maintenance des données

---

## 🚀 Utilisation dans les Contrôleurs

Tous les contrôleurs utilisent `config::getConnexion()` pour accéder à la base de données avec PDO préparé:

```php
$db = config::getConnexion();
$req = $db->prepare($sql);
$req->bindValue(':param', $value);
$req->execute();
$results = $req->fetchAll(PDO::FETCH_ASSOC);
```

---

**Date**: Avril 2026
**Version**: Database Joins Complete
**Statut**: ✅ Production Ready
