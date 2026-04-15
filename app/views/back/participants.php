<?php
session_start();
require_once __DIR__ . '/../../controller/ParticipantController.php';

$controller = new ParticipantController();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $controller->delete((int) $_POST['id']);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Participant supprime.'];
    $redirect = $_POST['redirect'] ?? 'participants.php';
    $allowed = ['participants.php', 'ev_show.php'];
    $base = basename(parse_url($redirect, PHP_URL_PATH));
    if (!in_array($base, $allowed, true)) {
        $redirect = 'participants.php';
    }
    header('Location: ' . $redirect);
    exit;
}

$search = trim($_GET['search'] ?? '');
$statut = $_GET['statut'] ?? '';
$evFilter = (int) ($_GET['ev'] ?? 0);

$rows = $controller->listParticipants($search, $statut, $evFilter);
$stats = $controller->getStats();
$evenements = $controller->getEventList();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — Gestion Participants</title>
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
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
      <?= htmlspecialchars($flash['msg']) ?>
      <button class="alert-close" onclick="this.parentElement.remove()">✕</button>
    </div>
    <?php endif; ?>

    <div class="page-strip">
      <div><div class="pg-title">👥 Gestion des Participants</div><div class="pg-sub">CRUD complet — BackOffice</div></div>
      <a href="p_form.php" class="btn btn-primary">＋ Nouveau participant</a>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon si-green">👥</div><div><div class="stat-value"><?= $stats['total'] ?></div><div class="stat-label">Total</div></div></div>
      <div class="stat-card"><div class="stat-icon si-green">✔</div><div><div class="stat-value"><?= $stats['confirmed'] ?></div><div class="stat-label">Confirmes</div></div></div>
      <div class="stat-card"><div class="stat-icon si-orange">⏳</div><div><div class="stat-value"><?= $stats['pending'] ?></div><div class="stat-label">En attente</div></div></div>
      <div class="stat-card"><div class="stat-icon si-gray">✕</div><div><div class="stat-value"><?= $stats['cancelled'] ?></div><div class="stat-label">Annules</div></div></div>
    </div>

    <div class="filter-bar">
      <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
        <input class="s-input" type="text" name="search" placeholder="🔍 Rechercher..." value="<?= htmlspecialchars($search) ?>">
        <select name="statut">
          <option value="">Tous statuts</option>
          <option value="confirmed" <?= $statut === 'confirmed' ? 'selected' : '' ?>>Confirmes</option>
          <option value="pending" <?= $statut === 'pending' ? 'selected' : '' ?>>En attente</option>
          <option value="cancelled" <?= $statut === 'cancelled' ? 'selected' : '' ?>>Annules</option>
        </select>
        <select name="ev">
          <option value="">Tous evenements</option>
          <?php foreach ($evenements as $e): ?>
          <option value="<?= $e['id'] ?>" <?= $evFilter === (int) $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['titre']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline btn-sm">Filtrer</button>
        <a href="participants.php" class="btn btn-outline btn-sm">Reset</a>
      </form>
    </div>

    <div class="card">
      <div class="card-body" style="padding:0">
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Participant</th><th>Email</th><th>Telephone</th><th>Evenement</th><th>Statut</th><th>Inscrit le</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="8"><div class="empty"><div class="empty-ic">👥</div><div class="empty-tt">Aucun participant</div></div></td></tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
              <tr>
                <td style="color:var(--g500);font-size:.78rem"><?= $r['id'] ?></td>
                <td>
                  <div style="display:flex;align-items:center;gap:8px">
                    <div class="av"><?= strtoupper(substr($r['prenom'], 0, 1) . substr($r['nom'], 0, 1)) ?></div>
                    <strong><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></strong>
                  </div>
                </td>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td><?= htmlspecialchars($r['telephone'] ?: '—') ?></td>
                <td><span class="badge b-blue" style="font-size:.68rem"><?= htmlspecialchars($r['ev_titre'] ?? '—') ?></span></td>
                <td>
                  <?php if ($r['statut'] === 'confirmed'): ?>
                    <span class="badge b-green">Confirme</span>
                  <?php elseif ($r['statut'] === 'pending'): ?>
                    <span class="badge b-orange">En attente</span>
                  <?php else: ?>
                    <span class="badge b-gray">Annule</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:.78rem;color:var(--g500)"><?= date('d/m/Y', strtotime($r['date_inscription'])) ?></td>
                <td>
                  <div style="display:flex;gap:4px">
                    <a href="p_form.php?id=<?= $r['id'] ?>" class="btn btn-outline btn-sm">✏</a>
                    <form method="POST" style="display:inline" onsubmit="return confirmDel('Supprimer ce participant ?')">
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
