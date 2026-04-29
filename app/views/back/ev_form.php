<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementController.php';

$controller = new EvenementController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$errors = [];
$ev = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ev = [
        'titre' => trim($_POST['titre'] ?? ''),
        'categorie' => trim($_POST['categorie'] ?? ''),
        'statut' => $_POST['statut'] ?? 'upcoming',
        'date_event' => $_POST['date_event'] ?? '',
        'heure' => $_POST['heure'] ?? '',
        'lieu' => trim($_POST['lieu'] ?? ''),
        'organisateur' => trim($_POST['organisateur'] ?? ''),
        'capacite' => $_POST['capacite'] ?? '',
        'description' => trim($_POST['description'] ?? ''),
    ];

    $pid = (int) ($_POST['id'] ?? 0);
    $errors = $controller->validate($ev);

    if (empty($errors)) {
        if ($pid > 0) {
            $controller->update($pid, $ev);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Evenement modifie avec succes !'];
        } else {
            $controller->create($ev);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Evenement cree avec succes !'];
        }

        header('Location: evenements.php');
        exit;
    }
} elseif ($isEdit) {
    $ev = $controller->findById($id);
    if (!$ev) {
        header('Location: evenements.php');
        exit;
    }

    if (!empty($ev['heure'])) {
        $ev['heure'] = substr($ev['heure'], 0, 5);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — <?= $isEdit ? 'Modifier' : 'Creer' ?> evenement</title>
<link rel="stylesheet" href="../../../public/css/style.css">
</head>
<body class="back-wrap">
<aside class="sidebar" id="sb">
  <div class="sb-brand"><div class="sb-icon">🌿</div><div class="sb-name"><span>Food</span><em>Save</em><small>Admin</small></div></div>
  <nav class="sb-nav">
    <div class="nav-lbl">Gestion</div>
    <a href="evenements.php" class="nav-a active">📅 Evenements</a>
    <a href="participants.php" class="nav-a">👥 Participants</a>
    <div class="nav-lbl" style="margin-top:10px">Acces rapide</div>
    <a href="../front/accueil.php" class="nav-a">🌐 Voir le site</a>
  </nav>
  <div class="sb-footer">
    <div class="user-card"><div class="u-av">AD</div><div><div class="u-name">Administrateur</div><div class="u-role">BackOffice</div></div></div>
  </div>
</aside>

<div class="main-wrap">
  <header class="topbar">
    <button class="ic-btn" onclick="document.getElementById('sb').classList.toggle('open')">☰</button>
    <div class="tb-title">🌿 FoodSave — BackOffice</div>
    <a href="../front/accueil.php" class="btn btn-outline btn-sm">🌐 Front</a>
  </header>

  <div class="content">
    <div class="page-strip">
      <div>
        <div class="pg-title"><?= $isEdit ? '✏ Modifier' : '＋ Creer' ?> un evenement</div>
        <div class="pg-sub">Les champs marques * sont obligatoires</div>
      </div>
      <a href="evenements.php" class="btn btn-outline">← Retour</a>
    </div>

    <div class="card" style="max-width:760px">
      <div class="card-body">
        <form method="POST" action="ev_form.php" id="ev-form" novalidate>
          <input type="hidden" name="id" value="<?= $isEdit ? $id : 0 ?>">

          <div class="form-group">
            <label for="titre">Titre *</label>
            <input type="text" id="titre" name="titre"
                   value="<?= htmlspecialchars($ev['titre'] ?? '') ?>"
                   placeholder="Ex: Atelier Anti-Gaspillage"
                   data-validate="required|minlen:3|maxlen:150">
            <?php if (isset($errors['titre'])): ?><span class="field-err"><?= $errors['titre'] ?></span><?php endif; ?>
            <div class="js-err" id="e-titre"></div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="categorie">Categorie *</label>
              <select id="categorie" name="categorie" data-validate="required">
                <option value="">-- Selectionner --</option>
                <?php foreach (['Atelier','Conference','Hackathon','Social','Formation','Autre'] as $c): ?>
                <option value="<?= $c ?>" <?= ($ev['categorie'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['categorie'])): ?><span class="field-err"><?= $errors['categorie'] ?></span><?php endif; ?>
              <div class="js-err" id="e-categorie"></div>
            </div>
            <div class="form-group">
              <label for="statut">Statut *</label>
              <select id="statut" name="statut" data-validate="required">
                <option value="upcoming" <?= ($ev['statut'] ?? 'upcoming') === 'upcoming' ? 'selected' : '' ?>>A venir</option>
                <option value="ongoing" <?= ($ev['statut'] ?? '') === 'ongoing' ? 'selected' : '' ?>>En cours</option>
                <option value="past" <?= ($ev['statut'] ?? '') === 'past' ? 'selected' : '' ?>>Termine</option>
              </select>
              <?php if (isset($errors['statut'])): ?><span class="field-err"><?= $errors['statut'] ?></span><?php endif; ?>
              <div class="js-err" id="e-statut"></div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="date_event">Date * <small style="color:var(--g500)">(YYYY-MM-DD)</small></label>
              <input type="text" id="date_event" name="date_event"
                     value="<?= htmlspecialchars($ev['date_event'] ?? '') ?>"
                     placeholder="2026-04-20"
                     data-validate="required|date">
              <?php if (isset($errors['date_event'])): ?><span class="field-err"><?= $errors['date_event'] ?></span><?php endif; ?>
              <div class="js-err" id="e-date_event"></div>
            </div>
            <div class="form-group">
              <label for="heure">Heure * <small style="color:var(--g500)">(HH:MM)</small></label>
              <input type="text" id="heure" name="heure"
                     value="<?= htmlspecialchars($ev['heure'] ?? '') ?>"
                     placeholder="10:00"
                     data-validate="required|time">
              <?php if (isset($errors['heure'])): ?><span class="field-err"><?= $errors['heure'] ?></span><?php endif; ?>
              <div class="js-err" id="e-heure"></div>
            </div>
          </div>

          <div class="form-group">
            <label for="lieu">Lieu *</label>
            <input type="text" id="lieu" name="lieu"
                   value="<?= htmlspecialchars($ev['lieu'] ?? '') ?>"
                   placeholder="Ex: Salle B2, Tunis"
                   data-validate="required|minlen:2">
            <?php if (isset($errors['lieu'])): ?><span class="field-err"><?= $errors['lieu'] ?></span><?php endif; ?>
            <div class="js-err" id="e-lieu"></div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="organisateur">Organisateur *</label>
              <input type="text" id="organisateur" name="organisateur"
                     value="<?= htmlspecialchars($ev['organisateur'] ?? '') ?>"
                     placeholder="Nom de l'organisateur"
                     data-validate="required|minlen:2">
              <?php if (isset($errors['organisateur'])): ?><span class="field-err"><?= $errors['organisateur'] ?></span><?php endif; ?>
              <div class="js-err" id="e-organisateur"></div>
            </div>
            <div class="form-group">
              <label for="capacite">Capacite *</label>
              <input type="text" id="capacite" name="capacite"
                     value="<?= htmlspecialchars($ev['capacite'] ?? '50') ?>"
                     placeholder="50"
                     data-validate="required|number|min:1|max:10000">
              <?php if (isset($errors['capacite'])): ?><span class="field-err"><?= $errors['capacite'] ?></span><?php endif; ?>
              <div class="js-err" id="e-capacite"></div>
            </div>
          </div>

          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"
                      placeholder="Decrivez l'evenement..."
                      data-validate="maxlen:1000"><?= htmlspecialchars($ev['description'] ?? '') ?></textarea>
            <div class="js-err" id="e-description"></div>
          </div>

          <div class="form-actions">
            <a href="evenements.php" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? '💾 Enregistrer' : '＋ Creer l\'evenement' ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toasts"></div>
<script src="../../../public/js/validation.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statut = document.getElementById('statut');
    const dateEvent = document.getElementById('date_event');

    function today() {
        const d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function syncDateField() {
        if (statut.value === 'ongoing') {
            dateEvent.value = today();
            dateEvent.readOnly = true;
            dateEvent.setAttribute('data-validate', 'date');
        } else {
            dateEvent.readOnly = false;
            dateEvent.setAttribute('data-validate', 'required|date');
        }
    }

    statut.addEventListener('change', syncDateField);
    syncDateField();
});
</script>
</body>
</html>
