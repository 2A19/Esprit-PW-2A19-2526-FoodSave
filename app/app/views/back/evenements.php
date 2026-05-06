<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementController.php';

$controller = new EvenementController();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $controller->delete((int) $_POST['id']);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Evenement supprimé.'];
    header('Location: evenements.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$statut = $_GET['statut'] ?? '';
$sort   = $_GET['sort'] ?? 'date_event';
$dir    = ($_GET['dir'] ?? 'asc') === 'asc' ? 'asc' : 'desc';
$allowedSort = ['titre','categorie','date_event','lieu','organisateur','capacite','statut'];
if (!in_array($sort, $allowedSort)) $sort = 'date_event';

$rows  = $controller->listEvents($search, $statut, '', $sort, $dir);
$stats = $controller->getStats();
$slabels = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
$sbadge  = ['upcoming' => 'b-green', 'ongoing' => 'b-orange', 'past' => 'b-gray'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — Gestion Evenements</title>
<link rel="stylesheet" href="../../../public/css/style.css">
<style>
[data-lang-btn] { padding:4px 10px;border-radius:6px;border:1.5px solid var(--g300);background:#fff;font-size:.78rem;font-weight:600;cursor:pointer;transition:all .2s; }
[data-lang-btn].active-lang { background:var(--green);color:#fff;border-color:var(--green); }
.timer-wrap { display:inline-flex;align-items:center;gap:3px;font-size:.72rem;flex-wrap:wrap; }
.timer-unit { background:var(--g900);color:#fff;padding:1px 4px;border-radius:3px;font-weight:700;font-family:var(--fh);font-size:.7rem; }
.timer-unit small { font-size:.5rem;opacity:.7; }
.timer-sep { color:var(--g500);font-weight:700; }
.timer-done { color:var(--green);font-weight:700;font-size:.75rem; }
.qr-container { display:none;padding:4px;text-align:center;background:#f8f9fa;border-radius:10px;margin-top:4px;border:1px solid #e0e0e0; }
.ai-panel { background:#fff;border:1px solid var(--g200);border-radius:12px;padding:16px;margin-bottom:18px; }
.ai-panel-title { font-family:var(--fh);font-size:.9rem;font-weight:800;color:var(--g900);margin-bottom:10px;display:flex;align-items:center;gap:8px; }
.spinner { width:14px;height:14px;border:2px solid var(--g300);border-top-color:var(--green);border-radius:50%;animation:spin .7s linear infinite;display:inline-block; }
.ai-loading { display:flex;align-items:center;gap:8px;color:var(--g500);font-size:.85rem; }
@keyframes spin { to{transform:rotate(360deg)} }
</style>
</head>
<body class="back-wrap">

<aside class="sidebar" id="sb">
  <div class="sb-brand"><div class="sb-icon">🌿</div><div class="sb-name"><span>Food</span><em>Save</em><small>Admin</small></div></div>
  <nav class="sb-nav">
    <div class="nav-lbl" data-tr="Gestion">Gestion</div>
    <a href="evenements.php"   class="nav-a active">📅 <span data-tr="Evenements">Evenements</span></a>
    <a href="participants.php" class="nav-a">👥 <span data-tr="Participants">Participants</span></a>
    <a href="statistiques.php" class="nav-a">📊 <span data-tr="Statistiques">Statistiques</span></a>
    <div class="nav-lbl" style="margin-top:10px" data-tr="Acces rapide">Acces rapide</div>
    <a href="../front/accueil.php" class="nav-a">🌐 <span data-tr="Voir le site">Voir le site</span></a>
  </nav>
  <div class="sb-footer">
    <div class="user-card"><div class="u-av">AD</div>
      <div><div class="u-name" data-tr="Administrateur">Administrateur</div><div class="u-role">BackOffice</div></div>
    </div>
  </div>
</aside>

<div class="main-wrap">
  <header class="topbar">
    <button class="ic-btn" onclick="document.getElementById('sb').classList.toggle('open')">☰</button>
    <div class="tb-title">🌿 FoodSave — BackOffice</div>
    <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
      <!-- Traduction FR / AR uniquement -->
      <button data-lang-btn="fr">🇫🇷</button>
      <button data-lang-btn="ar">🇹🇳</button>
      <!-- Export PDF -->
      <a href="export_pdf.php?type=evenements<?= $search?'&search='.urlencode($search):'' ?><?= $statut?'&statut='.urlencode($statut):'' ?>"
         class="btn btn-sm" style="background:linear-gradient(135deg,#e53935,#c62828);color:#fff">📄 PDF</a>
      <button class="btn btn-outline btn-sm" onclick="pushNotif('FoodSave','Notifications actives !')">🔔</button>
      <a href="statistiques.php" class="btn btn-outline btn-sm">📊 Stats</a>
      <a href="../front/accueil.php" class="btn btn-outline btn-sm">🌐 Front</a>
    </div>
  </header>

  <div class="content">

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>">
      <?= htmlspecialchars($flash['msg']) ?>
      <button class="alert-close" onclick="this.parentElement.remove()">✕</button>
    </div>
    <?php endif; ?>

    <div class="page-strip">
      <div>
        <div class="pg-title" data-tr="Gestion des Evenements">📅 Gestion des Evenements</div>
        <div class="pg-sub">CRUD · Filtre · Tri · QR · Timer · IA</div>
      </div>
      <a href="ev_form.php" class="btn btn-primary">＋ <span data-tr="Nouvel evenement">Nouvel evenement</span></a>
    </div>

    <!-- KPIs -->
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon si-green">📅</div><div><div class="stat-value"><?= $stats['total'] ?></div><div class="stat-label" data-tr="Total">Total</div></div></div>
      <div class="stat-card"><div class="stat-icon si-green">🔜</div><div><div class="stat-value"><?= $stats['upcoming'] ?></div><div class="stat-label" data-tr="A venir">A venir</div></div></div>
      <div class="stat-card"><div class="stat-icon si-orange">▶</div><div><div class="stat-value"><?= $stats['ongoing'] ?></div><div class="stat-label" data-tr="En cours">En cours</div></div></div>
      <div class="stat-card"><div class="stat-icon si-blue">✔</div><div><div class="stat-value"><?= $stats['past'] ?></div><div class="stat-label" data-tr="Termines">Termines</div></div></div>
    </div>

    <!-- IA Recommandations -->
    <div class="ai-panel">
      <div class="ai-panel-title">🤖 <span data-tr="Recommandations IA">Recommandations IA</span>
        <button class="btn btn-outline btn-sm" onclick="loadRecoEv()" style="margin-left:auto" data-tr="Analyser">Analyser</button>
        <button class="btn btn-outline btn-sm tts-btn"
                data-text="Gestion des événements FoodSave. Total : <?= $stats['total'] ?> événements. <?= $stats['upcoming'] ?> à venir, <?= $stats['ongoing'] ?> en cours, <?= $stats['past'] ?> terminés.">
          🔊 Lire
        </button>
      </div>
      <div id="aiRecoEv" style="color:var(--g500);font-size:.83rem">Cliquez sur « Analyser » pour des recommandations IA sur vos événements.</div>
    </div>

    <!-- Filtres — seulement Filtre instant (liveSearch supprimé) -->
    <div class="filter-bar">
      <div style="display:flex;gap:10px;flex-wrap:wrap;flex:1;align-items:center">

        <!-- FILTRE INSTANT côté client uniquement -->
        <input id="clientFilter" class="s-input" type="text"
               placeholder="⚡ Filtre instant..."
               style="min-width:220px">
        <span id="filterCount" style="font-size:.75rem;color:var(--g500)"></span>

        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <select name="statut" onchange="this.form.submit()">
            <option value="">Tous statuts</option>
            <option value="upcoming" <?= $statut==='upcoming'?'selected':'' ?>>A venir</option>
            <option value="ongoing"  <?= $statut==='ongoing' ?'selected':'' ?>>En cours</option>
            <option value="past"     <?= $statut==='past'    ?'selected':'' ?>>Termines</option>
          </select>
          <a href="evenements.php" class="btn btn-outline btn-sm" data-tr="Reset">Reset</a>
        </form>
      </div>
    </div>

    <!-- Tableau -->
    <div class="card">
      <div class="card-body" style="padding:0">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th data-sort="titre">Titre ↕</th>
                <th data-sort="categorie">Categorie ↕</th>
                <th data-sort="date_event">Date ↕</th>
                <th data-sort="lieu">Lieu ↕</th>
                <th data-sort="organisateur">Organisateur ↕</th>
                <th data-sort="capacite">Places ↕</th>
                <th data-sort="statut">Statut ↕</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="9"><div class="empty"><div class="empty-ic">📅</div><div class="empty-tt" data-tr="Aucun evenement">Aucun evenement</div></div></td></tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
              <?php
                // Construire les données QR complètes pour cet événement
                $qrData = implode('|', [
                    'FoodSave-Evenement',
                    'ID:'.$r['id'],
                    'Titre:'.htmlspecialchars_decode($r['titre']),
                    'Categorie:'.$r['categorie'],
                    'Date:'.date('d/m/Y',strtotime($r['date_event'])),
                    'Heure:'.substr($r['heure'],0,5),
                    'Lieu:'.htmlspecialchars_decode($r['lieu']),
                    'Organisateur:'.htmlspecialchars_decode($r['organisateur']),
                    'Capacite:'.$r['capacite'],
                    'Inscrits:'.$r['nb_p'],
                    'Statut:'.($slabels[$r['statut']]??$r['statut'])
                ]);
              ?>
              <tr data-filterable>
                <td style="color:var(--g500);font-size:.78rem"><?= $r['id'] ?></td>

                <!-- Titre (sans barcode) -->
                <td>
                  <strong><?= htmlspecialchars($r['titre']) ?></strong>
                </td>

                <td><span class="badge b-blue"><?= htmlspecialchars($r['categorie']) ?></span></td>

                <!-- Date + Timer -->
                <td>
                  <?= date('d/m/Y', strtotime($r['date_event'])) ?>
                  <br><small style="color:var(--g500)"><?= substr($r['heure'],0,5) ?></small>
                  <?php if ($r['statut'] === 'upcoming'): ?>
                  <div class="timer-wrap" style="margin-top:3px"
                       data-countdown="<?= $r['date_event'].' '.substr($r['heure'],0,5) ?>"></div>
                  <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($r['lieu']) ?></td>
                <td><?= htmlspecialchars($r['organisateur']) ?></td>

                <!-- Places + QR avec toutes les données -->
                <td>
                  <span style="font-size:.8rem"><?= $r['nb_p'] ?>/<?= $r['capacite'] ?></span>
                  <div class="progress-bar" style="margin-top:3px">
                    <div class="progress-fill" style="width:<?= $r['capacite']>0?min(100,round($r['nb_p']/$r['capacite']*100)):0 ?>%"></div>
                  </div>
                  <div>
                    <button class="btn btn-outline btn-sm" style="padding:2px 8px;font-size:.65rem;margin-top:4px"
                            data-qr="<?= htmlspecialchars($qrData) ?>"
                            data-qr-target="qrEv-<?= $r['id'] ?>">📲 QR</button>
                  </div>
                  <div class="qr-container" id="qrEv-<?= $r['id'] ?>"></div>
                </td>

                <td><span class="badge <?= $sbadge[$r['statut']]??'b-gray' ?>"><?= $slabels[$r['statut']]??$r['statut'] ?></span></td>

                <td>
                  <div style="display:flex;gap:4px;flex-wrap:wrap">
                    <a href="ev_show.php?id=<?= $r['id'] ?>" class="btn btn-outline btn-sm" title="Voir">👁</a>
                    <a href="ev_form.php?id=<?= $r['id'] ?>" class="btn btn-outline btn-sm" title="Modifier">✏</a>
                    <button class="btn btn-outline btn-sm tts-btn"
                            data-text="<?= htmlspecialchars('Événement : '.$r['titre'].'. '.($slabels[$r['statut']]??$r['statut']).'. Le '.date('d/m/Y',strtotime($r['date_event'])).' à '.substr($r['heure'],0,5).'. Lieu : '.$r['lieu'].'. '.$r['nb_p'].' inscrits sur '.$r['capacite'].' places.') ?>">🔊</button>
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
<script src="../../../public/js/features.js"></script>
<script>
const evStats = {
    total: <?= $stats['total'] ?>, upcoming: <?= $stats['upcoming'] ?>,
    ongoing: <?= $stats['ongoing'] ?>, past: <?= $stats['past'] ?>,
    total_places: <?= $stats['total_cap'] ?>
};
function loadRecoEv() {
    getRecommendations({
        type: 'evenements',
        stats: evStats,
        contexte: 'Plateforme FoodSave anti-gaspillage alimentaire, Tunisie'
    }, document.getElementById('aiRecoEv'));
}
</script>
</body>
</html>
