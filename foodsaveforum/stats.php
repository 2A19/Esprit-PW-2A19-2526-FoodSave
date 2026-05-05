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
    <title>Statistiques Likes/Dislikes - FoodSave Forum</title>
    <link rel="stylesheet" href="/foodsaveforum/public/assets/css/style.css?v=1.2">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .chart-box {
            background: white;
            border: 1px solid #dce8df;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 6px 18px rgba(34, 48, 38, 0.06);
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f5f34;
            margin-bottom: 12px;
            text-align: center;
        }

        .summary-row {
            display: flex;
            gap: 10px;
            margin: 0 0 16px;
            flex-wrap: wrap;
        }

        .summary-pill {
            border: 1px solid #dce8df;
            background: #f8fbf9;
            border-radius: 999px;
            padding: 8px 12px;
            font-weight: 700;
            color: #4d5b50;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 16px;
            padding: 10px 16px;
            background: #6f7d71;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
        }

        .back-link:hover {
            background: #5c695f;
        }

        @media (max-width: 900px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="front-office">
    <header class="header">
        <div class="container header-inner">
            <div class="logo logo-brand">
                <a href="index.php?action=posts">
                    <img src="/foodsaveforum/public/assets/images/logo-foodsave.svg?v=20260421_v2" alt="FoodSave Logo" class="logo-image">
                </a>
            </div>
            <nav class="navbar">
                <ul>
                    <li><a href="index.php?action=posts">Accueil</a></li>
                    <li><a href="stats.php">Statistiques</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <a href="index.php?action=posts" class="btn-small btn-secondary">Retour au forum</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container page-card">
            <a href="index.php?action=posts" class="back-link">← Retour au Forum</a>
            <h1 style="color:#1f5f34; margin-bottom: 10px;">Statistiques Likes / Dislikes</h1>
            <div class="summary-row">
                <span class="summary-pill">👍 Likes totaux: <?php echo $totalLikes; ?></span>
                <span class="summary-pill">👎 Dislikes totaux: <?php echo $totalDislikes; ?></span>
            </div>

            <div class="charts-grid">
                <div class="chart-box">
                    <div class="chart-title">Courbe des réactions par post</div>
                    <canvas id="lineChart"></canvas>
                </div>

                <div class="chart-box">
                    <div class="chart-title">Disque de sondage Likes vs Dislikes</div>
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 FoodSave - Plateforme Anti-Gaspillage. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        const postTitles = <?php echo json_encode($postTitles); ?>;
        const likesData = <?php echo json_encode($likesData); ?>;
        const dislikesData = <?php echo json_encode($dislikesData); ?>;
        const totalLikes = <?php echo (int) $totalLikes; ?>;
        const totalDislikes = <?php echo (int) $totalDislikes; ?>;

        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: postTitles,
                datasets: [
                    {
                        label: 'Likes',
                        data: likesData,
                        borderColor: '#3ea95b',
                        backgroundColor: 'rgba(62, 169, 91, 0.15)',
                        borderWidth: 3,
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Dislikes',
                        data: dislikesData,
                        borderColor: '#d73535',
                        backgroundColor: 'rgba(215, 53, 53, 0.12)',
                        borderWidth: 3,
                        tension: 0.35,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: '#4d5b50',
                            font: { weight: '700' }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#4d5b50' }
                    },
                    x: {
                        ticks: { color: '#4d5b50' }
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
                    backgroundColor: ['#3ea95b', '#d73535'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#4d5b50',
                            font: { weight: '700' }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
