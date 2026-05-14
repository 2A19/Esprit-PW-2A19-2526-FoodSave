<?php
if(!isset($type)) {
    $type = 'evenements';
    $rows = $rows ?? [];
    $ev = $ev ?? null;
    $participants = $participants ?? [];
    $stats = $stats ?? [];
    $pstats = $pstats ?? [];
    $events = $events ?? [];
    $slabels = $slabels ?? [];
    $sbadge = $sbadge ?? [];
    $plabels = $plabels ?? [];
    $pbadge = $pbadge ?? [];
}
$autoprint = isset($_GET['autoprint']) && $_GET['autoprint'] == '1';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Export PDF</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:"DM Sans",Arial,sans-serif;background:#fff;color:#222;padding:40px;font-size:13px;line-height:1.6}
        .no-print{text-align:center;margin-bottom:30px}
        .btn-print{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#16a34a;color:#fff;border:none;border-radius:50px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;box-shadow:0 4px 20px rgba(22,163,74,0.3);transition:all 0.2s;text-decoration:none}
        .btn-print:hover{background:#15803d;transform:translateY(-2px)}
        .header{text-align:center;margin-bottom:30px;padding-bottom:20px;border-bottom:3px solid #16a34a}
        .header h1{font-size:24px;color:#16a34a;margin-bottom:4px}
        .header p{color:#666;font-size:14px}
        .header .date{color:#999;font-size:12px;margin-top:4px}
        .section-title{font-size:16px;font-weight:700;color:#16a34a;margin:24px 0 12px;padding-bottom:6px;border-bottom:1px solid #ddd}
        table{width:100%;border-collapse:collapse;margin-bottom:20px}
        th{background:#f0fdf4;color:#166534;padding:10px 14px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;border:1px solid #d1fae5}
        td{padding:9px 14px;border:1px solid #e5e7eb;color:#374151;font-size:12px}
        tr:nth-child(even) td{background:#f9fafb}
        .badge{display:inline-block;padding:2px 10px;border-radius:50px;font-size:11px;font-weight:600}
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
        .stat-item{background:#f0fdf4;border:1px solid #d1fae5;border-radius:10px;padding:16px;text-align:center}
        .stat-item .num{font-size:24px;font-weight:700;color:#16a34a}
        .stat-item .label{font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-top:2px}
        .ev-info{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
        .ev-info-item{padding:10px 14px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb}
        .ev-info-item .lbl{font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600}
        .ev-info-item .val{font-size:14px;color:#111;font-weight:500;margin-top:2px}
        @media print{.no-print{display:none}body{padding:20px}th{background:#e5e7eb!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}.stat-item{background:#f0fdf4!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}}
    </style>
</head>
<body>
<div class="no-print">
    <button class="btn-print" onclick="window.print()"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v8H6z"/></svg> Imprimer / Enregistrer PDF</button>
</div>
<div class="header">
    <h1>FoodSave - Rapport</h1>
    <p><?php echo $type === 'evenements' ? 'Liste des evenements' : ($type === 'evenement' ? 'Detail evenement' : ($type === 'participants' ? 'Liste des participants' : 'Statistiques')); ?></p>
    <div class="date">Genere le <?php echo date('d/m/Y H:i'); ?></div>
</div>

<?php if($type === 'evenements' && !empty($rows)): ?>
<table>
    <thead><tr><th>#</th><th>Titre</th><th>Categorie</th><th>Date</th><th>Lieu</th><th>Places</th><th>Statut</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
    <tr>
        <td><?php echo $r['id']; ?></td>
        <td><strong><?php echo htmlspecialchars($r['titre']); ?></strong></td>
        <td><?php echo htmlspecialchars($r['categorie'] ?? '-'); ?></td>
        <td><?php echo date('d/m/Y', strtotime($r['date_event'])); ?></td>
        <td><?php echo htmlspecialchars($r['lieu'] ?? '-'); ?></td>
        <td><?php echo ($r['nb_p'] ?? 0) . '/' . ($r['capacite'] ?? '-'); ?></td>
        <td><span class="badge" style="background:#d1fae5;color:#166534"><?php echo $slabels[$r['statut']] ?? $r['statut']; ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if($type === 'evenement' && $ev): ?>
<div class="section-title">Informations</div>
<div class="ev-info">
    <div class="ev-info-item"><div class="lbl">Titre</div><div class="val"><?php echo htmlspecialchars($ev['titre']); ?></div></div>
    <div class="ev-info-item"><div class="lbl">Categorie</div><div class="val"><?php echo htmlspecialchars($ev['categorie'] ?? '-'); ?></div></div>
    <div class="ev-info-item"><div class="lbl">Date</div><div class="val"><?php echo date('d/m/Y', strtotime($ev['date_event'])); ?></div></div>
    <div class="ev-info-item"><div class="lbl">Statut</div><div class="val"><span class="badge" style="background:#d1fae5;color:#166534"><?php echo $slabels[$ev['statut']] ?? $ev['statut']; ?></span></div></div>
    <div class="ev-info-item"><div class="lbl">Lieu</div><div class="val"><?php echo htmlspecialchars($ev['lieu'] ?? '-'); ?></div></div>
    <div class="ev-info-item"><div class="lbl">Organisateur</div><div class="val"><?php echo htmlspecialchars($ev['organisateur'] ?? '-'); ?></div></div>
</div>
<?php if(!empty($participants)): ?>
<div class="section-title">Participants (<?php echo count($participants); ?>)</div>
<table>
    <thead><tr><th>#</th><th>Nom</th><th>Email</th><th>Tel</th><th>Statut</th></tr></thead>
    <tbody>
    <?php foreach($participants as $p): ?>
    <tr>
        <td><?php echo $p['id']; ?></td>
        <td><?php echo htmlspecialchars(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '')); ?></td>
        <td><?php echo htmlspecialchars($p['email'] ?? '-'); ?></td>
        <td><?php echo htmlspecialchars($p['telephone'] ?? '-'); ?></td>
        <td><span class="badge" style="background:#d1fae5;color:#166534"><?php echo $plabels[$p['statut']] ?? $p['statut']; ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php endif; ?>

<?php if($type === 'participants' && !empty($rows)): ?>
<table>
    <thead><tr><th>#</th><th>Participant</th><th>Email</th><th>Telephone</th><th>Evenement</th><th>Statut</th><th>Inscrit le</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
    <tr>
        <td><?php echo $r['id']; ?></td>
        <td><?php echo htmlspecialchars(($r['prenom'] ?? '') . ' ' . ($r['nom'] ?? '')); ?></td>
        <td><?php echo htmlspecialchars($r['email'] ?? '-'); ?></td>
        <td><?php echo htmlspecialchars($r['telephone'] ?? '-'); ?></td>
        <td><?php echo htmlspecialchars($r['ev_titre'] ?? '-'); ?></td>
        <td><span class="badge" style="background:#d1fae5;color:#166534"><?php echo $plabels[$r['statut']] ?? $r['statut']; ?></span></td>
        <td><?php echo isset($r['date_inscription']) ? date('d/m/Y', strtotime($r['date_inscription'])) : '-'; ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if($type === 'stats'): ?>
<div class="section-title">Statistiques</div>
<div class="stats-grid">
    <div class="stat-item"><div class="num"><?php echo $stats['total'] ?? 0; ?></div><div class="label">Evenements</div></div>
    <div class="stat-item"><div class="num"><?php echo $stats['upcoming'] ?? 0; ?></div><div class="label">A venir</div></div>
    <div class="stat-item"><div class="num"><?php echo $stats['ongoing'] ?? 0; ?></div><div class="label">En cours</div></div>
    <div class="stat-item"><div class="num"><?php echo $stats['past'] ?? 0; ?></div><div class="label">Passes</div></div>
    <div class="stat-item"><div class="num"><?php echo $pstats['total'] ?? 0; ?></div><div class="label">Participants</div></div>
    <div class="stat-item"><div class="num"><?php echo $pstats['confirmed'] ?? 0; ?></div><div class="label">Confirmes</div></div>
    <div class="stat-item"><div class="num"><?php echo $pstats['pending'] ?? 0; ?></div><div class="label">En attente</div></div>
    <div class="stat-item"><div class="num"><?php echo $pstats['cancelled'] ?? 0; ?></div><div class="label">Annules</div></div>
</div>
<div class="section-title">Evenements par categorie</div>
<table>
    <thead><tr><th>Categorie</th><th>Nombre</th></tr></thead>
    <tbody>
    <?php
    $catCount = [];
    foreach($events as $e){
        $c = $e['categorie'] ?? 'Autre';
        if(!isset($catCount[$c])) $catCount[$c] = 0;
        $catCount[$c]++;
    }
    foreach($catCount as $cat => $cnt):
    ?>
    <tr><td><?php echo htmlspecialchars($cat); ?></td><td><?php echo $cnt; ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php if($autoprint): ?>
<script>window.onload=function(){window.print();}</script>
<?php endif; ?>
</body>
</html>
