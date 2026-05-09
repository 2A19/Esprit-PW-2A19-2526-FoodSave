<?php
session_start();

require_once __DIR__ . '/Model/Database.php';
require_once __DIR__ . '/config.php';

$db = config::getConnexion();

$sql = "SELECT 
            p.id_post,
            p.titre,
            COUNT(CASE WHEN pl.type_reaction = 'like' THEN 1 END) as likes,
            COUNT(CASE WHEN pl.type_reaction = 'dislike' THEN 1 END) as dislikes
        FROM posts p
        LEFT JOIN post_likes pl ON p.id_post = pl.id_post
        GROUP BY p.id_post, p.titre
        ORDER BY p.date_creation ASC";

$stmt = $db->prepare($sql);
$stmt->execute();
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$postTitles = [];
$likesData = [];
$dislikesData = [];

foreach ($stats as $stat) {
    $postTitles[] = mb_strimwidth($stat['titre'], 0, 22, '...');
    $likesData[] = (int) $stat['likes'];
    $dislikesData[] = (int) $stat['dislikes'];
}

$totalLikes = array_sum($likesData);
$totalDislikes = array_sum($dislikesData);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques – FoodSave Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/foodsaveforum/public/assets/css/style.css?v=3.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ── Stat cards ── */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px;
            margin: 0 0 20px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            transition: all 0.2s;
        }
        .stat-card:hover { background: var(--bg-card-hover); border-color: var(--border-hover); }
        .stat-card .number { display: block; color: var(--green-bright); font-size: 1.55rem; font-weight: 800; letter-spacing: -0.5px; }
        .stat-card h4 { color: var(--text-muted); font-size: 0.82rem; margin-top: 4px; text-transform: uppercase; letter-spacing: 1px; font-weight: 500; }

        /* ── Charts ── */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .chart-box {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
        }
        .chart-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: rgba(255,255,255,0.7);
            margin-bottom: 14px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ── Content header ── */
        .content-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }
        .content-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .content-header h1 i { color: var(--amber); }

        @media (max-width: 900px) {
            .charts-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header class="admin-header">
    <div class="container admin-header-inner">
        <div class="logo logo-brand">
            <a href="index.php?action=posts">
                <img src="/foodsaveforum/public/assets/images/logo-foodsave.svg?v=20260421_v2" alt="FoodSave Logo" class="logo-image">
            </a>
        </div>

        <div class="header-actions">
            <a href="#logout" class="btn btn-small btn-secondary"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="container">
        <div class="admin-shell">

            <!-- Sidebar -->
            <aside class="admin-sidebar">
                <ul>
                    <li><a href="admin.php?action=dashboard"><i class="fas fa-th-large"></i> Tableau de bord</a></li>
                    <li><a href="admin.php?action=posts"><i class="fas fa-newspaper"></i> Sujets</a></li>
                    <li><a href="admin.php?action=commentaires"><i class="fas fa-comments"></i> Messages</a></li>
                    <li><a href="stats.php" class="active"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                    <li><a href="index.php?action=posts"><i class="fas fa-reply"></i> Retour au forum</a></li>
                    <li><a href="#logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                </ul>
            </aside>

            <!-- Content -->
            <div class="admin-content">

                <div class="content-header">
                    <h1><i class="fas fa-chart-line"></i> Statistiques Likes / Dislikes</h1>
                </div>

                <div class="stats-cards">
                    <div class="stat-card">
                        <span class="number"><?php echo $totalLikes; ?></span>
                        <h4>👍 Likes totaux</h4>
                    </div>
                    <div class="stat-card">
                        <span class="number" style="color: var(--red);"><?php echo $totalDislikes; ?></span>
                        <h4>👎 Dislikes totaux</h4>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-box">
                        <div class="chart-title">Courbe des réactions par post</div>
                        <canvas id="lineChart"></canvas>
                    </div>
                    <div class="chart-box">
                        <div class="chart-title">Likes vs Dislikes</div>
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<footer class="footer">
    <div class="container">
        <p>© 2026 FoodSave – Plateforme Anti-Gaspillage. Tous droits réservés.</p>
    </div>
</footer>

<script src="/foodsaveforum/public/assets/js/script.js?v=2.0"></script>
<script>
    const postTitles   = <?php echo json_encode($postTitles); ?>;
    const likesData    = <?php echo json_encode($likesData); ?>;
    const dislikesData = <?php echo json_encode($dislikesData); ?>;
    const totalLikes    = <?php echo (int) $totalLikes; ?>;
    const totalDislikes = <?php echo (int) $totalDislikes; ?>;

    const gridColor  = 'rgba(74,222,128,0.07)';
    const tickColor  = 'rgba(255,255,255,0.4)';
    const legendColor = 'rgba(255,255,255,0.6)';

    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: postTitles,
            datasets: [
                {
                    label: 'Likes',
                    data: likesData,
                    borderColor: '#4ade80',
                    backgroundColor: 'rgba(74,222,128,0.1)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#4ade80'
                },
                {
                    label: 'Dislikes',
                    data: dislikesData,
                    borderColor: '#f87171',
                    backgroundColor: 'rgba(248,113,113,0.08)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#f87171'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: legendColor, font: { weight: '600' } } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: tickColor },
                    grid: { color: gridColor }
                },
                x: {
                    ticks: { color: tickColor, maxRotation: 45 },
                    grid: { color: gridColor }
                }
            }
        }
    });

    new Chart(document.getElementById('pieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Likes', 'Dislikes'],
            datasets: [{
                data: [totalLikes, totalDislikes],
                backgroundColor: ['rgba(74,222,128,0.7)', 'rgba(248,113,113,0.7)'],
                borderColor: 'rgba(13,31,20,0.8)',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: legendColor, font: { weight: '600' }, padding: 16 }
                }
            }
        }
    });
</script>
</body>
</html>
