<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'FoodSave - BackOffice'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/wf/public/assets/css/style.css?v=3.0">
    <style>
        /* ── Back-office overrides: map old class names → dark design system ── */

        /* Stats cards (back views use .stat-card / .stats-cards) */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px;
            margin: 14px 0 16px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            text-align: center;
            transition: all 0.2s;
        }
        .stat-card:hover { background: var(--bg-card-hover); border-color: var(--border-hover); }
        .stat-card .number { display: block; color: var(--green-bright); font-size: 1.55rem; font-weight: 800; letter-spacing: -0.5px; }
        .stat-card h4 { color: var(--text-muted); font-size: 0.82rem; margin-top: 4px; text-transform: uppercase; letter-spacing: 1px; font-weight: 500; }

        /* Data table wrapper (back views use .data-table) */
        .data-table {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .data-table h3 {
            margin-bottom: 12px;
            color: rgba(255,255,255,0.7);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .data-table h3 i { color: var(--green-bright); }

        /* Content header */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 11px 13px; text-align: left; border-bottom: 1px solid rgba(74,222,128,0.06); }
        th {
            background: rgba(74,222,128,0.05);
            color: rgba(255,255,255,0.65);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        td { color: var(--text-muted); font-size: 0.86rem; vertical-align: top; }
        tbody tr:hover td { background: rgba(74,222,128,0.025); }
        .table-responsive { overflow-x: auto; }

        /* Badges */
        .badge-status {
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            background: rgba(74,222,128,0.12);
            color: var(--green-bright);
            border: 1px solid rgba(74,222,128,0.25);
        }
        .badge-status.danger {
            background: rgba(248,113,113,0.12);
            color: var(--red);
            border-color: rgba(248,113,113,0.25);
        }
        .badge-status.warning {
            background: rgba(251,191,36,0.12);
            color: var(--amber);
            border-color: rgba(251,191,36,0.25);
        }

        /* Action buttons */
        .btn-edit, .btn-validate, .btn-delete, .btn-view {
            padding: 5px 13px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.74rem;
            font-weight: 600;
            transition: all 0.2s;
            margin: 2px;
            font-family: inherit;
        }
        .btn-view    { background: rgba(96,165,250,0.12);  color: var(--blue);  border: 1px solid rgba(96,165,250,0.25); }
        .btn-view:hover    { background: rgba(96,165,250,0.22); }
        .btn-validate{ background: rgba(74,222,128,0.12);  color: var(--green-bright); border: 1px solid rgba(74,222,128,0.25); }
        .btn-validate:hover{ background: rgba(74,222,128,0.22); }
        .btn-delete  { background: rgba(248,113,113,0.12); color: var(--red);  border: 1px solid rgba(248,113,113,0.25); }
        .btn-delete:hover  { background: rgba(248,113,113,0.22); }
        .btn-edit    { background: rgba(251,191,36,0.12);  color: var(--amber); border: 1px solid rgba(251,191,36,0.25); }
        .btn-edit:hover    { background: rgba(251,191,36,0.22); }

        /* Alerts */
        .alert, .success {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }
        .alert-success, .success {
            background: rgba(74,222,128,0.08);
            border: 1px solid rgba(74,222,128,0.2);
            color: var(--green-bright);
        }
        .alert-danger {
            background: rgba(248,113,113,0.08);
            border: 1px solid rgba(248,113,113,0.2);
            color: var(--red);
        }

        /* Empty state */
        .empty-state { text-align: center; padding: 3rem; color: var(--text-muted); }

        /* Filters */
        .filters { margin-bottom: 16px; background: rgba(255,255,255,0.025); border: 1px solid var(--border); border-radius: 12px; padding: 13px; }
        .filter-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .filter-form select {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-base);
            padding: 7px 12px;
            font-family: inherit;
            font-size: 0.88rem;
        }
        .filter-form select option { background: #0d1f14; }
    </style>
</head>
<body>

<!-- Ambient background (same as front) -->
<header class="admin-header">
    <div class="container admin-header-inner">
        <div class="logo logo-brand">
            <a href="index.php?action=posts">
                <img src="/wf/public/assets/images/logo-foodsave.svg?v=20260421_v2" alt="FoodSave Logo" class="logo-image">
            </a>
        </div>



        <div class="header-actions">
            <a href="/wf/foodsave/index.php?action=logout" class="btn btn-small btn-secondary"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
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
                    <li><a href="/wf/forum/stats.php"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                    <li><a href="index.php?action=posts"><i class="fas fa-reply"></i> Retour au forum</a></li>
                    <li><a href="/wf/foodsave/index.php?action=logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                </ul>
            </aside>

            <!-- Content -->
            <div class="admin-content">
                <?php
                if (isset($errors) && !empty($errors)) {
                    echo '<div class="alert alert-danger">';
                    foreach ($errors as $error) {
                        echo '<p>' . htmlspecialchars($error) . '</p>';
                    }
                    echo '</div>';
                }
                if (isset($success) && $success) {
                    echo '<div class="success"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($message) . '</div>';
                }
                ?>

                <?php include $content; ?>
            </div>
        </div>
    </div>
</main>

<footer class="footer">
    <div class="container">
        <p>© 2026 FoodSave – Plateforme Anti-Gaspillage. Tous droits réservés.</p>
    </div>
</footer>

<script src="/wf/public/assets/js/script.js?v=2.0"></script>
</body>
</html>
