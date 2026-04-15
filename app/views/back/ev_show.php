<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementController.php';
require_once __DIR__ . '/../../controller/ParticipantController.php';

$controller = new EvenementController();
$participantController = new ParticipantController();

$id = (int) ($_GET['id'] ?? 0);
$ev = $controller->findById($id);

if (!$ev) {
    header('Location: evenements.php');
    exit;
}

$participants = $participantController->findByEvent($id);
$nb = $controller->countParticipants($id);
$pct = $ev['capacite'] > 0 ? min(100, round($nb / $ev['capacite'] * 100)) : 0;

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
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
      <?= htmlspecialchars($flash['msg']) ?>
      <button class="alert-close" onclick="this.parentElement.remove()">✕</button>
    </div>
    <?php endif; ?>

    <div class="page-strip">
      <div><div class="pg-title">👁 <?= htmlspecialchars($ev['titre']) ?></div><div class="pg-sub">Detail evenement #<?= $ev['id'] ?></div></div>
      <div style="display:flex;gap:8px">
        <a href="ev_form.php?id=<?= $ev['id'] ?>" class="btn btn-primary">✏ Modifier</a>
        <a href="evenements.php" class="btn btn-outline">← Retour</a>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:22px">
      <div class="card">
        <div class="card-header"><span class="card-title">📋 Informations</span></div>
        <div class="card-body">
          <table style="width:100%;font-size:.86rem;border-collapse:collapse">
            <?php foreach ([
              ['Categorie', '<span class="badge b-blue">' . htmlspecialchars($ev['categorie']) . '</span>'],
              ['Statut', '<span class="badge ' . ($sbadge[$ev['statut']] ?? 'b-gray') . '">' . ($slabels[$ev['statut']] ?? $ev['statut']) . '</span>'],
              ['Date', date('d/m/Y', strtotime($ev['date_event'])) . ' a ' . htmlspecialchars(substr($ev['heure'], 0, 5))],
              ['Lieu', htmlspecialchars($ev['lieu'])],
              ['Organisateur', htmlspecialchars($ev['organisateur'])],
              ['Cree le', date('d/m/Y H:i', strtotime($ev['created_at']))],
            ] as [$l, $v]): ?>
            <tr>
              <td style="color:var(--g500);padding:6px 0;width:40%;font-weight:600"><?= $l ?></td>
              <td><?= $v ?></td>
            </tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><span class="card-title">👥 Inscriptions</span></div>
        <div class="card-body">
          <div style="text-align:center;margin-bottom:16px">
            <div style="font-size:2.4rem;font-weight:900;color:var(--green)"><?= $nb ?></div>
            <div style="color:var(--g500);font-size:.83rem">sur <?= $ev['capacite'] ?> places</div>
          </div>
          <div class="ev-prog">
            <div class="progress-lbl"><span>Remplissage</span><span><?= $pct ?>%</span></div>
            <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
          </div>
          <?php if (!empty($ev['description'])): ?>
          <div style="background:var(--g100);border-radius:8px;padding:10px;font-size:.83rem;color:var(--g700);margin-top:10px">
            <?= nl2br(htmlspecialchars($ev['description'])) ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <span class="card-title">👥 Participants (<?= count($participants) ?>)</span>
        <a href="p_form.php?ev=<?= $ev['id'] ?>" class="btn btn-primary btn-sm">＋ Ajouter</a>
      </div>
      <div class="card-body" style="padding:0">
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Nom</th><th>Email</th><th>Tel</th><th>Statut</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($participants)): ?>
              <tr><td colspan="7"><div class="empty"><div class="empty-ic">👥</div><div class="empty-tt">Aucun participant</div></div></td></tr>
            <?php else: ?>
              <?php foreach ($participants as $p): ?>
              <tr>
                <td style="color:var(--g500);font-size:.78rem"><?= $p['id'] ?></td>
                <td><strong><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></strong></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= htmlspecialchars($p['telephone'] ?: '—') ?></td>
                <td>
                  <?php if ($p['statut'] === 'confirmed'): ?>
                    <span class="badge b-green">Confirme</span>
                  <?php elseif ($p['statut'] === 'pending'): ?>
                    <span class="badge b-orange">En attente</span>
                  <?php else: ?>
                    <span class="badge b-gray">Annule</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:.78rem;color:var(--g500)"><?= date('d/m/Y', strtotime($p['date_inscription'])) ?></td>
                <td>
                  <div style="display:flex;gap:4px">
                    <a href="p_form.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">✏</a>
                    <form method="POST" action="participants.php" style="display:inline" onsubmit="return confirmDel('Supprimer ce participant ?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $p['id'] ?>">
                      <input type="hidden" name="redirect" value="ev_show.php?id=<?= $ev['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toasts"></div>
<script src="../../../public/js/validation.js"></script>
</body>
</html>
