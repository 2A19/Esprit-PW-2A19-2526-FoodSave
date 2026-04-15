<?php
require_once __DIR__ . '/../../controller/EvenementController.php';

$controller = new EvenementController();
$search = trim($_GET['search'] ?? '');
$statut = $_GET['statut'] ?? '';
$categorie = $_GET['categorie'] ?? '';

$rows = $controller->listEvents($search, $statut, $categorie);

$slabels = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
$sbadge = ['upcoming' => 'b-green', 'ongoing' => 'b-orange', 'past' => 'b-gray'];
$currentUrl = 'evenements.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — Evenements</title>
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
  <div class="f-container">
    <div class="sec-head" style="margin-bottom:22px">
      <div><div class="sec-title">📅 Tous les Evenements</div><div class="sec-sub">Decouvrez nos actions et rejoignez-nous</div></div>
    </div>

    <div class="filter-bar" style="margin-bottom:24px">
      <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
        <input class="s-input" type="text" name="search" placeholder="🔍 Rechercher..." value="<?= htmlspecialchars($search) ?>">
        <select name="statut">
          <option value="">Tous statuts</option>
          <option value="upcoming" <?= $statut === 'upcoming' ? 'selected' : '' ?>>A venir</option>
          <option value="ongoing" <?= $statut === 'ongoing' ? 'selected' : '' ?>>En cours</option>
          <option value="past" <?= $statut === 'past' ? 'selected' : '' ?>>Termines</option>
        </select>
        <select name="categorie">
          <option value="">Toutes categories</option>
          <?php foreach (['Atelier','Conference','Hackathon','Social','Formation','Autre'] as $c): ?>
          <option value="<?= $c ?>" <?= $categorie === $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
        <a href="evenements.php" class="btn btn-outline btn-sm">Reset</a>
      </form>
    </div>

    <div class="ev-grid">
    <?php if (empty($rows)): ?>
      <div class="empty" style="grid-column:1/-1"><div class="empty-ic">📅</div><div class="empty-tt">Aucun evenement</div></div>
    <?php else: ?>
      <?php foreach ($rows as $ev):
        $pct = $ev['capacite'] > 0 ? min(100, round($ev['nb_p'] / $ev['capacite'] * 100)) : 0;
        $sLabel = $slabels[$ev['statut']] ?? $ev['statut'];
        $sBadge = $sbadge[$ev['statut']] ?? 'b-gray';
      ?>
      <div class="ev-card" style="animation:fadeUp .4s ease both">
        <div class="ev-top <?= $ev['statut'] ?>"></div>
        <div class="ev-body">
          <div class="ev-head">
            <div class="ev-name"><?= htmlspecialchars($ev['titre']) ?></div>
            <span class="badge <?= $sBadge ?>"><?= $sLabel ?></span>
          </div>
          <div class="ev-meta">
            <div class="ev-meta-row"><span class="ev-icon">📅</span><?= date('d/m/Y', strtotime($ev['date_event'])) ?> — <?= substr($ev['heure'], 0, 5) ?></div>
            <div class="ev-meta-row"><span class="ev-icon">📍</span><?= htmlspecialchars($ev['lieu']) ?></div>
            <div class="ev-meta-row"><span class="ev-icon">👤</span><?= htmlspecialchars($ev['organisateur']) ?></div>
            <div class="ev-meta-row"><span class="ev-icon">🏷</span><span class="badge b-blue"><?= htmlspecialchars($ev['categorie']) ?></span></div>
          </div>
          <?php if (!empty($ev['description'])): ?>
          <p class="ev-desc"><?= htmlspecialchars(mb_substr($ev['description'], 0, 90)) ?>...</p>
          <?php endif; ?>
          <div class="ev-prog">
            <div class="progress-lbl"><span><?= $ev['nb_p'] ?>/<?= $ev['capacite'] ?></span><span><?= $pct ?>%</span></div>
            <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
          </div>
          <div class="ev-actions">
            <a href="ev_detail.php?id=<?= $ev['id'] ?>" class="btn btn-outline btn-sm">Details</a>
            <?php if ($ev['statut'] !== 'past' && $pct < 100): ?>
            <a href="inscription.php?id=<?= $ev['id'] ?>" class="btn btn-primary btn-sm">S'inscrire</a>
            <?php else: ?>
            <span class="badge b-gray" style="padding:6px 12px"><?= $pct >= 100 ? 'Complet' : 'Termine' ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
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
