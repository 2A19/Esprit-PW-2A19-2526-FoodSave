# 🍃 FoodSave — Gestion des Déchets
**Équipe NextWave | Module Projet Web | ESPRIT 2025-2026**

---

## 📁 Structure du projet

```
FoodSave/
├── index.html              ← Front Office (HTML/CSS/JS pur)
├── backoffice.html         ← Back Office Admin
├── css/
│   └── style.css           ← Charte graphique FoodSave
├── js/
│   └── app.js              ← CRUD + Validation JS (sans HTML5)
├── config/
│   ├── Database.php        ← Connexion PDO (singleton)
│   └── schema.sql          ← Script SQL de création de la BD
├── models/
│   └── Dechet.php          ← Model MVC (PDO)
├── controllers/
│   └── DechetController.php ← Controller MVC
└── views/
    ├── frontoffice/dechet/ ← Vues utilisateur
    └── backoffice/dechet/  ← Vues admin
```

---

## ✅ Conformité avec les exigences du professeur

| Exigence | Statut |
|---|---|
| CRUD fonctionnel FrontOffice | ✅ |
| CRUD fonctionnel BackOffice | ✅ |
| Templates FrontOffice & BackOffice | ✅ |
| Contrôle de saisie dans tous les formulaires | ✅ |
| Validation JavaScript (PAS HTML5) | ✅ (`novalidate` sur tous les forms) |
| Architecture MVC | ✅ (Model / View / Controller) |
| Connexion PDO obligatoire | ✅ |
| GitHub / tableau de bord des tâches | À configurer |

---

## 🚀 Installation (PHP + MySQL)

1. **Copier** le dossier dans `htdocs/` (XAMPP) ou `www/` (WAMP)
2. **Créer la BD** : importer `config/schema.sql` dans phpMyAdmin
3. **Configurer** `config/Database.php` avec vos identifiants MySQL
4. **Démarrer Apache + MySQL** (XAMPP/WAMP)
5. Ouvrir `http://localhost/FoodSave/index.html`

### API utilisée par le formulaire d'ajout

- Endpoint : `api/dechets.php`
- `GET` : retourne la liste des déchets depuis MySQL (`scope=all` pour l'admin)
- `POST` : enregistre un nouveau déchet en base de données
- `PUT` : modifie un déchet existant
- `DELETE` : supprime un déchet existant

⚠️ Ouvrir le projet via un serveur PHP (Apache) et pas en double-cliquant le fichier HTML, sinon l'API PHP ne sera pas appelée.

---

## 🎨 Charte graphique

- **Vert principal** : `#4CAF50` (Écologie)
- **Orange** : `#FFA726` (Énergie)
- **Blanc** : `#FFFFFF` (Simplicité)

---

## 👥 Membres de l'équipe

| Nom | Module |
|---|---|
| Faten Karoui | Gestion utilisateurs |
| Nermine Achour | Conseils & Articles |
| Fares Chihaoui | **Gestion des Déchets** ← ce template |
| Wadhah Laaribi | Événements |
| Cyrine Mahouachi | Forum |
