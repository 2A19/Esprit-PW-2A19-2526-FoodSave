<?php
/**
 * export_pdf.php — Export PDF via page HTML imprimable
 * Usage : export_pdf.php?type=evenements|participants|stats
 *         export_pdf.php?type=evenement&id=X
 */
session_start();
require_once __DIR__ . '/../../controller/EvenementController.php';
require_once __DIR__ . '/../../controller/ParticipantController.php';

$type = $_GET['type'] ?? 'evenements';
$evCtrl = new EvenementController();
$pCtrl  = new ParticipantController();

// Filtre passé en GET
$search   = trim($_GET['search'] ?? '');
$statut   = $_GET['statut'] ?? '';
$evFilter = (int)($_GET['ev'] ?? 0);

$title    = 'Export';
$content  = '';
$date     = date('d/m/Y H:i');

if ($type === 'evenements') {
    $title = 'Liste des Evenements';
    $rows  = $evCtrl->listEvents($search, $statut);
    $stats = $evCtrl->getStats();

    ob_start();
    ?>
    <div class="section-title">📊 Résumé</div>
    <div class="kpi-row">
      <div class="kpi"><span class="kv"><?= $stats['total'] ?></span><span class="kl">Total</span></div>
      <div class="kpi"><span class="kv g"><?= $stats['upcoming'] ?></span><span class="kl">A venir</span></div>
      <div class="kpi"><span class="kv o"><?= $stats['ongoing'] ?></span><span class="kl">En cours</span></div>
      <div class="kpi"><span class="kv gr"><?= $stats['past'] ?></span><span class="kl">Termines</span></div>
    </div>

    <div class="section-title" style="margin-top:16px">📋 Evenements (<?= count($rows) ?>)</div>
    <table>
      <thead>
        <tr><th>#</th><th>Titre</th><th>Categorie</th><th>Date</th><th>Lieu</th><th>Organisateur</th><th>Places</th><th>Statut</th></tr>
      </thead>
      <tbody>
      <?php
      $slabels = ['upcoming'=>'A venir','ongoing'=>'En cours','past'=>'Termine'];
      foreach ($rows as $r):
        $pct = $r['capacite'] > 0 ? min(100,round($r['nb_p']/$r['capacite']*100)) : 0;
      ?>
      <tr>
        <td style="color:#999;font-size:.7rem"><?= $r['id'] ?></td>
        <td><strong><?= htmlspecialchars($r['titre']) ?></strong></td>
        <td><span class="badge"><?= htmlspecialchars($r['categorie']) ?></span></td>
        <td><?= date('d/m/Y', strtotime($r['date_event'])) ?><br><small style="color:#999"><?= substr($r['heure'],0,5) ?></small></td>
        <td><?= htmlspecialchars($r['lieu']) ?></td>
        <td><?= htmlspecialchars($r['organisateur']) ?></td>
        <td><?= $r['nb_p'] ?>/<?= $r['capacite'] ?> <small style="color:#999">(<?= $pct ?>%)</small></td>
        <td><span class="badge s-<?= $r['statut'] ?>"><?= $slabels[$r['statut']] ?? $r['statut'] ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php
    $content = ob_get_clean();

} elseif ($type === 'evenement' && isset($_GET['id'])) {
    $ev = $evCtrl->findById((int)$_GET['id']);
    if (!$ev) { die('Evenement introuvable'); }
    $parts = $pCtrl->listParticipants('','',  (int)$_GET['id']);
    $title = 'Fiche Evenement : ' . htmlspecialchars($ev['titre']);
    $slabels = ['upcoming'=>'A venir','ongoing'=>'En cours','past'=>'Termine'];
    $nb_p  = $evCtrl->countParticipants((int)$_GET['id']);
    $pct   = $ev['capacite'] > 0 ? min(100,round($nb_p/$ev['capacite']*100)) : 0;

    ob_start(); ?>
    <div class="ev-header">
      <h2><?= htmlspecialchars($ev['titre']) ?></h2>
      <span class="badge s-<?= $ev['statut'] ?>"><?= $slabels[$ev['statut']] ?? $ev['statut'] ?></span>
    </div>
    <div class="info-grid">
      <div><span class="lbl">Categorie</span><span><?= htmlspecialchars($ev['categorie']) ?></span></div>
      <div><span class="lbl">Date</span><span><?= date('d/m/Y', strtotime($ev['date_event'])) ?> à <?= substr($ev['heure'],0,5) ?></span></div>
      <div><span class="lbl">Lieu</span><span><?= htmlspecialchars($ev['lieu']) ?></span></div>
      <div><span class="lbl">Organisateur</span><span><?= htmlspecialchars($ev['organisateur']) ?></span></div>
      <div><span class="lbl">Capacite</span><span><?= $ev['capacite'] ?> places</span></div>
      <div><span class="lbl">Inscrits</span><span><?= $nb_p ?> / <?= $ev['capacite'] ?> (<?= $pct ?>%)</span></div>
    </div>
    <?php if ($ev['description']): ?>
    <div style="margin:12px 0"><span class="lbl">Description</span><p style="margin-top:4px;color:#546E7A;font-size:.88rem"><?= nl2br(htmlspecialchars($ev['description'])) ?></p></div>
    <?php endif; ?>

    <div class="section-title" style="margin-top:16px">👥 Participants (<?= count($parts) ?>)</div>
    <?php if (empty($parts)): ?>
    <p style="color:#999;font-size:.88rem">Aucun participant inscrit.</p>
    <?php else: ?>
    <table>
      <thead><tr><th>#</th><th>Participant</th><th>Email</th><th>Telephone</th><th>Statut</th><th>Inscrit le</th></tr></thead>
      <tbody>
      <?php
      $plabels = ['confirmed'=>'Confirme','pending'=>'En attente','cancelled'=>'Annule'];
      foreach ($parts as $p): ?>
      <tr>
        <td style="color:#999;font-size:.7rem"><?= $p['id'] ?></td>
        <td><strong><?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?></strong></td>
        <td><?= htmlspecialchars($p['email']) ?></td>
        <td><?= htmlspecialchars($p['telephone'] ?: '—') ?></td>
        <td><span class="badge ps-<?= $p['statut'] ?>"><?= $plabels[$p['statut']] ?? $p['statut'] ?></span></td>
        <td><?= date('d/m/Y', strtotime($p['date_inscription'])) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
    <?php
    $content = ob_get_clean();

} elseif ($type === 'participants') {
    $title = 'Liste des Participants';
    $rows  = $pCtrl->listParticipants($search, $statut, $evFilter);
    $stats = $pCtrl->getStats();

    ob_start(); ?>
    <div class="section-title">📊 Résumé</div>
    <div class="kpi-row">
      <div class="kpi"><span class="kv"><?= $stats['total'] ?></span><span class="kl">Total</span></div>
      <div class="kpi"><span class="kv g"><?= $stats['confirmed'] ?></span><span class="kl">Confirmes</span></div>
      <div class="kpi"><span class="kv o"><?= $stats['pending'] ?></span><span class="kl">En attente</span></div>
      <div class="kpi"><span class="kv r"><?= $stats['cancelled'] ?></span><span class="kl">Annules</span></div>
    </div>

    <div class="section-title" style="margin-top:16px">📋 Participants (<?= count($rows) ?>)</div>
    <table>
      <thead><tr><th>#</th><th>Participant</th><th>Email</th><th>Telephone</th><th>Evenement</th><th>Statut</th><th>Inscrit le</th></tr></thead>
      <tbody>
      <?php
      $plabels = ['confirmed'=>'Confirme','pending'=>'En attente','cancelled'=>'Annule'];
      foreach ($rows as $p): ?>
      <tr>
        <td style="color:#999;font-size:.7rem"><?= $p['id'] ?></td>
        <td><strong><?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?></strong></td>
        <td><?= htmlspecialchars($p['email']) ?></td>
        <td><?= htmlspecialchars($p['telephone'] ?: '—') ?></td>
        <td><span class="badge"><?= htmlspecialchars($p['ev_titre'] ?? '—') ?></span></td>
        <td><span class="badge ps-<?= $p['statut'] ?>"><?= $plabels[$p['statut']] ?? $p['statut'] ?></span></td>
        <td><?= date('d/m/Y', strtotime($p['date_inscription'])) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php
    $content = ob_get_clean();

} elseif ($type === 'stats') {
    $title   = 'Rapport Statistiques';
    $evStats = $evCtrl->getStats();
    $pStats  = $pCtrl->getStats();
    $db      = Database::getConnection();

    $byCategorie = $db->query(
        "SELECT categorie, COUNT(*) AS nb_ev, COALESCE(SUM(capacite),0) AS total_places
         FROM evenements GROUP BY categorie ORDER BY nb_ev DESC"
    )->fetchAll();

    $topEv = $db->query(
        "SELECT e.titre, e.capacite, COUNT(p.id) AS nb_p,
                ROUND(COUNT(p.id)/NULLIF(e.capacite,0)*100,1) AS taux
         FROM evenements e
         LEFT JOIN participants p ON p.evenement_id = e.id AND p.statut != 'cancelled'
         GROUP BY e.id ORDER BY taux DESC LIMIT 5"
    )->fetchAll();

    $taux = $pStats['total'] > 0 ? round($pStats['confirmed']/$pStats['total']*100) : 0;

    ob_start(); ?>
    <div class="section-title">📅 Evenements</div>
    <div class="kpi-row">
      <div class="kpi"><span class="kv"><?= $evStats['total'] ?></span><span class="kl">Total</span></div>
      <div class="kpi"><span class="kv g"><?= $evStats['upcoming'] ?></span><span class="kl">A venir</span></div>
      <div class="kpi"><span class="kv o"><?= $evStats['ongoing'] ?></span><span class="kl">En cours</span></div>
      <div class="kpi"><span class="kv gr"><?= $evStats['past'] ?></span><span class="kl">Termines</span></div>
    </div>

    <div class="section-title" style="margin-top:14px">👥 Participants</div>
    <div class="kpi-row">
      <div class="kpi"><span class="kv"><?= $pStats['total'] ?></span><span class="kl">Total</span></div>
      <div class="kpi"><span class="kv g"><?= $pStats['confirmed'] ?></span><span class="kl">Confirmes</span></div>
      <div class="kpi"><span class="kv o"><?= $pStats['pending'] ?></span><span class="kl">En attente</span></div>
      <div class="kpi"><span class="kv r"><?= $pStats['cancelled'] ?></span><span class="kl">Annules</span></div>
      <div class="kpi"><span class="kv g"><?= $taux ?>%</span><span class="kl">Taux confirmation</span></div>
    </div>

    <div class="section-title" style="margin-top:14px">📂 Par catégorie</div>
    <table>
      <thead><tr><th>Categorie</th><th>Nb evenements</th><th>Places totales</th></tr></thead>
      <tbody>
      <?php foreach ($byCategorie as $c): ?>
      <tr>
        <td><strong><?= htmlspecialchars($c['categorie']) ?></strong></td>
        <td><?= $c['nb_ev'] ?></td>
        <td><?= number_format($c['total_places']) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <div class="section-title" style="margin-top:14px">🏆 Top 5 evenements</div>
    <table>
      <thead><tr><th>#</th><th>Evenement</th><th>Inscrits</th><th>Capacite</th><th>Taux</th></tr></thead>
      <tbody>
      <?php foreach ($topEv as $i => $t): ?>
      <tr>
        <td style="color:#999"><?= $i+1 ?></td>
        <td><strong><?= htmlspecialchars($t['titre']) ?></strong></td>
        <td><?= $t['nb_p'] ?></td>
        <td><?= $t['capacite'] ?></td>
        <td><?= $t['taux'] ?>%</td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php
    $content = ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>FoodSave — <?= htmlspecialchars($title) ?></title>
<style>
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family:'Segoe UI',Arial,sans-serif; font-size:13px; color:#263238; background:#fff; padding:24px; }

  /* En-tête */
  .pdf-header { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid #4CAF50; padding-bottom:14px; margin-bottom:20px; }
  .pdf-logo   { display:flex; align-items:center; gap:10px; }
  .pdf-logo-icon { width:40px; height:40px; background:linear-gradient(135deg,#4CAF50,#388E3C); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; }
  .pdf-logo-name { font-size:1.2rem; font-weight:800; color:#263238; }
  .pdf-logo-name em { color:#4CAF50; font-style:normal; }
  .pdf-meta   { text-align:right; font-size:.75rem; color:#90A4AE; line-height:1.6; }
  .pdf-title  { font-size:1.15rem; font-weight:700; color:#263238; margin-bottom:2px; }

  /* Sections */
  .section-title { font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#90A4AE; margin-bottom:10px; }
  .kpi-row { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:6px; }
  .kpi { background:#F5F7FA; border-radius:8px; padding:12px 18px; text-align:center; min-width:90px; border:1px solid #ECEFF1; }
  .kv { display:block; font-size:1.6rem; font-weight:900; line-height:1; }
  .kl { display:block; font-size:.68rem; color:#90A4AE; margin-top:2px; font-weight:500; }
  .g { color:#4CAF50; } .o { color:#FFA726; } .r { color:#ef5350; } .gr { color:#90A4AE; }

  /* Table */
  table { width:100%; border-collapse:collapse; font-size:.82rem; margin-bottom:8px; }
  thead tr { background:#F5F7FA; }
  th { padding:8px 10px; text-align:left; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#90A4AE; border-bottom:2px solid #ECEFF1; }
  td { padding:8px 10px; border-bottom:1px solid #ECEFF1; vertical-align:middle; }
  tr:last-child td { border-bottom:none; }

  /* Badges */
  .badge { display:inline-block; padding:2px 8px; border-radius:50px; font-size:.68rem; font-weight:700; background:#E3F2FD; color:#1565C0; }
  .s-upcoming { background:#E8F5E9; color:#388E3C; }
  .s-ongoing  { background:#FFF3E0; color:#EF6C00; }
  .s-past     { background:#ECEFF1; color:#546E7A; }
  .ps-confirmed { background:#E8F5E9; color:#388E3C; }
  .ps-pending   { background:#FFF3E0; color:#EF6C00; }
  .ps-cancelled { background:#FFEBEE; color:#c62828; }

  /* Fiche event */
  .ev-header { display:flex; align-items:center; gap:12px; margin-bottom:14px; }
  .ev-header h2 { font-size:1.1rem; font-weight:800; }
  .info-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:10px; }
  .info-grid > div { background:#F5F7FA; border-radius:7px; padding:10px; border:1px solid #ECEFF1; }
  .lbl { display:block; font-size:.68rem; font-weight:700; text-transform:uppercase; color:#90A4AE; letter-spacing:.04em; margin-bottom:3px; }

  /* Pied de page */
  .pdf-footer { margin-top:24px; padding-top:12px; border-top:1px solid #ECEFF1; display:flex; justify-content:space-between; font-size:.72rem; color:#90A4AE; }

  /* Bouton impression */
  .print-bar { position:fixed; top:0; left:0; right:0; background:#fff; border-bottom:1px solid #ECEFF1; padding:10px 24px; display:flex; justify-content:space-between; align-items:center; z-index:999; box-shadow:0 2px 10px rgba(0,0,0,.08); }
  .print-bar .pb-title { font-weight:700; font-size:.9rem; }
  .pb-btns { display:flex; gap:8px; }
  .btn-p { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:7px; font-size:.83rem; font-weight:600; cursor:pointer; border:none; text-decoration:none; }
  .btn-print { background:linear-gradient(135deg,#e53935,#c62828); color:#fff; }
  .btn-back  { background:#ECEFF1; color:#546E7A; }

  @media print {
    .print-bar { display:none; }
    body { padding:10px; }
    @page { margin:1.5cm; }
  }
  body { padding-top: 58px; }
  @media print { body { padding-top:0; } }
</style>
</head>
<body>

<!-- Barre d'impression -->
<div class="print-bar">
  <span class="pb-title">📄 <?= htmlspecialchars($title) ?></span>
  <div class="pb-btns">
    <button class="btn-p btn-print" onclick="window.print()">🖨 Imprimer / Enregistrer PDF</button>
    <button class="btn-p btn-back" onclick="window.history.back()">← Retour</button>
  </div>
</div>

<!-- En-tête document -->
<div class="pdf-header">
  <div class="pdf-logo">
    <div class="pdf-logo-icon">🌿</div>
    <div>
      <div class="pdf-logo-name"><span>Food</span><em>Save</em></div>
      <div style="font-size:.72rem;color:#90A4AE">BackOffice — Export</div>
    </div>
  </div>
  <div class="pdf-meta">
    <div class="pdf-title"><?= htmlspecialchars($title) ?></div>
    <div>Généré le <?= $date ?></div>
    <div>FoodSave Admin</div>
  </div>
</div>

<!-- Contenu -->
<?= $content ?>

<!-- Pied de page -->
<div class="pdf-footer">
  <span>FoodSave — BackOffice</span>
  <span>Export du <?= $date ?></span>
</div>

<script>
// Auto-print si paramètre autoprint=1
if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
  window.onload = () => window.print();
}
</script>
</body>
</html>
