<?php
if(!isset($stats) || !isset($pstats) || !isset($events) || !isset($slabels) || !isset($sbadge)) {
    header('Location: admin.php?action=evenements');
    exit;
}
$categories = [];
foreach($events as $e){
    $cat = $e['categorie'] ?? 'Autre';
    if(!isset($categories[$cat])) $categories[$cat] = 0;
    $categories[$cat]++;
}
$catLabels = json_encode(array_keys($categories));
$catData = json_encode(array_values($categories));
$pLabels = json_encode(array_keys($pstats ?? []));
$pData = json_encode(array_values($pstats ?? []));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Admin : Statistiques</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:"DM Sans",sans-serif;background:#0d1f14;color:#e8f5e9;overflow-x:hidden}
        .bg-mesh{position:fixed;inset:0;pointer-events:none;z-index:0}
        .glow-1{position:absolute;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(34,197,94,0.18) 0%,transparent 70%);top:-80px;right:-60px;animation:driftA 8s ease-in-out infinite alternate}
        .glow-2{position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(16,185,129,0.12) 0%,transparent 70%);bottom:0;left:-80px;animation:driftB 10s ease-in-out infinite alternate}
        .glow-3{position:absolute;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(234,179,8,0.09) 0%,transparent 70%);top:40%;left:38%;animation:driftA 12s ease-in-out infinite alternate}
        @keyframes driftA{from{transform:translate(0,0)}to{transform:translate(30px,20px)}}
        @keyframes driftB{from{transform:translate(0,0)}to{transform:translate(-20px,-30px)}}
        .grid-lines{position:absolute;inset:0;background-image:linear-gradient(rgba(34,197,94,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(34,197,94,0.04) 1px,transparent 1px);background-size:48px 48px}
        .admin-container{display:flex;min-height:100vh;position:relative;z-index:1}
        .sidebar{width:280px;background:linear-gradient(180deg,#0f1f10 0%,#0a150a 100%);border-right:1px solid rgba(74,222,128,0.08);position:fixed;height:100vh;overflow-y:auto;z-index:10}
        .sidebar-header{padding:1.5rem;border-bottom:1px solid rgba(255,255,255,0.05);margin-bottom:1rem}
        .logo-area{display:flex;align-items:center;gap:12px}
        .logo-area img{height:40px}
        .logo-area span{font-size:1.2rem;font-weight:700;background:linear-gradient(135deg,#4caf50,#ff6b35);-webkit-background-clip:text;background-clip:text;color:transparent}
        .sidebar-menu{list-style:none;padding:0 1rem}
        .sidebar-menu li{margin-bottom:0.5rem}
        .sidebar-menu a{display:flex;align-items:center;gap:12px;padding:12px 16px;color:rgba(255,255,255,0.5);text-decoration:none;border-radius:12px;transition:all 0.3s ease;font-weight:500}
        .sidebar-menu a:hover,.sidebar-menu a.active{background:rgba(74,222,128,0.12);color:#4ade80;border:1px solid rgba(74,222,128,0.2)}
        .sidebar-menu a i{width:24px;font-size:1.1rem}
        .main-content{flex:1;margin-left:280px;padding:2rem;min-height:100vh;position:relative;z-index:5}
        .navbar{background:rgba(15,31,16,0.85);backdrop-filter:blur(20px);border-radius:20px;padding:0.8rem 1.5rem;margin-bottom:2rem;border:1px solid rgba(74,222,128,0.1)}
        .nav-container{display:flex;justify-content:space-between;align-items:center}
        .nav-logo{display:flex;align-items:center;gap:10px}
        .nav-logo img{height:40px}
        .nav-logo span{font-weight:700;font-size:1.2rem;color:#fff}
        .nav-menu{display:flex;gap:0.5rem}
        .nav-link{text-decoration:none;color:rgba(255,255,255,0.5);padding:8px 18px;border-radius:50px;transition:all 0.3s ease;font-size:13px;font-weight:500}
        .nav-link:hover,.nav-link.active{background:#16a34a;color:#fff;box-shadow:0 0 16px rgba(22,163,74,0.4)}
        .login-btn{padding:8px 18px;border-radius:50px;border:none;cursor:pointer;font-weight:500;font-family:inherit}
        .login-outline{background:transparent;border:1px solid rgba(74,222,128,0.35);color:#4ade80}
        .login-outline:hover{background:rgba(74,222,128,0.08)}
        .login-primary{background:#16a34a;color:#fff;box-shadow:0 0 20px rgba(22,163,74,0.35)}
        .login-primary:hover{background:#15803d;transform:translateY(-1px)}
        .content-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem}
        .content-header h1{font-size:26px;font-weight:700;color:#fff;letter-spacing:-0.8px}
        .content-header h1 i{color:#4ade80}
        .btn-pdf{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:#ef4444;border:none;border-radius:50px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;box-shadow:0 0 20px rgba(239,68,68,0.35);transition:all 0.2s;text-decoration:none}
        .btn-pdf:hover{background:#dc2626;transform:translateY(-2px);box-shadow:0 0 30px rgba(239,68,68,0.5)}
        .stats-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem}
        .stat-card{background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:16px;padding:20px;display:flex;align-items:center;gap:16px;transition:all 0.25s;position:relative;overflow:hidden}
        .stat-card::before{content:"";position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#16a34a,#fbbf24,#ef4444);opacity:0.6}
        .stat-card:hover{background:rgba(74,222,128,0.05);border-color:rgba(74,222,128,0.22);transform:translateY(-3px)}
        .stat-icon-wrap{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
        .si-green{background:rgba(74,222,128,0.12)}
        .si-blue{background:rgba(59,130,246,0.12)}
        .si-orange{background:rgba(251,191,36,0.12)}
        .si-red{background:rgba(239,68,68,0.12)}
        .si-purple{background:rgba(168,85,247,0.12)}
        .stat-val{font-size:30px;font-weight:700;color:#fff;line-height:1}
        .stat-lbl{font-size:11px;font-weight:600;color:rgba(255,255,255,0.35);letter-spacing:0.08em;text-transform:uppercase;margin-top:3px}
        .charts-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem}
        .chart-card{background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:20px;padding:1.5rem}
        .chart-card h3{font-size:15px;font-weight:700;color:#fff;margin-bottom:1rem;display:flex;align-items:center;gap:8px}
        .chart-card h3 i{color:#4ade80}
        .chart-card canvas{max-height:280px}
        .table-wrap{background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:20px;overflow:hidden}
        .table-head{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid rgba(255,255,255,0.05)}
        .table-head-title{font-size:15px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px}
        table{width:100%;border-collapse:collapse}
        thead tr{background:rgba(74,222,128,0.05)}
        th{padding:14px 20px;text-align:left;font-size:11px;font-weight:700;color:rgba(255,255,255,0.35);letter-spacing:0.1em;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,0.05)}
        td{padding:14px 20px;font-size:13px;color:rgba(255,255,255,0.75);border-bottom:1px solid rgba(255,255,255,0.04)}
        .badge{padding:4px 12px;border-radius:50px;font-size:11px;font-weight:700}
        .main-content::-webkit-scrollbar{width:6px}
        .main-content::-webkit-scrollbar-thumb{background:rgba(74,222,128,0.2);border-radius:3px}
        @media (max-width:768px){
            .sidebar{width:80px}
            .sidebar-header .logo-area span,.sidebar-menu a span{display:none}
            .main-content{margin-left:80px}
            .stats-cards{grid-template-columns:1fr}
            .charts-grid{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div><div class="glow-3"></div>
</div>
<div class="admin-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-area">
                <img src="./assets/images/logo-foodsave.png" alt="Logo">
                <span>FoodSave Admin</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin.php?action=dashboard"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="admin.php?action=evenements"><i class="fas fa-calendar-alt"></i> <span>Evenements</span></a></li>
            <li><a href="admin.php?action=participants"><i class="fas fa-users"></i> <span>Participants</span></a></li>
            <li><a href="admin.php?action=evenementStats" class="active"><i class="fas fa-chart-bar"></i> <span>Statistiques</span></a></li>
        </ul>
    </div>
    <div class="main-content">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <img src="./assets/images/logo-foodsave.png" alt="Logo">
                    <span>FoodSave Admin</span>
                </div>
                <div class="nav-menu">
                    <a href="admin.php?action=evenements" class="nav-link">Evenements</a>
                    <a href="admin.php?action=participants" class="nav-link">Participants</a>
                </div>
                <div class="user-actions">
                    <button class="login-btn login-outline"><i class="fas fa-user"></i> Profil</button>
                    <button class="login-btn login-primary"><i class="fas fa-sign-out-alt"></i> Deconnexion</button>
                </div>
            </div>
        </nav>
        <div class="content-header">
            <h1><i class="fas fa-chart-bar"></i> Statistiques</h1>
            <a href="admin.php?action=evenementExportPdf&type=stats" class="btn-pdf"><i class="fas fa-file-pdf"></i> Exporter PDF</a>
        </div>
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon-wrap si-green"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <div class="stat-val"><?php echo $stats['total'] ?? 0; ?></div>
                    <div class="stat-lbl">Total evenements</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-blue"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-val"><?php echo $stats['upcoming'] ?? 0; ?></div>
                    <div class="stat-lbl">A venir</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-orange"><i class="fas fa-play-circle"></i></div>
                <div>
                    <div class="stat-val"><?php echo $stats['ongoing'] ?? 0; ?></div>
                    <div class="stat-lbl">En cours</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-red"><i class="fas fa-history"></i></div>
                <div>
                    <div class="stat-val"><?php echo $stats['past'] ?? 0; ?></div>
                    <div class="stat-lbl">Passes</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-green"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-val"><?php echo $pstats['total'] ?? 0; ?></div>
                    <div class="stat-lbl">Total participants</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-blue"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-val"><?php echo $pstats['confirmed'] ?? 0; ?></div>
                    <div class="stat-lbl">Confirmes</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-orange"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-val"><?php echo $pstats['pending'] ?? 0; ?></div>
                    <div class="stat-lbl">En attente</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-red"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-val"><?php echo $pstats['cancelled'] ?? 0; ?></div>
                    <div class="stat-lbl">Annules</div>
                </div>
            </div>
        </div>
        <div class="charts-grid">
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Repartition des evenements</h3>
                <canvas id="eventChart"></canvas>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Repartition des participants</h3>
                <canvas id="participantChart"></canvas>
            </div>
        </div>
        <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:20px;padding:1.5rem;margin-bottom:1.5rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
                <h3 style="font-size:15px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px"><i class="fas fa-robot" style="color:#4ade80"></i> Recommandations IA</h3>
                <button id="aiRecommendBtn" style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:linear-gradient(135deg,#16a34a,#0d9488);border:none;border-radius:50px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;box-shadow:0 0 20px rgba(22,163,74,0.35);transition:all 0.2s">🤖 Analyser</button>
            </div>
            <div id="aiRecommendations"></div>
        </div>
        <div class="table-wrap">
            <div class="table-head">
                <div class="table-head-title"><i class="fas fa-list-ul"></i> Evenements par categorie</div>
            </div>
            <div style="overflow-x:auto">
                <table>
                    <thead>
                        <tr>
                            <th>Categorie</th>
                            <th>Nombre d'evenements</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $cat => $count): ?>
                        <tr>
                            <td><span class="badge" style="background:rgba(74,222,128,0.12);color:#4ade80"><?php echo htmlspecialchars($cat); ?></span></td>
                            <td><?php echo $count; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($categories)): ?>
                        <tr><td colspan="2" style="text-align:center;padding:32px;color:rgba(255,255,255,0.4)">Aucune donnee disponible.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
<?php if(!empty($catLabels)): ?>
new Chart(document.getElementById('eventChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo $catLabels; ?>,
        datasets: [{
            data: <?php echo $catData; ?>,
            backgroundColor: ['#4ade80','#60a5fa','#fbbf24','#f87171','#c084fc','#fb923c'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: { color: 'rgba(255,255,255,0.7)', font: { family: 'DM Sans' } }
            }
        }
    }
});
<?php endif; ?>
<?php if(!empty($pLabels)): ?>
new Chart(document.getElementById('participantChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo $pLabels; ?>,
        datasets: [{
            data: <?php echo $pData; ?>,
            backgroundColor: ['#4ade80','#fbbf24','#f87171'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: { color: 'rgba(255,255,255,0.7)', font: { family: 'DM Sans' } }
            }
        }
    }
});
<?php endif; ?>
</script>
<div id="toasts" style="position:fixed;top:20px;right:20px;z-index:99999;min-width:260px"></div>
<script src="./assets/js/features.js"></script>
<script>
// AI Recommendations
(function() {
    var btn = document.getElementById('aiRecommendBtn');
    var result = document.getElementById('aiRecommendations');
    if (!btn || !result) return;
    btn.addEventListener('click', function() {
        var context = {
            total_participants: <?php echo $pstats['total'] ?? 0; ?>,
            confirmed: <?php echo $pstats['confirmed'] ?? 0; ?>,
            pending: <?php echo $pstats['pending'] ?? 0; ?>,
            cancelled: <?php echo $pstats['cancelled'] ?? 0; ?>,
            total_events: <?php echo $stats['total'] ?? 0; ?>,
            upcoming_events: <?php echo $stats['upcoming'] ?? 0; ?>
        };
        getRecommendations(context, result);
    });
})();
</script>
</body>
</html>
