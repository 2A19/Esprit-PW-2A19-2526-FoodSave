<?php
// ============================================================
//  app/views/front/inscription.php — Formulaire d'inscription
// ============================================================
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/EvenementModel.php';
require_once __DIR__ . '/../../models/ParticipantModel.php';

$evMdl  = new EvenementModel();
$pModel = new ParticipantModel();
$id     = (int)($_GET['id'] ?? 0);
$ev     = $evMdl->findById($id);
if (!$ev) { header('Location: evenements.php'); exit; }

$errors = [];
$p      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = [
        'nom'          => trim($_POST['nom']       ?? ''),
        'prenom'       => trim($_POST['prenom']    ?? ''),
        'email'        => strtolower(trim($_POST['email'] ?? '')),
        'telephone'    => trim($_POST['telephone'] ?? ''),
        'evenement_id' => $id,
        'statut'       => 'pending',
    ];

    // Validation PHP — PAS HTML5
    $errors = ParticipantModel::validate($p);

    if (empty($errors['email']) && $p['email'] !== '') {
        if ($pModel->emailExists($p['email'], $id)) {
            $errors['email'] = 'Vous etes deja inscrit(e) a cet evenement.';
        }
    }

    if (empty($errors)) {
        $pModel->create($p);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Inscription reussie ! Vous recevrez une confirmation bientot.'];
        header('Location: ev_detail.php?id='.$id); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — Inscription</title>
<link rel="stylesheet" href="../../../public/css/style.css">
</head>
<body style="background:var(--g100);display:flex;flex-direction:column;min-height:100vh">

<nav class="front-nav">
  <div class="fn-inner">
    <a href="accueil.php" class="fn-brand"><div class="bi">🌿</div><span><strong>Food</strong><em>Save</em></span></a>
    <div class="fn-links">
      <a href="accueil.php">Accueil</a>
      <a href="evenements.php" class="on">Evenements</a>
      <a href="../back/evenements.php" class="btn btn-primary btn-sm">⚙ Admin</a>
    </div>
  </div>
</nav>

<section class="f-section" style="flex:1">
  <div class="f-container" style="max-width:660px">

    <!-- Recap evenement -->
    <div class="card" style="margin-bottom:18px;border-left:4px solid var(--green)">
      <div class="card-body" style="padding:13px 17px;display:flex;gap:13px;align-items:center">
        <div style="font-size:2rem">📅</div>
        <div>
          <div style="font-weight:800;font-size:.97rem"><?= htmlspecialchars($ev['titre']) ?></div>
          <div style="font-size:.78rem;color:var(--g500);margin-top:2px">
            📅 <?= date('d/m/Y',strtotime($ev['date_event'])) ?> a <?= substr($ev['heure'],0,5) ?>
            &nbsp;·&nbsp; 📍 <?= htmlspecialchars($ev['lieu']) ?>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">✍ Formulaire d'inscription</span></div>
      <div class="card-body">

        <!-- Affichage erreurs PHP si JS désactivé -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          Veuillez corriger les erreurs ci-dessous.
          <button class="alert-close" onclick="this.parentElement.remove()">✕</button>
        </div>
        <?php endif; ?>

        <form method="POST" action="inscription.php?id=<?= $id ?>" id="ins-form" novalidate>

          <div class="form-row">
            <div class="form-group">
              <label for="prenom">Prenom *</label>
              <input type="text" id="prenom" name="prenom"
                     value="<?= htmlspecialchars($p['prenom'] ?? '') ?>"
                     placeholder="Votre prenom"
                     data-validate="required|letters|minlen:2|maxlen:100">
              <?php if (isset($errors['prenom'])): ?><span class="field-err"><?= $errors['prenom'] ?></span><?php endif; ?>
              <div class="js-err" id="e-prenom"></div>
            </div>
            <div class="form-group">
              <label for="nom">Nom *</label>
              <input type="text" id="nom" name="nom"
                     value="<?= htmlspecialchars($p['nom'] ?? '') ?>"
                     placeholder="Votre nom"
                     data-validate="required|letters|minlen:2|maxlen:100">
              <?php if (isset($errors['nom'])): ?><span class="field-err"><?= $errors['nom'] ?></span><?php endif; ?>
              <div class="js-err" id="e-nom"></div>
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email *</label>
            <input type="text" id="email" name="email"
                   value="<?= htmlspecialchars($p['email'] ?? '') ?>"
                   placeholder="votre@email.com"
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

          <div style="background:var(--green-bg);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:.79rem;color:var(--green-d)">
            🔒 Vos donnees sont utilisees uniquement pour la gestion de cet evenement.
          </div>

          <div class="form-actions">
            <a href="ev_detail.php?id=<?= $id ?>" class="btn btn-outline">Annuler</a>
            <button type="submit" class="btn btn-primary">Confirmer l'inscription</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</section>

<footer class="front-foot">
  <div class="ff-brand"><div style="width:28px;height:28px;background:var(--green);border-radius:6px;display:flex;align-items:center;justify-content:center">🌿</div><strong>Food</strong><em>Save</em></div>
  <p>© 2026 FoodSave — Equipe NextWave</p>
</footer>
<div class="toast-wrap" id="toasts"></div>
<script src="../../../public/js/validation.js"></script>
</body>
</html>
