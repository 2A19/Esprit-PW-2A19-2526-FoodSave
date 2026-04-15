<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementController.php';

$controller = new EvenementController();
$id = (int) ($_GET['id'] ?? 0);
$ev = $controller->findById($id);

if (!$ev) {
    header('Location: evenements.php');
    exit;
}

$nb = $controller->countParticipants($id);
$pct = $ev['capacite'] > 0 ? min(100, round($nb / $ev['capacite'] * 100)) : 0;
$isFull = $pct >= 100;

$slabels = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
$sbadge = ['upcoming' => 'b-green', 'ongoing' => 'b-orange', 'past' => 'b-gray'];

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — <?= htmlspecialchars($ev['titre']) ?></title>
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
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>" style="margin-bottom:20px">
      <?= htmlspecialchars($flash['msg']) ?>
      <button class="alert-close" onclick="this.parentElement.remove()">✕</button>
    </div>
    <?php endif; ?>

    <div class="breadcrumb" style="margin-bottom:18px">
      <a href="accueil.php">Accueil</a> › <a href="evenements.php">Evenements</a> › <?= htmlspecialchars($ev['titre']) ?>
    </div>

    <div class="show-grid">
      <div>
        <div class="card">
          <div class="show-banner"></div>
          <div class="card-body">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap">
              <h1 style="font-family:var(--fh);font-size:1.55rem;font-weight:900;line-height:1.2"><?= htmlspecialchars($ev['titre']) ?></h1>
              <span class="badge <?= $sbadge[$ev['statut']] ?? 'b-gray' ?>" style="padding:5px 12px;font-size:.75rem"><?= $slabels[$ev['statut']] ?? $ev['statut'] ?></span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
              <?php foreach ([
                ['📅', 'Date', date('d/m/Y', strtotime($ev['date_event'])) . ' a ' . substr($ev['heure'], 0, 5)],
                ['📍', 'Lieu', $ev['lieu']],
                ['👤', 'Organisateur', $ev['organisateur']],
                ['🏷', 'Categorie', $ev['categorie']],
              ] as [$ic, $lbl, $val]): ?>
              <div style="background:var(--g100);border-radius:8px;padding:11px">
                <div style="font-size:.68rem;color:var(--g500);font-weight:700;margin-bottom:3px"><?= $ic ?> <?= $lbl ?></div>
                <div style="font-size:.86rem;font-weight:600"><?= htmlspecialchars($val) ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php if (!empty($ev['description'])): ?>
            <div style="margin-bottom:18px">
              <h3 style="font-family:var(--fh);font-weight:800;margin-bottom:7px">Description</h3>
              <p style="font-size:.86rem;line-height:1.8;color:var(--g700)"><?= nl2br(htmlspecialchars($ev['description'])) ?></p>
            </div>
            <?php endif; ?>
            <div style="background:var(--green-bg);border-radius:10px;padding:14px">
              <div style="display:flex;justify-content:space-between;margin-bottom:7px">
                <span style="font-weight:700;color:var(--green-d)">👥 Inscriptions</span>
                <strong><?= $nb ?> / <?= $ev['capacite'] ?> (<?= $pct ?>%)</strong>
              </div>
              <div class="progress-bar" style="height:8px"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
            </div>
          </div>
        </div>
      </div>

      <div>
        <div class="card" style="position:sticky;top:80px">
          <div class="card-body" style="text-align:center">
            <div style="font-size:3rem;margin-bottom:12px">
              <?= $ev['statut'] === 'past' ? '✅' : ($isFull ? '🔒' : '✍') ?>
            </div>
            <?php if ($ev['statut'] === 'past'): ?>
              <h3 style="font-family:var(--fh);margin-bottom:8px">Evenement termine</h3>
              <p style="font-size:.8rem;color:var(--g500)">Consultez les prochains evenements !</p>
            <?php elseif ($isFull): ?>
              <h3 style="font-family:var(--fh);margin-bottom:8px">Complet</h3>
              <p style="font-size:.8rem;color:var(--g500)">Toutes les places sont prises.</p>
            <?php else: ?>
              <h3 style="font-family:var(--fh);margin-bottom:5px">Rejoindre cet evenement</h3>
              <p style="font-size:.79rem;color:var(--g500);margin-bottom:15px"><?= $ev['capacite'] - $nb ?> place(s) disponible(s)</p>
              <a href="inscription.php?id=<?= $ev['id'] ?>" class="btn btn-primary" style="width:100%;justify-content:center">✍ S'inscrire maintenant</a>
            <?php endif; ?>
            <a href="evenements.php" class="btn btn-outline btn-sm" style="margin-top:10px;width:100%;justify-content:center">← Tous les evenements</a>
          </div>
        </div>
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
