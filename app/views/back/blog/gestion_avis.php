<?php
// Vérifier que les variables existent
if(!isset($avis) || !isset($totalAvis) || !isset($totalPending) || !isset($totalApproved) || !isset($averageNote)) {
    header('Location: index.php?action=adminArticles');
    exit;
}

// Démarrer la session pour les notifications
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Détecter si c'est un rafraîchissement après ajout d'avis
$shouldPlaySound = isset($_GET['refresh']) && $_GET['refresh'] == 1;

// Vérifier s'il y a de nouvelles notifications (non lues)
$hasNewNotifications = false;
if(isset($_SESSION['notifications']) && !empty($_SESSION['notifications'])) {
    foreach($_SESSION['notifications'] as $notif) {
        if(!isset($notif['lu']) || !$notif['lu']) {
            $hasNewNotifications = true;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Admin : Gestion des avis</title>
    
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* ========== STYLE COMPLET DU TEMPLATE ========== */
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:"DM Sans",sans-serif;background:#0d1f14;color:#e8f5e9;overflow-x:hidden}

        /* Shared BG */
        .bg-mesh{position:fixed;inset:0;pointer-events:none;z-index:0}
        .glow-1{position:absolute;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(34,197,94,0.18) 0%,transparent 70%);top:-80px;right:-60px;animation:driftA 8s ease-in-out infinite alternate}
        .glow-2{position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(16,185,129,0.12) 0%,transparent 70%);bottom:0;left:-80px;animation:driftB 10s ease-in-out infinite alternate}
        .glow-3{position:absolute;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(234,179,8,0.09) 0%,transparent 70%);top:40%;left:38%;animation:driftA 12s ease-in-out infinite alternate}
        @keyframes driftA{from{transform:translate(0,0)}to{transform:translate(30px,20px)}}
        @keyframes driftB{from{transform:translate(0,0)}to{transform:translate(-20px,-30px)}}
        .grid-lines{position:absolute;inset:0;background-image:linear-gradient(rgba(34,197,94,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(34,197,94,0.04) 1px,transparent 1px);background-size:48px 48px}

        /* Admin layout */
        .admin-container{display:flex;min-height:100vh;position:relative;z-index:1}
        
        /* Sidebar */
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
        
        /* Main content */
        .main-content{
            flex:1;
            margin-left:280px;
            padding:2rem;
            min-height:100vh;
            position:relative;
            z-index:5;
        }
        
        /* Navbar */
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
        
        /* Content header */
        .content-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem}
        .content-header h1{font-size:26px;font-weight:700;color:#fff;letter-spacing:-0.8px}
        .content-header h1 i{color:#4ade80}
        
        /* Stats cards */
        .stats-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem}
        .stat-card{
            background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);
            border-radius:16px;padding:20px;display:flex;align-items:center;gap:16px;
            transition:all 0.25s;position:relative;overflow:hidden
        }
        .stat-card::before{content:"";position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#16a34a,#fbbf24,#ef4444);opacity:0.6}
        .stat-card:hover{background:rgba(74,222,128,0.05);border-color:rgba(74,222,128,0.22);transform:translateY(-3px)}
        .stat-icon-wrap{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
        .si-green{background:rgba(74,222,128,0.12)}.si-blue{background:rgba(59,130,246,0.12)}.si-orange{background:rgba(251,191,36,0.12)}.si-red{background:rgba(239,68,68,0.12)}
        .stat-val{font-size:30px;font-weight:700;color:#fff;line-height:1}
        .stat-lbl{font-size:11px;font-weight:600;color:rgba(255,255,255,0.35);letter-spacing:0.08em;text-transform:uppercase;margin-top:3px}
        
        /* Search bar - CORRIGÉE */
        .search-bar-container{margin-bottom:20px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:0 20px}
        .search-input{
            flex:2;min-width:200px;padding:12px 18px;
            background-color:#1a2a1a !important;
            border:1px solid #4ade80 !important;
            border-radius:30px;
            color:#ffffff !important;
            font-family:"DM Sans",sans-serif;
            font-size:0.9rem;
        }
        .search-input:focus{
            outline:none;
            border-color:#fbbf24 !important;
            background-color:#0d1f14 !important;
            box-shadow:0 0 0 3px rgba(74,222,128,0.2);
        }
        .search-input::placeholder{
            color:#8ba888 !important;
        }
        .status-filter{
            padding:12px 18px;
            background-color:#1a2a1a !important;
            border:1px solid #4ade80 !important;
            border-radius:30px;
            color:#ffffff !important;
            font-family:"DM Sans",sans-serif;
            cursor:pointer;
        }
        .status-filter:focus{
            outline:none;
            border-color:#fbbf24 !important;
        }
        .status-filter option{
            background-color:#1a2a1a !important;
            color:#ffffff !important;
        }
        .btn-clear{background:#6c757d;color:#fff;border:none;padding:10px 20px;border-radius:30px;cursor:pointer;font-weight:500;transition:all 0.2s}
        .btn-clear:hover{background:#5a6268;transform:translateY(-1px)}
        .search-count{margin-top:8px;font-size:0.8rem;color:rgba(255,255,255,0.4);padding:0 20px}
        
        /* Table */
        .table-wrap{background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:20px;overflow:hidden}
        .table-head{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid rgba(255,255,255,0.05)}
        .table-head-title{font-size:15px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px}
        
        table{width:100%;border-collapse:collapse}
        thead tr{background:rgba(74,222,128,0.05)}
        th{padding:14px 20px;text-align:left;font-size:11px;font-weight:700;color:rgba(255,255,255,0.35);letter-spacing:0.1em;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,0.05)}
        td{padding:14px 20px;font-size:13px;color:rgba(255,255,255,0.75);border-bottom:1px solid rgba(255,255,255,0.04)}
        tr:hover td{background:rgba(74,222,128,0.04)}
        
        /* Badges */
        .badge-status{padding:4px 12px;border-radius:50px;font-size:11px;font-weight:700}
        .badge-status.approuve{background:rgba(34,197,94,0.15);color:#4ade80;border:1px solid rgba(34,197,94,0.3)}
        .badge-status.en-attente{background:rgba(251,191,36,0.15);color:#fbbf24;border:1px solid rgba(251,191,36,0.3)}
        
        .stars{color:#ffc107;font-size:0.85rem;letter-spacing:2px}
        
        /* Action buttons */
        .action-btns{display:flex;gap:6px}
        .btn-edit,.btn-validate,.btn-delete{
            display:inline-flex;align-items:center;justify-content:center;
            padding:6px 12px;border-radius:8px;border:none;cursor:pointer;
            font-size:12px;font-weight:500;transition:all 0.2s;text-decoration:none
        }
        .btn-edit{background:rgba(251,191,36,0.12);color:#fbbf24}
        .btn-edit:hover{background:rgba(251,191,36,0.25);transform:scale(1.02)}
        .btn-validate{background:rgba(34,197,94,0.12);color:#4ade80}
        .btn-validate:hover{background:rgba(34,197,94,0.25);transform:scale(1.02)}
        .btn-delete{background:rgba(239,68,68,0.12);color:#f87171}
        .btn-delete:hover{background:rgba(239,68,68,0.25);transform:scale(1.02)}
        
        /* Notification styles */
        .notification-item{
            background:rgba(34,197,94,0.08);border-left:4px solid #4ade80;
            padding:12px 15px;margin-bottom:10px;border-radius:8px;
            display:flex;justify-content:space-between;align-items:center;
            animation:slideIn 0.3s ease
        }
        @keyframes slideIn{from{opacity:0;transform:translateX(-20px)}to{opacity:1;transform:translateX(0)}}
        .notification-item strong{color:#4ade80}
        .notification-item small{color:rgba(255,255,255,0.4);font-size:0.7rem}
        .notification-close{background:none;border:none;color:rgba(255,255,255,0.4);cursor:pointer;font-size:1rem}
        .notification-close:hover{color:#f87171}
        .btn-clear-all{background:#dc3545;color:#fff;padding:5px 12px;border-radius:20px;text-decoration:none;font-size:12px;transition:all 0.2s}
        .btn-clear-all:hover{background:#c82333;transform:translateY(-1px)}
        
        /* Toast */
        .toast-notification{position:fixed;bottom:30px;right:30px;z-index:99999;background:#0f1f10;border:1px solid rgba(74,222,128,0.3);border-radius:12px;padding:14px 20px;display:flex;align-items:center;gap:10px;font-size:13px;font-weight:600;color:#fff;box-shadow:0 8px 32px rgba(0,0,0,0.4);transform:translateY(80px);opacity:0;transition:all 0.35s cubic-bezier(0.34,1.56,0.64,1);pointer-events:none}
        .toast-notification.show{transform:translateY(0);opacity:1}
        
        /* Success messages */
        .success{background:rgba(34,197,94,0.12);color:#4ade80;padding:12px;border-radius:12px;margin-bottom:1rem;display:flex;align-items:center;gap:10px;border:1px solid rgba(34,197,94,0.2)}
        
        /* Scrollbar */
        .main-content::-webkit-scrollbar{width:6px}
        .main-content::-webkit-scrollbar-thumb{background:rgba(74,222,128,0.2);border-radius:3px}
        
        @media (max-width:768px){
            .sidebar{width:80px}
            .sidebar-header .logo-area span,.sidebar-menu a span{display:none}
            .main-content{margin-left:80px}
            .stats-cards{grid-template-columns:1fr}
            .search-bar-container{flex-direction:column}
            .search-input,.status-filter{width:100%}
            .btn-clear{width:100%}
        }
    </style>
</head>
<body>

<!-- Shared background -->
<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div><div class="glow-3"></div>
</div>

<div class="admin-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-area">
                <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="Logo">
                <span>FoodSave Admin</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php?action=adminArticles"><i class="fas fa-newspaper"></i> <span>Articles</span></a></li>
            <li><a href="index.php?action=adminAvis" class="active"><i class="fas fa-star"></i> <span>Avis</span></a></li>
            <li><a href="index.php?action=statsEvolution"><i class="fas fa-chart-line"></i> <span>Statistiques</span></a></li>
            <li><a href="#"><i class="fas fa-users"></i> <span>Utilisateurs</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Navbar -->
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="Logo">
                    <span>FoodSave Admin</span>
                </div>
                <div class="nav-menu">
                    <a href="index.php?action=adminArticles" class="nav-link">Articles</a>
                    <a href="index.php?action=adminAvis" class="nav-link active">Avis</a>
                </div>
                <div class="user-actions">
                    <button class="login-btn login-outline"><i class="fas fa-user"></i> Profil</button>
                    <button class="login-btn login-primary"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
                </div>
            </div>
        </nav>

        <!-- Notifications -->
        <div style="margin-bottom: 20px;">
            <?php if(isset($_SESSION['notifications']) && !empty($_SESSION['notifications'])): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4 style="margin: 0; color: #fff;">📢 Notifications</h4>
                <a href="index.php?action=clearAllNotifications" 
                   onclick="return confirm('⚠️ Supprimer TOUTES les notifications ? Cette action est irréversible.')" 
                   class="btn-clear-all">
                    🗑️ Tout supprimer
                </a>
            </div>
            <?php endif; ?>
            
            <?php
            if(isset($_SESSION['notifications']) && !empty($_SESSION['notifications'])) {
                foreach($_SESSION['notifications'] as $key => $notif) {
                    $isUnread = !isset($notif['lu']) || !$notif['lu'];
                    echo '<div class="notification-item" style="' . ($isUnread ? 'background: rgba(255,152,0,0.1); border-left-color: #ff9800;' : '') . '">';
                    echo '<div>';
                    echo '<strong>🔔 Notification</strong><br>';
                    echo htmlspecialchars($notif['message']);
                    echo '<br><small>' . $notif['date'] . '</small>';
                    echo '</div>';
                    echo '<div>';
                    echo '<a href="index.php?action=clearNotification&key=' . $key . '" class="notification-close" title="Supprimer">✖</a>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>

        <!-- Header -->
        <div class="content-header">
            <h1><i class="fas fa-star" style="color: #ffc107;"></i> Gestion des avis</h1>
        </div>

        <!-- Messages de succès -->
        <?php if(isset($_GET['success'])): ?>
            <?php if($_GET['success'] == 'approved'): ?>
                <div class="success"><i class="fas fa-check-circle"></i> Avis approuvé !</div>
            <?php elseif($_GET['success'] == 'updated'): ?>
                <div class="success"><i class="fas fa-check-circle"></i> Avis modifié !</div>
            <?php elseif($_GET['success'] == 'deleted'): ?>
                <div class="success"><i class="fas fa-check-circle"></i> Avis supprimé !</div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon-wrap si-green">⭐</div>
                <div>
                    <div class="stat-val"><?php echo $totalAvis; ?></div>
                    <div class="stat-lbl">Total avis</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-blue">✅</div>
                <div>
                    <div class="stat-val"><?php echo $totalApproved; ?></div>
                    <div class="stat-lbl">Approuvés</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-orange">⏳</div>
                <div>
                    <div class="stat-val"><?php echo $totalPending; ?></div>
                    <div class="stat-lbl">En attente</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-red">📊</div>
                <div>
                    <div class="stat-val"><?php echo $averageNote; ?>/5</div>
                    <div class="stat-lbl">Note moyenne</div>
                </div>
            </div>
        </div>
        
        <!-- Tableau des avis avec barre de recherche -->
        <div class="table-wrap">
            <div class="table-head">
                <div class="table-head-title"><i class="fas fa-list-ul"></i> Liste des avis</div>
            </div>
            
            <!-- Barre de recherche dynamique + filtre statut (CORRIGÉE) -->
            <div style="padding: 0 20px 20px 20px;">
                <div class="search-bar-container">
                    <input type="text" id="searchAvisInput" class="search-input" placeholder="🔍 Rechercher par article, utilisateur ou contenu...">
                    <select id="searchStatusFilter" class="status-filter">
                        <option value="">📋 Tous les statuts</option>
                        <option value="approuvé">✅ Approuvé</option>
                        <option value="en attente">⏳ En attente</option>
                    </select>
                    <button id="clearSearchAvisBtn" class="btn-clear">✖ Effacer</button>
                </div>
                <div id="searchCount" class="search-count"></div>
            </div>
            
            <div class="table-responsive" style="overflow-x: auto;">
                <table style="width: 100%; min-width: 800px;" id="avisTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 20%;">Article</th>
                            <th style="width: 15%;">Utilisateur</th>
                            <th style="width: 10%;">Note</th>
                            <th style="width: 25%;">Avis</th>
                            <th style="width: 10%;">Statut</th>
                            <th style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($avis as $a): ?>
                        <tr data-statut="<?php echo $a['statut']; ?>">
                            <td><?php echo $a['id']; ?></td>
                            <td><?php echo htmlspecialchars(substr($a['article_titre'], 0, 30)) . (strlen($a['article_titre']) > 30 ? '...' : ''); ?></td>
                            <td><?php echo htmlspecialchars($a['user_name'] ?? 'Utilisateur'); ?></td>
                            <td class="stars" style="white-space: nowrap;">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= $a['note']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                              </td>
                            <td style="word-wrap: break-word; max-width: 250px;">
                                <?php echo htmlspecialchars(substr($a['contenu'], 0, 60)) . (strlen($a['contenu']) > 60 ? '...' : ''); ?>
                              </td>
                            <td>
                                <?php if($a['statut'] == 'approuvé'): ?>
                                    <span class="badge-status approuve">Approuvé</span>
                                <?php else: ?>
                                    <span class="badge-status en-attente">En attente</span>
                                <?php endif; ?>
                              </td>
                            <td class="action-btns">
                                <a href="index.php?action=editAvisForm&id=<?php echo $a['id']; ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <?php if($a['statut'] == 'en attente'): ?>
                                    <a href="index.php?action=approveAvis&id=<?php echo $a['id']; ?>" class="btn-validate" onclick="return confirm('Approuver cet avis ?')">
                                        <i class="fas fa-check"></i> Approuver
                                    </a>
                                <?php endif; ?>
                                <a href="index.php?action=deleteAvis&id=<?php echo $a['id']; ?>" class="btn-delete" onclick="return confirm('Supprimer cet avis ?')">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </a>
                              </td>
                          </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript pour la recherche dynamique + filtre statut -->
<script>
// Recherche dynamique dans le tableau des avis
const searchAvisInput = document.getElementById('searchAvisInput');
const statusFilter = document.getElementById('searchStatusFilter');
const clearAvisBtn = document.getElementById('clearSearchAvisBtn');
const avisRows = document.querySelectorAll('#avisTable tbody tr');
const searchCountSpan = document.getElementById('searchCount');

function filterAvis() {
    const searchTerm = searchAvisInput.value.toLowerCase().trim();
    const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
    let visibleCount = 0;
    
    avisRows.forEach(row => {
        const articleCell = row.querySelector('td:nth-child(2)');
        const userCell = row.querySelector('td:nth-child(3)');
        const contentCell = row.querySelector('td:nth-child(5)');
        
        let article = articleCell ? articleCell.innerText.toLowerCase() : '';
        let user = userCell ? userCell.innerText.toLowerCase() : '';
        let content = contentCell ? contentCell.innerText.toLowerCase() : '';
        let status = row.getAttribute('data-statut') || '';
        
        const matchSearch = (searchTerm === '' || 
                            article.includes(searchTerm) || 
                            user.includes(searchTerm) || 
                            content.includes(searchTerm));
        const matchStatus = (statusValue === '' || status === statusValue);
        
        if (matchSearch && matchStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    if (searchCountSpan) {
        let message = `📊 ${visibleCount} avis(s) affiché(s)`;
        if (searchTerm !== '') {
            message = `🔍 ${visibleCount} résultat(s) trouvé(s) pour "${searchTerm}"`;
        }
        if (statusValue !== '') {
            let statusText = (statusValue === 'approuvé') ? 'Approuvé' : 'En attente';
            message += ` (filtre: ${statusText})`;
        }
        searchCountSpan.innerHTML = message;
    }
}

if (searchAvisInput) {
    searchAvisInput.addEventListener('input', filterAvis);
    searchAvisInput.addEventListener('keyup', filterAvis);
}
if (statusFilter) statusFilter.addEventListener('change', filterAvis);
if (clearAvisBtn) {
    clearAvisBtn.addEventListener('click', function() {
        searchAvisInput.value = '';
        if (statusFilter) statusFilter.value = '';
        filterAvis();
        searchAvisInput.focus();
    });
}

filterAvis();
</script>

<!-- Script pour les notifications sonores (optionnel) -->
<script>
    let lastTimestamp = <?php echo time(); ?>;
    let audioContext = null;
    
    function initAudio() {
        if (!audioContext) {
            try {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            } catch(e) {}
        }
    }
    
    function playBeep() {
        if (!audioContext) return;
        try {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            oscillator.frequency.value = 880;
            gainNode.gain.value = 0.2;
            oscillator.start();
            gainNode.gain.exponentialRampToValueAtTime(0.00001, audioContext.currentTime + 0.3);
            oscillator.stop(audioContext.currentTime + 0.3);
            audioContext.resume();
        } catch(e) {}
    }
    
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = '🔔 ' + message;
        document.body.appendChild(toast);
        setTimeout(() => { if(toast.parentNode) toast.remove(); }, 3000);
        setTimeout(() => toast.classList.add('show'), 10);
    }
    
    function checkNotifications() {
        fetch('index.php?action=checkNotifications&last=' + lastTimestamp)
            .then(response => response.json())
            .then(data => {
                if(data.new && data.new.length > 0) {
                    initAudio();
                    playBeep();
                    data.new.forEach(notif => showToast(notif.message));
                    location.reload();
                }
                lastTimestamp = data.timestamp;
            })
            .catch(() => {});
    }
    
    document.addEventListener('click', function initOnClick() {
        initAudio();
        document.removeEventListener('click', initOnClick);
    });
    
    setInterval(checkNotifications, 5000);
    checkNotifications();
    
    <?php if($shouldPlaySound): ?>
    setTimeout(() => { initAudio(); playBeep(); }, 100);
    <?php endif; ?>
</script>

</body>
</html>