<?php if (session_status() === PHP_SESSION_NONE) session_start();

/* ── Prépare toutes les données PHP avant d'émettre du HTML ── */
$total    = (int)($stats['total']    ?? 0);
$totalKg  = round((float)($stats['total_kg'] ?? 0), 2);
$avgKg    = round((float)($stats['avg_kg']   ?? 0), 2);
$lastDate = $stats['last_date'] ?? '—';
$maxKgByType = (!empty($byType)) ? max(array_column($byType, 'total_kg')) : 1;

$colorPalette = ['#4ade80','#facc15','#fb923c','#60a5fa','#c084fc',
                 '#34d399','#f472b6','#a78bfa','#38bdf8','#fb7185'];

$jsMoisLabels    = json_encode(array_column($byMonth,    'mois'));
$jsMoisData      = json_encode(array_map('floatval', array_column($byMonth,    'total_kg')));
$jsRaisonLabels  = json_encode(array_column($byRaison,   'raison'));
$jsRaisonData    = json_encode(array_map('floatval', array_column($byRaison,   'total_kg')));
$jsRaisonColors  = json_encode(array_slice($colorPalette, 0, count($byRaison)));
$jsCatLabels     = json_encode(array_column($byCategorie,'categorie'));
$jsCatData       = json_encode(array_map('floatval', array_column($byCategorie,'total_kg')));
$jsCatColors     = json_encode(array_map(fn($r) => $r['couleur'] ?? '#4ade80', $byCategorie));
$jsTypeLabels    = json_encode(array_column($byType,     'type_aliment'));
$jsTypeData      = json_encode(array_map('floatval', array_column($byType,     'total_kg')));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques Déchets — FoodSave Admin</title>
    <link rel="icon" type="image/png" href="assets/images/logo-foodsave.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px}
        .stat-card{background:var(--color-surface,#1a2e1a);border:1px solid var(--color-border,rgba(74,222,128,.1));border-radius:14px;padding:20px 24px;display:flex;flex-direction:column;gap:6px}
        .stat-card .lbl{font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--color-text-muted,#81c784)}
        .stat-card .val{font-size:2rem;font-weight:700;color:#4ade80}
        .stat-card .sub{font-size:.8rem;color:var(--color-text-muted,#81c784);opacity:.7}
        .charts-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
        @media(max-width:900px){.charts-row{grid-template-columns:1fr}}
        .chart-card{background:var(--color-surface,#1a2e1a);border:1px solid var(--color-border,rgba(74,222,128,.1));border-radius:14px;padding:20px 24px}
        .chart-card h3{font-size:.85rem;font-weight:600;color:#a5d6a7;margin:0 0 14px;text-transform:uppercase;letter-spacing:.5px}
        .chart-card canvas{max-height:260px}
        .table-card{background:var(--color-surface,#1a2e1a);border:1px solid var(--color-border,rgba(74,222,128,.1));border-radius:14px;overflow:hidden;margin-bottom:24px}
        .table-card h3{font-size:.85rem;font-weight:600;color:#a5d6a7;margin:0;padding:16px 20px;border-bottom:1px solid rgba(74,222,128,.08);text-transform:uppercase;letter-spacing:.5px}
        .table-card table{width:100%;border-collapse:collapse}
        .table-card th{padding:10px 16px;background:rgba(74,222,128,.05);color:#81c784;font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;text-align:left}
        .table-card td{padding:10px 16px;border-top:1px solid rgba(74,222,128,.05);font-size:.875rem;color:#e2e8f0}
        .badge-r{display:inline-block;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:600;background:rgba(74,222,128,.12);color:#4ade80;border:1px solid rgba(74,222,128,.2)}
        .bar-w{background:rgba(74,222,128,.08);border-radius:6px;height:6px;margin-top:4px;overflow:hidden}
        .bar-f{height:100%;border-radius:6px;background:linear-gradient(90deg,#16a34a,#4ade80)}
        .btn-export{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:10px;border:none;cursor:pointer;font-size:.85rem;font-weight:600;transition:all .2s}
        .btn-export-pdf{background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff}
        .btn-export-pdf:hover{background:linear-gradient(135deg,#b91c1c,#dc2626);transform:translateY(-1px);box-shadow:0 4px 14px rgba(220,38,38,.4)}
        .btn-export-pdf:disabled{opacity:.6;cursor:not-allowed;transform:none}
        .export-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
        .export-bar-title{font-size:1.1rem;font-weight:700;color:#e2e8f0}
        @keyframes spin{to{transform:rotate(360deg)}}
        .spin{animation:spin .8s linear infinite;display:inline-block}
    </style>
</head>
<body>
<div class="admin-body">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="assets/images/logo-foodsave.png" alt="FoodSave">
            <span><span class="brand-food">Food</span><span class="brand-save">Save</span></span>
        </div>
        <nav class="sidebar-nav">
            <a href="admin.php?action=dashboard">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Tableau de bord
            </a>
            <a href="admin.php?action=users">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>Utilisateurs
            </a>
            <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--color-border);opacity:.8">
                <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;color:var(--color-text-muted);padding:0 12px;margin-bottom:8px">Blog &amp; Contenu</div>
            </div>
            <a href="admin.php?action=adminArticles">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>Articles &amp; Blog
            </a>
            <a href="admin.php?action=adminAvis">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg>Avis &amp; Commentaires
            </a>
            <a href="admin.php?action=adminNewsletter">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>Newsletter
            </a>
            <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--color-border);opacity:.8">
                <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;color:var(--color-text-muted);padding:0 12px;margin-bottom:8px">Gestion Evenements</div>
            </div>
            <a href="admin.php?action=evenements">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Evenements
            </a>
            <a href="admin.php?action=participants">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Participants
            </a>
            <a href="admin.php?action=evenementStats">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>Statistiques
            </a>
            <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--color-border);opacity:.8">
                <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;color:var(--color-text-muted);padding:0 12px;margin-bottom:8px">Déchets</div>
            </div>
            <a href="admin.php?action=dechet_index">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>Déchets
            </a>
            <a href="admin.php?action=collecte_index">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>Collectes
            </a>
            <a href="admin.php?action=category_index">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>Catégories
            </a>
            <a href="admin.php?action=dechet_stats" class="active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>Statistiques
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="index.php?action=logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Déconnexion
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="admin-main">
        <div class="admin-topbar">
            <div style="display:flex;align-items:center;gap:14px">
                <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')" aria-label="Menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="admin-topbar-title">
                    <h1>Statistiques Déchets</h1>
                    <div class="admin-breadcrumb"><a href="admin.php?action=dashboard">Accueil</a> / <a href="admin.php?action=dechet_index">Déchets</a> / Statistiques</div>
                </div>
            </div>
            <div class="admin-topbar-actions">
                <div class="admin-user">
                    <div class="avatar avatar-orange">
                        <?php if (!empty($_SESSION['user_profile_photo'])): ?>
                            <img src="assets/uploads/profile_photos/<?= htmlspecialchars($_SESSION['user_profile_photo']) ?>" alt="Photo">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.875rem"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></div>
                        <div style="font-size:.75rem;opacity:.6"><?= htmlspecialchars($_SESSION['user_role'] ?? 'Administrateur') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-content" style="padding:28px">

            <!-- EXPORT BAR -->
            <div class="export-bar">
                <div class="export-bar-title">Rapport des Déchets</div>
                <button class="btn-export btn-export-pdf" id="btnExportPdf" onclick="exportPDF()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    Exporter PDF
                </button>
            </div>

            <!-- ZONE EXPORTABLE -->
            <div id="pdf-content">

            <!-- KPI -->
            <div class="stats-grid">
                <div class="stat-card"><div class="lbl">Total déchets</div><div class="val"><?= $total ?></div><div class="sub">enregistrements</div></div>
                <div class="stat-card"><div class="lbl">Quantité totale</div><div class="val"><?= $totalKg ?></div><div class="sub">kg / unités</div></div>
                <div class="stat-card"><div class="lbl">Moyenne par déchet</div><div class="val"><?= $avgKg ?></div><div class="sub">kg / unités</div></div>
                <div class="stat-card"><div class="lbl">Dernière entrée</div><div class="val" style="font-size:1.3rem"><?= htmlspecialchars($lastDate) ?></div><div class="sub">date</div></div>
            </div>

            <!-- CHARTS -->
            <div class="charts-row">
                <div class="chart-card"><h3>Évolution mensuelle (kg)</h3><canvas id="cMois"></canvas></div>
                <div class="chart-card"><h3>Répartition par raison</h3><canvas id="cRaison"></canvas></div>
            </div>
            <div class="charts-row">
                <div class="chart-card"><h3>Par catégorie</h3><canvas id="cCat"></canvas></div>
                <div class="chart-card"><h3>Top types d'aliments</h3><canvas id="cType"></canvas></div>
            </div>

            <!-- TABLE TYPE -->
            <div class="table-card">
                <h3>Détail par type d'aliment</h3>
                <table>
                    <thead><tr><th>Type d'aliment</th><th>Nb</th><th>Total (kg)</th><th>Proportion</th></tr></thead>
                    <tbody>
                    <?php if (!empty($byType)): foreach ($byType as $r):
                        $p = $maxKgByType > 0 ? round($r['total_kg'] / $maxKgByType * 100) : 0; ?>
                        <tr>
                            <td><?= htmlspecialchars($r['type_aliment']) ?></td>
                            <td><?= (int)$r['nb'] ?></td>
                            <td><?= round((float)$r['total_kg'], 2) ?></td>
                            <td style="min-width:130px">
                                <div style="font-size:.75rem;color:#81c784;margin-bottom:2px"><?= $p ?>%</div>
                                <div class="bar-w"><div class="bar-f" style="width:<?= $p ?>%"></div></div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="4" style="text-align:center;padding:24px;opacity:.5">Aucune donnée.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- TABLE RAISON -->
            <div class="table-card">
                <h3>Détail par raison</h3>
                <table>
                    <thead><tr><th>Raison</th><th>Nb</th><th>Total (kg)</th></tr></thead>
                    <tbody>
                    <?php if (!empty($byRaison)): foreach ($byRaison as $r): ?>
                        <tr>
                            <td><span class="badge-r"><?= htmlspecialchars($r['raison']) ?></span></td>
                            <td><?= (int)$r['nb'] ?></td>
                            <td><?= round((float)$r['total_kg'], 2) ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="3" style="text-align:center;padding:24px;opacity:.5">Aucune donnée.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            </div><!-- fin #pdf-content -->

            <a href="admin.php?action=dechet_index" style="color:#4ade80;text-decoration:none;font-size:.875rem">← Retour à la liste</a>
        </div>
    </div>
</div>

<script>
/* Toutes les données sont injectées ici — aucun mélange PHP dans les new Chart() */
const MOIS_LABELS   = <?= $jsMoisLabels ?>;
const MOIS_DATA     = <?= $jsMoisData ?>;
const RAISON_LABELS = <?= $jsRaisonLabels ?>;
const RAISON_DATA   = <?= $jsRaisonData ?>;
const RAISON_COLORS = <?= $jsRaisonColors ?>;
const CAT_LABELS    = <?= $jsCatLabels ?>;
const CAT_DATA      = <?= $jsCatData ?>;
const CAT_COLORS    = <?= $jsCatColors ?>;
const TYPE_LABELS   = <?= $jsTypeLabels ?>;
const TYPE_DATA     = <?= $jsTypeData ?>;

const G    = '#4ade80';
const MUTED = '#81c784';
const GRID  = 'rgba(74,222,128,0.06)';
const LEG   = { labels: { color: '#a5d6a7', font: { size: 12 }, padding: 14 } };
const AXES  = {
    x: { ticks: { color: MUTED }, grid: { color: GRID } },
    y: { ticks: { color: MUTED }, grid: { color: GRID } }
};

function initCharts() {
    // Évolution mensuelle
    new Chart(document.getElementById('cMois'), {
        type: 'line',
        data: { labels: MOIS_LABELS, datasets: [{
            label: 'kg / mois', data: MOIS_DATA,
            borderColor: G, backgroundColor: 'rgba(74,222,128,0.12)',
            pointBackgroundColor: G, tension: 0.4, fill: true
        }]},
        options: { responsive: true, plugins: { legend: LEG }, scales: AXES }
    });

    // Répartition par raison
    new Chart(document.getElementById('cRaison'), {
        type: 'doughnut',
        data: { labels: RAISON_LABELS, datasets: [{
            data: RAISON_DATA,
            backgroundColor: RAISON_COLORS,
            borderWidth: 2, borderColor: '#0f1f10'
        }]},
        options: { responsive: true, plugins: { legend: LEG } }
    });

    // Par catégorie
    new Chart(document.getElementById('cCat'), {
        type: 'bar',
        data: { labels: CAT_LABELS, datasets: [{
            label: 'Total (kg)', data: CAT_DATA,
            backgroundColor: CAT_COLORS, borderRadius: 6
        }]},
        options: { responsive: true, plugins: { legend: { display: false } }, scales: AXES }
    });

    // Top types (horizontal)
    new Chart(document.getElementById('cType'), {
        type: 'bar',
        data: { labels: TYPE_LABELS, datasets: [{
            label: 'Total (kg)', data: TYPE_DATA,
            backgroundColor: 'rgba(74,222,128,0.7)',
            borderColor: G, borderWidth: 1, borderRadius: 6
        }]},
        options: {
            indexAxis: 'y', responsive: true,
            plugins: { legend: { display: false } }, scales: AXES
        }
    });
}

/* Lance les graphiques une fois le DOM prêt */
document.addEventListener('DOMContentLoaded', initCharts);

/* ══════════════════════════════
   EXPORT PDF
══════════════════════════════ */
async function exportPDF() {
    const btn = document.getElementById('btnExportPdf');
    btn.disabled = true;
    btn.innerHTML = '<span class="spin">⏳</span> Génération...';

    try {
        const { jsPDF } = window.jspdf;
        const content   = document.getElementById('pdf-content');

        // Capture la zone entière en canvas
        const canvas = await html2canvas(content, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#0f1f10',
            logging: false,
            allowTaint: true
        });

        const imgData  = canvas.toDataURL('image/png');
        const pdf      = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const pageW    = pdf.internal.pageSize.getWidth();
        const pageH    = pdf.internal.pageSize.getHeight();
        const margin   = 10;
        const usableW  = pageW - margin * 2;
        const imgW     = canvas.width;
        const imgH     = canvas.height;
        const ratio    = usableW / imgW;
        const totalH   = imgH * ratio;

        // ── En-tête PDF ──
        pdf.setFillColor(15, 31, 16);
        pdf.rect(0, 0, pageW, 18, 'F');
        pdf.setTextColor(74, 222, 128);
        pdf.setFontSize(13);
        pdf.setFont('helvetica', 'bold');
        pdf.text('FoodSave — Statistiques Déchets', margin, 12);
        pdf.setFontSize(8);
        pdf.setTextColor(129, 199, 132);
        pdf.setFont('helvetica', 'normal');
        const now = new Date();
        pdf.text('Généré le ' + now.toLocaleDateString('fr-FR') + ' à ' + now.toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'}), pageW - margin, 12, { align: 'right' });

        // ── Contenu paginé ──
        const contentY  = 22; // départ après l'en-tête
        const sliceH    = (pageH - contentY - margin) / ratio; // hauteur en px par page

        let srcY = 0;
        let isFirstPage = true;

        while (srcY < imgH) {
            if (!isFirstPage) {
                pdf.addPage();
                // En-tête sur chaque page
                pdf.setFillColor(15, 31, 16);
                pdf.rect(0, 0, pageW, 18, 'F');
                pdf.setTextColor(74, 222, 128);
                pdf.setFontSize(13);
                pdf.setFont('helvetica', 'bold');
                pdf.text('FoodSave — Statistiques Déchets', margin, 12);
                pdf.setFontSize(8);
                pdf.setTextColor(129, 199, 132);
                pdf.setFont('helvetica', 'normal');
                pdf.text('Généré le ' + now.toLocaleDateString('fr-FR') + ' à ' + now.toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'}), pageW - margin, 12, { align: 'right' });
            }

            const thisSliceH = Math.min(sliceH, imgH - srcY);

            // Découpe le canvas en tranche
            const sliceCanvas = document.createElement('canvas');
            sliceCanvas.width  = imgW;
            sliceCanvas.height = thisSliceH;
            const ctx = sliceCanvas.getContext('2d');
            ctx.drawImage(canvas, 0, srcY, imgW, thisSliceH, 0, 0, imgW, thisSliceH);

            pdf.addImage(sliceCanvas.toDataURL('image/png'), 'PNG', margin, contentY, usableW, thisSliceH * ratio);

            srcY += thisSliceH;
            isFirstPage = false;
        }

        // ── Pied de page ──
        const totalPages = pdf.internal.getNumberOfPages();
        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            pdf.setFontSize(7);
            pdf.setTextColor(100, 140, 100);
            pdf.text('Page ' + i + ' / ' + totalPages, pageW / 2, pageH - 4, { align: 'center' });
        }

        const dateStr = now.toISOString().slice(0,10);
        pdf.save('foodsave_stats_dechets_' + dateStr + '.pdf');

    } catch (err) {
        console.error('Erreur PDF:', err);
        alert('Erreur lors de la génération du PDF. Vérifiez la console.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg> Exporter PDF';
    }
}
</script>
</body>
</html>
