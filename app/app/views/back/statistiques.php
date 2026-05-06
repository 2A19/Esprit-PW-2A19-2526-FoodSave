<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementController.php';
require_once __DIR__ . '/../../controller/ParticipantController.php';

$evCtrl = new EvenementController();
$pCtrl  = new ParticipantController();

$evStats = $evCtrl->getStats();
$pStats  = $pCtrl->getStats();

// Stats par catégorie
$db = Database::getConnection();

$byCategorie = $db->query(
    "SELECT categorie,
            COUNT(*) AS nb_ev,
            COALESCE(SUM(capacite),0) AS total_places
     FROM evenements
     GROUP BY categorie
     ORDER BY nb_ev DESC"
)->fetchAll();

// Top 5 événements les plus remplis
$topEv = $db->query(
    "SELECT e.titre, e.capacite,
            COUNT(p.id) AS nb_p,
            ROUND(COUNT(p.id)/NULLIF(e.capacite,0)*100,1) AS taux
     FROM evenements e
     LEFT JOIN participants p ON p.evenement_id = e.id AND p.statut != 'cancelled'
     GROUP BY e.id
     ORDER BY taux DESC
     LIMIT 5"
)->fetchAll();

// Inscriptions par mois (12 derniers mois)
$inscMoisRaw = $db->query(
    "SELECT DATE_FORMAT(date_inscription,'%Y-%m') AS mois,
            COUNT(*) AS nb
     FROM participants
     WHERE date_inscription >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
     GROUP BY mois
     ORDER BY mois ASC"
)->fetchAll();

// Construire un tableau complet des 12 derniers mois (inclut les mois à 0)
$inscByMois = [];
foreach ($inscMoisRaw as $r) { $inscByMois[$r['mois']] = (int)$r['nb']; }
$inscMois = [];
for ($i = 11; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("-$i months"));
    $inscMois[] = ['mois' => $key, 'nb' => $inscByMois[$key] ?? 0];
}

$currentUrl = basename($_SERVER['PHP_SELF']);
$totalParticipants = $pStats['total'];
$tauxConfirmation  = $totalParticipants > 0
    ? round($pStats['confirmed'] / $totalParticipants * 100)
    : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — Statistiques</title>
