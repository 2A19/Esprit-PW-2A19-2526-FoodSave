<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementController.php';

$controller = new EvenementController();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $controller->delete((int) $_POST['id']);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Evenement supprime avec succes.'];
    header('Location: evenements.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$statut = $_GET['statut'] ?? '';
$rows = $controller->listEvents($search, $statut);
$stats = $controller->getStats();

$slabels = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
$sbadge = ['upcoming' => 'b-green', 'ongoing' => 'b-orange', 'past' => 'b-gray'];
$currentUrl = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — Gestion Evenements</title>
<link rel="stylesheet" href="../../../public/css/style.css">
</head>
<body class="back-wrap">
<aside class="sidebar" id="sb">
  <div class="sb-brand">
    <div class="sb-icon">🌿</div>
    <div class="sb-name"><span>Food</span><em>Save</em><small>Admin</small></div>
  </div>
  <nav class="sb-nav">
    <div class="nav-lbl">Gestion</div>
    <a href="evenements.php" class="nav-a <?= $currentUrl === 'evenements.php' ? 'active' : '' ?>">📅 Evenements</a>
    <a href="participants.php" class="nav-a <?= $currentUrl === 'participants.php' ? 'active' : '' ?>">👥 Participants</a>
    <div class="nav-lbl" style="margin-top:10px">Acces rapide</div>
    <a href="../front/accueil.php" class="nav-a">🌐 Voir le site</a>
  </nav>
  <div class="sb-footer">
    <div class="user-card">
      <div class="u-av">AD</div>
      <div><div class="u-name">Administrateur</div><div class="u-role">BackOffice</div></div>
    </div>
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
      <div><div class="pg-title">📅 Gestion des Evenements</div><div class="pg-sub">CRUD complet — BackOffice</div></div>
      <a href="ev_form.php" class="btn btn-primary">＋ Nouvel evenement</a>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon si-green">📅</div><div><div class="stat-value"><?= $stats['total'] ?></div><div class="stat-label">Total</div></div></div>
      <div class="stat-card"><div class="stat-icon si-green">🔜</div><div><div class="stat-value"><?= $stats['upcoming'] ?></div><div class="stat-label">A venir</div></div></div>
      <div class="stat-card"><div class="stat-icon si-orange">▶</div><div><div class="stat-value"><?= $stats['ongoing'] ?></div><div class="stat-label">En cours</div></div></div>
      <div class="stat-card"><div class="stat-icon si-blue">✔</div><div><div class="stat-value"><?= $stats['past'] ?></div><div class="stat-label">Termines</div></div></div>
    </div>

    <div class="filter-bar">
      <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
        <input class="s-input" type="text" name="search" placeholder="🔍 Rechercher..." value="<?= htmlspecialchars($search) ?>">
        <select name="statut">
          <option value="">Tous les statuts</option>
          <option value="upcoming" <?= $statut === 'upcoming' ? 'selected' : '' ?>>A venir</option>
          <option value="ongoing" <?= $statut === 'ongoing' ? 'selected' : '' ?>>En cours</option>
          <option value="past" <?= $statut === 'past' ? 'selected' : '' ?>>Termines</option>
        </select>
        <button type="submit" class="btn btn-outline btn-sm">Filtrer</button>
        <a href="evenements.php" class="btn btn-outline btn-sm">Reset</a>
      </form>
    </div>

    <div class="card">
      <div class="card-body" style="padding:0">
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Titre</th><th>Categorie</th><th>Date / Heure</th><th>Lieu</th><th>Organisateur</th><th>Places</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="9"><div class="empty"><div class="empty-ic">📅</div><div class="empty-tt">Aucun evenement</div></div></td></tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
              <tr>
                <td style="color:var(--g500);font-size:.78rem"><?= $r['id'] ?></td>
                <td><strong><?= htmlspecialchars($r['titre']) ?></strong></td>
                <td><span class="badge b-blue"><?= htmlspecialchars($r['categorie']) ?></span></td>
                <td><?= date('d/m/Y', strtotime($r['date_event'])) ?><br><small style="color:var(--g500)"><?= substr($r['heure'], 0, 5) ?></small></td>
                <td><?= htmlspecialchars($r['lieu']) ?></td>
                <td><?= htmlspecialchars($r['organisateur']) ?></td>
                <td>
                  <span style="font-size:.8rem"><?= $r['nb_p'] ?>/<?= $r['capacite'] ?></span>
                  <div class="progress-bar" style="margin-top:3px">
                    <div class="progress-fill" style="width:<?= $r['capacite'] > 0 ? min(100, round($r['nb_p'] / $r['capacite'] * 100)) : 0 ?>%"></div>
                  </div>
                </td>
                <td><span class="badge <?= $sbadge[$r['statut']] ?? 'b-gray' ?>"><?= $slabels[$r['statut']] ?? $r['statut'] ?></span></td>
                <td>
                  <div style="display:flex;gap:4px;flex-wrap:wrap">
                    <a href="ev_show.php?id=<?= $r['id'] ?>" class="btn btn-outline btn-sm" title="Voir">👁</a>
                    <a href="ev_form.php?id=<?= $r['id'] ?>" class="btn btn-outline btn-sm" title="Modifier">✏</a>
                    <form method="POST" style="display:inline" onsubmit="return confirmDel('Supprimer cet evenement ?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
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
