# FoodSave — Gestion Evenements & Participants
## Projet PHP · Architecture MVC · PDO · Validation JS sans HTML5
**Equipe NextWave** | Module Developpement Web

---

## INSTALLATION (3 etapes)

### Etape 1 — Copier le dossier
Copier `foodsave2/` dans :
```
C:/xampp/htdocs/foodsave2/
```

### Etape 2 — Importer la base de donnees
1. Ouvrir phpMyAdmin : http://localhost/phpmyadmin
2. Aller dans l'onglet **SQL**
3. Copier-coller tout le contenu de `config/foodsave_db.sql`
4. Cliquer **Executer**

### Etape 3 — Ouvrir dans le navigateur
- **FrontOffice** : http://localhost/foodsave2/app/views/front/accueil.php
- **BackOffice**  : http://localhost/foodsave2/app/views/back/evenements.php

> IMPORTANT : Ne jamais ouvrir les fichiers directement (double-clic).
> Toujours passer par http://localhost/... sinon PHP ne s'execute pas.

---

## STRUCTURE DES FICHIERS

```
foodsave2/
|
|-- config/
|   |-- database.php          Connexion PDO (Singleton)
|   +-- foodsave_db.sql       Schema SQL + donnees de test
|
|-- app/
|   |-- models/
|   |   |-- EvenementModel.php     CRUD + validation PHP
|   |   +-- ParticipantModel.php   CRUD + validation PHP
|   |
|   +-- views/
|       |-- front/                 FrontOffice
|       |   |-- accueil.php        Page d'accueil + hero
|       |   |-- evenements.php     Liste + filtres
|       |   |-- ev_detail.php      Detail evenement
|       |   +-- inscription.php    Formulaire inscription
|       |
|       +-- back/                  BackOffice
|           |-- evenements.php     Liste CRUD evenements
|           |-- ev_form.php        Creer / Modifier evenement
|           |-- ev_show.php        Detail + participants
|           |-- participants.php   Liste CRUD participants
|           +-- p_form.php         Creer / Modifier participant
|
+-- public/
    |-- css/
    |   +-- style.css         Tout le design (FoodSave theme)
    +-- js/
        +-- validation.js     Validation SANS HTML5 (JS pur)
```

---

## PAGES DE L'APPLICATION

### FrontOffice (visible par tous)

| Page | URL | Description |
|------|-----|-------------|
| Accueil | /front/accueil.php | Hero + evenements a venir |
| Evenements | /front/evenements.php | Liste complete + filtres |
| Detail | /front/ev_detail.php?id=1 | Detail d'un evenement |
| Inscription | /front/inscription.php?id=1 | S'inscrire a un evenement |

### BackOffice (administration)

| Page | URL | Description |
|------|-----|-------------|
| Evenements | /back/evenements.php | Liste + recherche + stats |
| Creer/Modifier | /back/ev_form.php | Formulaire evenement |
| Detail evenement | /back/ev_show.php?id=1 | Voir + participants |
| Participants | /back/participants.php | Liste + filtres + stats |
| Creer/Modifier | /back/p_form.php | Formulaire participant |

---

## CONTRAINTES RESPECTEES

| Exigence | Statut | Detail |
|----------|--------|--------|
| CRUD FrontOffice | OK | Inscription + detail evenement |
| CRUD BackOffice | OK | Create/Read/Update/Delete complet |
| Templates integres | OK | Design FoodSave sur toutes les pages |
| Validation SANS HTML5 | OK | validation.js : required, email, date, time, letters, phone... |
| Architecture MVC | OK | Models/ + Views/ + logique dans les pages |
| PDO obligatoire | OK | Database::getConnection() Singleton dans tous les models |
| Validation PHP cote serveur | OK | validate() dans EvenementModel et ParticipantModel |

---

## VALIDATION JS (sans HTML5)

Le fichier `public/js/validation.js` valide les formulaires SANS utiliser :
- `type="email"` (interdit HTML5)
- `type="date"` (interdit HTML5)
- `required` attribut HTML5 (interdit)
- `min`, `max`, `pattern` attributs HTML5 (interdits)

Regles disponibles via `data-validate="..."` :
```
required        → champ obligatoire
minlen:N        → minimum N caracteres
maxlen:N        → maximum N caracteres
email           → format email valide
number          → nombre valide
min:N           → valeur minimum N
max:N           → valeur maximum N
date            → format YYYY-MM-DD
time            → format HH:MM
letters         → lettres uniquement
phone           → telephone (8-20 chiffres)
```

Exemple :
```html
<input type="text" id="titre" name="titre"
       data-validate="required|minlen:3|maxlen:150">
<div class="js-err" id="e-titre"></div>
```

---

## BASE DE DONNEES

```sql
TABLE evenements
  id, titre, categorie, statut, date_event, heure,
  lieu, organisateur, capacite, description, created_at

TABLE participants
  id, nom, prenom, email, telephone,
  evenement_id (FK), statut, date_inscription
```

---

## EQUIPE NEXTWAVE

| Membre | Module |
|--------|--------|
| Faten Karoui | Gestion utilisateurs |
| Nermine Achour | Conseils & Articles |
| Fares Chihaoui | Gestion des dechets |
| Wadhah Laaribi | Gestion Evenements |
| Cyrine Mahouachi | Forum |
