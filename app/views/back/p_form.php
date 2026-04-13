<?php
// ============================================================
//  app/views/back/p_form.php — Creer / Modifier un participant
// ============================================================
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/ParticipantModel.php';
require_once __DIR__ . '/../../models/EvenementModel.php';

$model  = new ParticipantModel();
$evMdl  = new EvenementModel();
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$preEv  = (int)($_GET['ev'] ?? 0);
$isEdit = $id > 0;
$errors = [];
$p      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['id'] ?? 0);
    $p = [
        'nom'          => trim($_POST['nom']          ?? ''),
        'prenom'       => trim($_POST['prenom']       ?? ''),
        'email'        => strtolower(trim($_POST['email'] ?? '')),
        'telephone'    => trim($_POST['telephone']    ?? ''),
        'evenement_id' => $_POST['evenement_id']      ?? '',
        'statut'       => $_POST['statut']            ?? 'pending',
    ];

    // Validation PHP — PAS HTML5
    $errors = ParticipantModel::validate($p);

    if (empty($errors['email']) && $p['email'] !== '' && (int)$p['evenement_id'] > 0) {
        if ($model->emailExists($p['email'], (int)$p['evenement_id'], $pid)) {
            $errors['email'] = 'Email deja inscrit pour cet evenement.';
        }
    }

    if (empty($errors)) {
        if ($pid > 0) {
            $model->update($pid, $p);
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Participant modifie !'];
        } else {
            $model->create($p);
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Participant ajoute !'];
        }
        header('Location: participants.php'); exit;
    }
} elseif ($isEdit) {
    $p = $model->findById($id);
    if (!$p) { header('Location: participants.php'); exit; }
}

$evenements = $evMdl->findAll();
$selEv = $p['evenement_id'] ?? $preEv;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — <?= $isEdit ? 'Modifier' : 'Ajouter' ?> participant</title>
<link rel="stylesheet" href="../../../public/css/style.css">
</head>
<body class="back-wrap">

<aside class="sidebar" id="sb">
  <div class="sb-brand"><div class="sb-icon">🌿</div><div class="sb-name"><span>Food</span><em>Save</em><small>Admin</small></div></div>
  <nav class="sb-nav">
    <div class="nav-lbl">Gestion</div>
    <a href="evenements.php" class="nav-a">📅 Evenements</a>
    <a href="participants.php" class="nav-a active">👥 Participants</a>
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
      <div><div class="pg-title"><?= $isEdit ? '✏ Modifier' : '＋ Ajouter' ?> un participant</div><div class="pg-sub">Les champs * sont obligatoires</div></div>
      <a href="participants.php" class="btn btn-outline">← Retour</a>
    </div>

    <div class="card" style="max-width:660px">
      <div class="card-body">
        <form method="POST" action="p_form.php" id="p-form" novalidate>
          <input type="hidden" name="id" value="<?= $isEdit ? $id : 0 ?>">

          <div class="form-row">
            <div class="form-group">
              <label for="prenom">Prenom *</label>
              <input type="text" id="prenom" name="prenom"
                     value="<?= htmlspecialchars($p['prenom'] ?? '') ?>"
                     placeholder="Ex: Amine"
                     data-validate="required|letters|minlen:2|maxlen:100">
              <?php if (isset($errors['prenom'])): ?><span class="field-err"><?= $errors['prenom'] ?></span><?php endif; ?>
              <div class="js-err" id="e-prenom"></div>
            </div>
            <div class="form-group">
              <label for="nom">Nom *</label>
              <input type="text" id="nom" name="nom"
                     value="<?= htmlspecialchars($p['nom'] ?? '') ?>"
                     placeholder="Ex: Ben Salem"
                     data-validate="required|letters|minlen:2|maxlen:100">
              <?php if (isset($errors['nom'])): ?><span class="field-err"><?= $errors['nom'] ?></span><?php endif; ?>
              <div class="js-err" id="e-nom"></div>
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email *</label>
            <input type="text" id="email" name="email"
                   value="<?= htmlspecialchars($p['email'] ?? '') ?>"
                   placeholder="exemple@email.com"
                   data-validate="required|email">
            <?php if (isset($errors['email'])): ?><span class="field-err"><?= $errors['email'] ?></span><?php endif; ?>
            <div class="js-err" id="e-email"></div>
          </div>

          <div class="form-group">
            <label for="telephone">Telephone <small style="color:var(--g500)">(optionnel)</small></label>
            <input type="text" id="telephone" name="telephone"
                   value="<?= htmlspecialchars($p['telephone'] ?? '') ?>"
                   placeholder="Ex: 55 123 456"
                   data-validate="phone">
            <?php if (isset($errors['telephone'])): ?><span class="field-err"><?= $errors['telephone'] ?></span><?php endif; ?>
            <div class="js-err" id="e-telephone"></div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="evenement_id">Evenement *</label>
              <select id="evenement_id" name="evenement_id" data-validate="required">
                <option value="">-- Selectionner --</option>
                <?php foreach ($evenements as $e): ?>
                <option value="<?= $e['id'] ?>" <?= (string)$selEv===(string)$e['id']?'selected':'' ?>>
                  <?= htmlspecialchars($e['titre']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['evenement_id'])): ?><span class="field-err"><?= $errors['evenement_id'] ?></span><?php endif; ?>
              <div class="js-err" id="e-evenement_id"></div>
            </div>
            <div class="form-group">
              <label for="statut">Statut *</label>
              <select id="statut" name="statut" data-validate="required">
                <option value="pending"   <?= ($p['statut']??'pending')==='pending'  ?'selected':'' ?>>En attente</option>
                <option value="confirmed" <?= ($p['statut']??'')==='confirmed'       ?'selected':'' ?>>Confirme</option>
                <option value="cancelled" <?= ($p['statut']??'')==='cancelled'       ?'selected':'' ?>>Annule</option>
              </select>
              <?php if (isset($errors['statut'])): ?><span class="field-err"><?= $errors['statut'] ?></span><?php endif; ?>
              <div class="js-err" id="e-statut"></div>
            </div>
          </div>

          <div class="form-actions">
            <a href="participants.php" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-primary">
              <?= $isEdit ? '💾 Enregistrer' : '＋ Ajouter' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toasts"></div>
<script src="../../../public/js/validation.js"></script>
</body>
</html>