<link rel="stylesheet" href="../../../public/css/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
.charts-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(340px,1fr)); gap:20px; margin-bottom:24px; }
.chart-card  { background:#fff; border-radius:14px; border:1px solid var(--g200); box-shadow:var(--shadow); padding:20px; }
.chart-title { font-family:var(--fh); font-size:.95rem; font-weight:800; color:var(--g900); margin-bottom:16px; display:flex; align-items:center; gap:8px; }
canvas       { max-height:260px; }
.kpi-row     { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:16px; margin-bottom:24px; }
.kpi-card    { background:#fff; border-radius:14px; border:1px solid var(--g200); box-shadow:var(--shadow); padding:20px 18px; text-align:center; }
.kpi-value   { font-family:var(--fh); font-size:2.2rem; font-weight:900; color:var(--green); line-height:1; }
.kpi-label   { font-size:.74rem; color:var(--g500); margin-top:4px; font-weight:500; }
.kpi-sub     { font-size:.68rem; color:var(--g500); margin-top:2px; }
.tbl-stats   { width:100%; border-collapse:collapse; font-size:.84rem; }
.tbl-stats th{ padding:9px 13px; text-align:left; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--g500); background:var(--g100); }
.tbl-stats td{ padding:10px 13px; border-bottom:1px solid var(--g200); vertical-align:middle; }
.tbl-stats tr:last-child td { border-bottom:none; }
.tbl-stats tbody tr:hover { background:var(--green-bg); }
.bar-mini    { height:6px; border-radius:3px; background:var(--green); }
.btn-export  { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-family:var(--fb); font-size:.85rem; font-weight:600; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
.btn-pdf     { background:linear-gradient(135deg,#e53935,#c62828); color:#fff; box-shadow:0 4px 12px rgba(229,57,53,.3); }
.btn-pdf:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(229,57,53,.4); }
@media print {
  .sidebar, .topbar, .no-print { display:none !important; }
  .main-wrap { margin:0 !important; }
  body { background:#fff; }
}
</style>
</head>
<body class="back-wrap">

<aside class="sidebar" id="sb">
  <div class="sb-brand">
    <div class="sb-icon">🌿</div>
    <div class="sb-name"><span>Food</span><em>Save</em><small>Admin</small></div>
  </div>
  <nav class="sb-nav">
    <div class="nav-lbl">Gestion</div>
    <a href="evenements.php"   class="nav-a">📅 Evenements</a>
    <a href="participants.php" class="nav-a">👥 Participants</a>
    <a href="statistiques.php" class="nav-a active">📊 Statistiques</a>
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
    <div style="display:flex;gap:8px">
      <a href="export_pdf.php?type=stats" class="btn-export btn-pdf no-print">📄 Exporter PDF</a>
      <a href="../front/accueil.php" class="btn btn-outline btn-sm no-print">🌐 Front</a>
    </div>
  </header>

  <div class="content">
    <div class="page-strip">
      <div>
        <div class="pg-title">📊 Tableau de bord statistiques</div>
        <div class="pg-sub">Vue globale de l'activite FoodSave</div>
      </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-row">
      <div class="kpi-card">
        <div class="kpi-value"><?= $evStats['total'] ?></div>
        <div class="kpi-label">Evenements total</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-value" style="color:var(--green)"><?= $evStats['upcoming'] ?></div>
        <div class="kpi-label">A venir</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-value" style="color:var(--orange)"><?= $evStats['ongoing'] ?></div>
        <div class="kpi-label">En cours</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-value" style="color:#90A4AE"><?= $evStats['past'] ?></div>
        <div class="kpi-label">Termines</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-value"><?= $pStats['total'] ?></div>
        <div class="kpi-label">Participants total</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-value" style="color:var(--green)"><?= $tauxConfirmation ?>%</div>
        <div class="kpi-label">Taux confirmation</div>
        <div class="kpi-sub"><?= $pStats['confirmed'] ?> confirmés</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-value" style="color:var(--orange)"><?= $pStats['pending'] ?></div>
        <div class="kpi-label">En attente</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-value" style="color:#ef5350"><?= $pStats['cancelled'] ?></div>
        <div class="kpi-label">Annules</div>
      </div>
    </div>

    <!-- Graphiques -->
    <div class="charts-grid">

      <!-- Statuts événements (Donut) -->
      <div class="chart-card">
        <div class="chart-title">📅 Repartition des evenements</div>
        <canvas id="chartEv"></canvas>
      </div>

      <!-- Statuts participants (Donut) -->
      <div class="chart-card">
        <div class="chart-title">👥 Statuts des participants</div>
        <canvas id="chartPart"></canvas>
      </div>

      <!-- Inscriptions par mois (Line) -->
      <div class="chart-card" style="grid-column:span 2">
        <div class="chart-title">📈 Inscriptions par mois (12 derniers mois)</div>
        <canvas id="chartMois"></canvas>
      </div>

    </div>

    <!-- Table catégories -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-header">
        <div class="card-title">📂 Evenements par categorie</div>
      </div>
      <div class="card-body" style="padding:0">
        <table class="tbl-stats">
          <thead>
            <tr><th>Categorie</th><th>Nb evenements</th><th>Places totales</th><th>Repartition</th></tr>
          </thead>
          <tbody>
          <?php
            $totalEv = max(1, array_sum(array_column($byCategorie, 'nb_ev')));
            foreach ($byCategorie as $c):
              $pct = round($c['nb_ev'] / $totalEv * 100);
          ?>
          <tr>
            <td><strong><?= htmlspecialchars($c['categorie']) ?></strong></td>
            <td><?= $c['nb_ev'] ?></td>
            <td><?= number_format($c['total_places']) ?></td>
            <td style="min-width:120px">
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;background:var(--g200);border-radius:3px;height:6px">
                  <div class="bar-mini" style="width:<?= $pct ?>%"></div>
                </div>
                <span style="font-size:.78rem;color:var(--g500);width:30px"><?= $pct ?>%</span>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Top événements -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">🏆 Top 5 evenements (taux de remplissage)</div>
      </div>
      <div class="card-body" style="padding:0">
        <table class="tbl-stats">
          <thead>
            <tr><th>#</th><th>Evenement</th><th>Inscrits</th><th>Capacite</th><th>Taux</th></tr>
          </thead>
          <tbody>
          <?php foreach ($topEv as $i => $t): $taux = (float)$t['taux']; ?>
          <tr>
            <td style="color:var(--g500);font-size:.78rem"><?= $i+1 ?></td>
            <td><strong><?= htmlspecialchars($t['titre']) ?></strong></td>
            <td><?= $t['nb_p'] ?></td>
            <td><?= $t['capacite'] ?></td>
            <td style="min-width:140px">
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;background:var(--g200);border-radius:3px;height:6px">
                  <div class="bar-mini" style="width:<?= min(100,$taux) ?>%;background:<?= $taux>=80 ? 'var(--green)' : ($taux>=50 ? 'var(--orange)' : '#90A4AE') ?>"></div>
                </div>
                <span style="font-size:.78rem;color:var(--g500);width:36px"><?= $taux ?>%</span>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main-wrap -->

<script>
const GREEN  = '#4CAF50';
const ORANGE = '#FFA726';
const GRAY   = '#90A4AE';
const BLUE   = '#42A5F5';
const RED    = '#ef5350';

// Donut événements
new Chart(document.getElementById('chartEv'), {
  type: 'doughnut',
  data: {
    labels: ['A venir', 'En cours', 'Termines'],
    datasets:[{ data:[<?= $evStats['upcoming'] ?>,<?= $evStats['ongoing'] ?>,<?= $evStats['past'] ?>],
      backgroundColor:[GREEN,ORANGE,GRAY], borderWidth:2, borderColor:'#fff', hoverOffset:6 }]
  },
  options:{ plugins:{ legend:{ position:'bottom', labels:{ font:{size:12}, padding:14 }}}, cutout:'60%', maintainAspectRatio:false }
});

// Donut participants
new Chart(document.getElementById('chartPart'), {
  type: 'doughnut',
  data: {
    labels: ['Confirmes', 'En attente', 'Annules'],
    datasets:[{ data:[<?= $pStats['confirmed'] ?>,<?= $pStats['pending'] ?>,<?= $pStats['cancelled'] ?>],
      backgroundColor:[GREEN,ORANGE,RED], borderWidth:2, borderColor:'#fff', hoverOffset:6 }]
  },
  options:{ plugins:{ legend:{ position:'bottom', labels:{ font:{size:12}, padding:14 }}}, cutout:'60%', maintainAspectRatio:false }
});

// Line inscriptions par mois
const moisLabels = <?= json_encode(array_column($inscMois,'mois')) ?>;
const moisData   = <?= json_encode(array_column($inscMois,'nb')) ?>;
new Chart(document.getElementById('chartMois'), {
  type:'line',
  data:{
    labels: moisLabels,
    datasets:[{
      label:'Inscriptions',
      data: moisData,
      borderColor: GREEN,
      backgroundColor:'rgba(76,175,80,.1)',
      fill:true,
      tension:.4,
      pointBackgroundColor: GREEN,
      pointRadius:4
    }]
  },
  options:{
    plugins:{ legend:{ display:false }},
    scales:{
      y:{ beginAtZero:true, ticks:{ stepSize:1 }, grid:{ color:'rgba(0,0,0,.05)' }},
      x:{ grid:{ display:false }}
    },
    maintainAspectRatio:false
  }
});
</script>
</body>
</html>
