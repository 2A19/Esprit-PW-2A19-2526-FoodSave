<?php
if(!isset($stats)) {
    header('Location: index.php?action=adminArticles');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Statistiques : Évolution des articles</title>
    
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
        
        .sidebar{
            width:280px;
            background:linear-gradient(180deg,#0f1f10 0%,#0a150a 100%);
            border-right:1px solid rgba(74,222,128,0.08);
            position:fixed;
            height:100vh;
            overflow-y:auto;
            z-index:10;
        }
        
        .sidebar-header{padding:1.5rem;border-bottom:1px solid rgba(255,255,255,0.05);margin-bottom:1rem}
        .logo-area{display:flex;align-items:center;gap:12px}
        .logo-area img{height:40px}
        .logo-area span{font-size:1.2rem;font-weight:700;background:linear-gradient(135deg,#4caf50,#ff6b35);-webkit-background-clip:text;background-clip:text;color:transparent}
        
        .sidebar-menu{list-style:none;padding:0 1rem}
        .sidebar-menu li{margin-bottom:0.5rem}
        .sidebar-menu a{
            display:flex;align-items:center;gap:12px;padding:12px 16px;
            color:rgba(255,255,255,0.5);text-decoration:none;border-radius:12px;
            transition:all 0.3s ease;font-weight:500
        }
        .sidebar-menu a:hover,.sidebar-menu a.active{
            background:rgba(74,222,128,0.12);color:#4ade80;border:1px solid rgba(74,222,128,0.2)
        }
        .sidebar-menu a i{width:24px;font-size:1.1rem}
        
        .main-content{
            flex:1;
            margin-left:280px;
            padding:2rem;
            min-height:100vh;
            position:relative;
            z-index:5;
        }
        
        .navbar{
            background:rgba(15,31,16,0.85);
            backdrop-filter:blur(20px);
            border-radius:20px;
            padding:0.8rem 1.5rem;
            margin-bottom:2rem;
            border:1px solid rgba(74,222,128,0.1);
        }
        .nav-container{display:flex;justify-content:space-between;align-items:center}
        .nav-logo{display:flex;align-items:center;gap:10px}
        .nav-logo img{height:40px}
        .nav-logo span{font-weight:700;font-size:1.2rem;color:#fff}
        .nav-menu{display:flex;gap:0.5rem}
        .nav-link{
            text-decoration:none;color:rgba(255,255,255,0.5);padding:8px 18px;
            border-radius:50px;transition:all 0.3s ease;font-size:13px;font-weight:500
        }
        .nav-link:hover,.nav-link.active{background:#16a34a;color:#fff;box-shadow:0 0 16px rgba(22,163,74,0.4)}
        
        .login-btn{padding:8px 18px;border-radius:50px;border:none;cursor:pointer;font-weight:500;font-family:inherit}
        .login-outline{background:transparent;border:1px solid rgba(74,222,128,0.35);color:#4ade80}
        .login-outline:hover{background:rgba(74,222,128,0.08)}
        .login-primary{background:#16a34a;color:#fff;box-shadow:0 0 20px rgba(22,163,74,0.35)}
        .login-primary:hover{background:#15803d;transform:translateY(-1px)}
        
        .stats-header{margin-bottom:2rem}
        .stats-header h1{font-size:26px;font-weight:700;color:#fff;letter-spacing:-0.8px}
        .stats-header h1 i{color:#4ade80}
        .stats-header p{color:rgba(255,255,255,0.5);margin-top:0.5rem}
        
        .kpi-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5rem;margin-bottom:2rem}
        .kpi-card{
            background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);
            border-radius:16px;padding:1.5rem;text-align:center;position:relative;overflow:hidden;
            transition:all 0.25s
        }
        .kpi-card::before{content:"";position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#16a34a,#fbbf24,#ef4444);opacity:0.6}
        .kpi-card:hover{background:rgba(74,222,128,0.05);border-color:rgba(74,222,128,0.22);transform:translateY(-3px)}
        .kpi-number{font-size:2.5rem;font-weight:800;color:#4ade80}
        .kpi-label{font-size:0.8rem;color:rgba(255,255,255,0.5);margin-top:0.5rem;text-transform:uppercase;letter-spacing:1px}
        .kpi-tendance{margin-top:0.5rem;font-size:0.8rem}
        .tendance-hausse{color:#4ade80}.tendance-baisse{color:#f87171}.tendance-stable{color:#fbbf24}
        
        .chart-card{background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:20px;padding:1.5rem;margin-bottom:1.5rem}
        .chart-card h3{margin-bottom:1rem;color:#fff;display:flex;align-items:center;gap:10px;font-size:1.1rem}
        .chart-card h3 i{color:#4ade80}
        .chart-container{position:relative;height:400px}
        
        .data-table{background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:20px;padding:1.5rem}
        .data-table h3{margin-bottom:1rem;color:#fff;display:flex;align-items:center;gap:10px}
        .data-table h3 i{color:#4ade80}
        
        table{width:100%;border-collapse:collapse}
        th,td{padding:12px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.05)}
        th{background:rgba(74,222,128,0.05);font-weight:600;color:rgba(255,255,255,0.7);font-size:0.85rem}
        
        .bar-cell{width:150px}
        .bar{
            background:linear-gradient(90deg,#4ade80,#16a34a);
            height:30px;border-radius:15px;color:#fff;
            display:flex;align-items:center;justify-content:flex-end;padding-right:10px;
            font-size:0.8rem;font-weight:500;min-width:30px;transition:width 0.5s ease
        }
        
        @media (max-width:768px){
            .sidebar{width:80px}
            .sidebar-header .logo-area span,.sidebar-menu a span{display:none}
            .main-content{margin-left:80px}
            .kpi-cards{grid-template-columns:1fr}
            .chart-container{height:300px}
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
            <li><a href="admin.php?action=adminArticles"><i class="fas fa-newspaper"></i> <span>Articles</span></a></li>
            <li><a href="admin.php?action=adminAvis"><i class="fas fa-star"></i> <span>Avis</span></a></li>
            <li><a href="admin.php?action=statsEvolution" class="active"><i class="fas fa-chart-line"></i> <span>Statistiques</span></a></li>
            <li><a href="#"><i class="fas fa-users"></i> <span>Utilisateurs</span></a></li>
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
                    <a href="admin.php?action=adminArticles" class="nav-link">Articles</a>
                    <a href="admin.php?action=adminAvis" class="nav-link">Avis</a>
                    <a href="admin.php?action=statsEvolution" class="nav-link active">Statistiques</a>
                </div>
                <div class="user-actions">
                    <button class="login-btn login-outline"><i class="fas fa-user"></i> Profil</button>
                    <button class="login-btn login-primary"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
                </div>
            </div>
        </nav>

        <div class="stats-header">
            <h1><i class="fas fa-chart-line"></i> Évolution des publications</h1>
            <p>Analyse de l'activité éditoriale sur les 12 derniers mois</p>
        </div>

        <div class="kpi-cards">
            <div class="kpi-card">
                <div class="kpi-number"><?php echo $stats['total']; ?></div>
                <div class="kpi-label">📝 Articles publiés</div>
                <div class="kpi-label">(12 derniers mois)</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-number"><?php echo $stats['moyenne']; ?></div>
                <div class="kpi-label">📊 Moyenne par mois</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-number"><?php echo $stats['meilleur_mois']; ?></div>
                <div class="kpi-label">🏆 Meilleur mois</div>
                <div class="kpi-label"><?php echo $stats['meilleur_mois_label']; ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-number">
                    <?php 
                    $evolution = $stats['evolution'];
                    echo ($evolution > 0 ? '+' : '') . $evolution;
                    ?>
                </div>
                <div class="kpi-label">📈 Évolution</div>
                <div class="kpi-tendance <?php echo 'tendance-' . $stats['tendance']; ?>">
                    <?php 
                    if($stats['tendance'] == 'hausse') echo '↑ En hausse sur la période';
                    elseif($stats['tendance'] == 'baisse') echo '↓ En baisse sur la période';
                    else echo '→ Stable sur la période';
                    ?>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <h3><i class="fas fa-chart-line"></i> Évolution mensuelle des publications</h3>
            <div class="chart-container">
                <canvas id="evolutionChart"></canvas>
            </div>
        </div>

        <div class="data-table">
            <h3><i class="fas fa-table"></i> Données détaillées</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Mois</th><th>Articles publiés</th><th>Visualisation</th><th>% du total</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $maxData = max($stats['data']);
                        for($i = 0; $i < count($stats['labels']); $i++):
                            $percentage = ($maxData > 0) ? round(($stats['data'][$i] / $maxData) * 100) : 0;
                            $barWidth = max(30, $percentage);
                        ?>
                        <tr>
                            <td><strong><?php echo $stats['labels'][$i]; ?></strong></td>
                            <td><?php echo $stats['data'][$i]; ?></td>
                            <td class="bar-cell">
                                <div class="bar" style="width: <?php echo $barWidth; ?>%;">
                                    <?php if($stats['data'][$i] > 0): ?>
                                        <?php echo $stats['data'][$i]; ?>
                                    <?php endif; ?>
                                </div>
                              </td>
                            <td><?php echo $percentage; ?>%</td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const moisLabels = <?php echo json_encode($stats['labels']); ?>;
    const moisData = <?php echo json_encode($stats['data']); ?>;
    
    const ctx = document.getElementById('evolutionChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: moisLabels,
            datasets: [{
                label: 'Articles publiés',
                data: moisData,
                borderColor: '#4ade80',
                backgroundColor: 'rgba(74,222,128,0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#4ade80',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: { callbacks: { label: function(context) { return '📝 ' + context.raw + ' article(s)'; } } },
                legend: { position: 'top', labels: { font: { family: 'DM Sans', size: 12 }, color: '#fff' } }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Nombre d\'articles', color: '#fff' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                x: { title: { display: true, text: 'Mois', color: '#fff' }, grid: { display: false } }
            }
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bars = document.querySelectorAll('.bar');
        bars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => { bar.style.width = width; }, 100);
        });
    });
</script>

</body>
</html>