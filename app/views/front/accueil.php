<?php
require_once __DIR__ . '/../../controller/EvenementController.php';

$controller = new EvenementController();
$evenements = $controller->attachParticipantCounts($controller->findUpcoming(6));
$stats = $controller->getStats();

$slabels = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
$sbadge = ['upcoming' => 'b-green', 'ongoing' => 'b-orange', 'past' => 'b-gray'];
$currentUrl = 'accueil.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — Accueil</title>
<link rel="stylesheet" href="../../../public/css/style.css">
</head>
<body style="background:var(--g100);display:flex;flex-direction:column;min-height:100vh">
<nav class="front-nav">
  <div class="fn-inner">
    <a href="accueil.php" class="fn-brand">
      <div class="bi">🌿</div>
      <span><strong>Food</strong><em>Save</em></span>
    </a>
    <div class="fn-links">
      <a href="accueil.php" class="<?= $currentUrl === 'accueil.php' ? 'on' : '' ?>">Accueil</a>
      <a href="evenements.php">Evenements</a>
      <a href="../back/evenements.php" class="btn btn-primary btn-sm">⚙ Admin</a>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="hero-inner">
    <div class="hero-tag">🌿 FoodSave</div>
    <h1>Ensemble contre le<br><span>Gaspillage Alimentaire</span></h1>
    <p>Rejoignez nos evenements et ateliers pour une alimentation durable et responsable.</p>
    <a href="evenements.php" class="hero-btn">📅 Voir tous les evenements</a>
    <div class="hero-stats">
      <div class="h-stat"><strong><?= $stats['total'] ?></strong><span>Evenements</span></div>
      <div class="h-stat"><strong><?= $stats['upcoming'] + $stats['ongoing'] ?></strong><span>A venir</span></div>
      <div class="h-stat"><strong><?= $stats['total_cap'] ?></strong><span>Places</span></div>
    </div>
  </div>
</section>

<section class="f-section">
  <div class="f-container">
    <div class="sec-head">
      <div><div class="sec-title">Prochains Evenements</div><div class="sec-sub">Inscrivez-vous et participez !</div></div>
      <a href="evenements.php" class="btn btn-outline">Voir tout →</a>
    </div>

    <div class="ev-grid">
    <?php if (empty($evenements)): ?>
      <div class="empty" style="grid-column:1/-1"><div class="empty-ic">📅</div><div class="empty-tt">Aucun evenement a venir</div></div>
    <?php else: ?>
      <?php foreach ($evenements as $ev):
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
            <div class="ev-meta-row"><span class="ev-icon">🏷</span><span class="badge b-blue"><?= htmlspecialchars($ev['categorie']) ?></span></div>
          </div>
          <?php if (!empty($ev['description'])): ?>
          <p class="ev-desc"><?= htmlspecialchars(mb_substr($ev['description'], 0, 90)) ?>...</p>
          <?php endif; ?>
          <div class="ev-prog">
            <div class="progress-lbl"><span><?= $ev['nb_p'] ?>/<?= $ev['capacite'] ?> inscrits</span><span><?= $pct ?>%</span></div>
            <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
          </div>
          <div class="ev-actions">
            <a href="ev_detail.php?id=<?= $ev['id'] ?>" class="btn btn-outline btn-sm">Details</a>
            <?php if ($ev['statut'] !== 'past' && $pct < 100): ?>
            <a href="inscription.php?id=<?= $ev['id'] ?>" class="btn btn-primary btn-sm">S'inscrire</a>
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
  <p>© 2026 FoodSave — Plateforme intelligente contre le gaspillage alimentaire</p>
  <p style="font-size:.74rem;opacity:.6;margin-top:4px">Equipe NextWave</p>
</footer>

<div class="toast-wrap" id="toasts"></div>
<script src="../../../public/js/validation.js"></script>
</body>
</html>
