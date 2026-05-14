<?php
// Vérifier que les variables existent
if(!isset($articles) || !isset($totalArticles) || !isset($totalPublished) || !isset($totalDrafts) || !isset($totalViews)) {
    header('Location: admin.php?action=adminArticles');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Admin : Gestion des articles</title>
    
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
        .btn-add{display:flex;align-items:center;gap:8px;padding:10px 22px;background:#16a34a;border:none;border-radius:50px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;box-shadow:0 0 20px rgba(22,163,74,0.35);transition:all 0.2s;text-decoration:none}
        .btn-add:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(22,163,74,0.5)}
        
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
        
        /* Search bar */
        .search-bar-container{margin-bottom:20px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:0 20px}
        .search-input{flex:1;min-width:250px;padding:12px 18px;background:rgba(255,255,255,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:30px;font-family:"DM Sans",sans-serif;font-size:0.9rem;color:#fff}
        .search-input:focus{outline:none;border-color:rgba(74,222,128,0.5);background:rgba(74,222,128,0.05)}
        .search-input::placeholder{color:rgba(255,255,255,0.3)}
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
        .badge-category{background:rgba(74,222,128,0.12);color:#4ade80;padding:4px 12px;border-radius:50px;font-size:11px;font-weight:500}
        .badge{padding:4px 12px;border-radius:50px;font-size:11px;font-weight:700}
        .badge.publie{background:rgba(34,197,94,0.15);color:#4ade80;border:1px solid rgba(34,197,94,0.3)}
        .badge.brouillon{background:rgba(251,191,36,0.15);color:#fbbf24;border:1px solid rgba(251,191,36,0.3)}
        
        /* Action buttons */
        .action-btns{display:flex;gap:6px}
        .btn-edit,.btn-delete{
            display:inline-flex;align-items:center;justify-content:center;
            padding:6px 12px;border-radius:8px;border:none;cursor:pointer;
            font-size:12px;font-weight:500;transition:all 0.2s;text-decoration:none
        }
        .btn-edit{background:rgba(251,191,36,0.12);color:#fbbf24}
        .btn-edit:hover{background:rgba(251,191,36,0.25);transform:scale(1.02)}
        .btn-delete{background:rgba(239,68,68,0.12);color:#f87171}
        .btn-delete:hover{background:rgba(239,68,68,0.25);transform:scale(1.02)}
        
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
            .search-input,.btn-clear{width:100%}
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
                <img src="./assets/images/logo-foodsave.png" alt="Logo">
                <span>FoodSave Admin</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin.php?action=adminArticles" class="active"><i class="fas fa-newspaper"></i> <span>Articles</span></a></li>
            <li><a href="admin.php?action=adminAvis"><i class="fas fa-star"></i> <span>Avis</span></a></li>
            <li><a href="admin.php?action=statsEvolution"><i class="fas fa-chart-line"></i> <span>Statistiques</span></a></li>
            <li><a href="#"><i class="fas fa-users"></i> <span>Utilisateurs</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Navbar -->
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <img src="./assets/images/logo-foodsave.png" alt="Logo">
                    <span>FoodSave Admin</span>
                </div>
                <div class="nav-menu">
                    <a href="admin.php?action=adminArticles" class="nav-link active">Articles</a>
                    <a href="admin.php?action=adminAvis" class="nav-link">Avis</a>
                </div>
                <div class="user-actions">
                    <button class="login-btn login-outline"><i class="fas fa-user"></i> Profil</button>
                    <button class="login-btn login-primary"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
                </div>
            </div>
        </nav>

        <!-- Header -->
        <div class="content-header">
            <h1><i class="fas fa-newspaper"></i> Gestion des articles</h1>
            <a href="admin.php?action=addArticleForm" class="btn-add"><i class="fas fa-plus"></i> Nouvel article</a>
        </div>

        <!-- Messages de succès -->
        <?php if(isset($_GET['success'])): ?>
            <?php if($_GET['success'] == 'created'): ?>
                <div class="success"><i class="fas fa-check-circle"></i> Article créé avec succès !</div>
            <?php elseif($_GET['success'] == 'updated'): ?>
                <div class="success"><i class="fas fa-check-circle"></i> Article modifié avec succès !</div>
            <?php elseif($_GET['success'] == 'deleted'): ?>
                <div class="success"><i class="fas fa-check-circle"></i> Article supprimé avec succès !</div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon-wrap si-green">📝</div>
                <div>
                    <div class="stat-val"><?php echo $totalArticles; ?></div>
                    <div class="stat-lbl">Total articles</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-blue">✅</div>
                <div>
                    <div class="stat-val"><?php echo $totalPublished; ?></div>
                    <div class="stat-lbl">Publiés</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-orange">📝</div>
                <div>
                    <div class="stat-val"><?php echo $totalDrafts; ?></div>
                    <div class="stat-lbl">Brouillons</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-red">👁️</div>
                <div>
                    <div class="stat-val"><?php echo $totalViews; ?></div>
                    <div class="stat-lbl">Vues totales</div>
                </div>
            </div>
        </div>

        <!-- Tableau des articles -->
        <div class="table-wrap">
            <div class="table-head">
                <div class="table-head-title"><i class="fas fa-list-ul"></i> Liste des articles</div>
            </div>
            
            <!-- Barre de recherche -->
            <div class="search-bar-container">
                <input type="text" id="searchArticleInput" class="search-input" placeholder="🔍 Rechercher un article par titre...">
                <button id="clearSearchBtn" class="btn-clear">✖ Effacer</button>
            </div>
            <div id="searchCount" class="search-count"></div>
            
            <div class="table-responsive" style="overflow-x: auto;">
                <table id="articlesTable">
                    <thead>
                        <tr>
                            <th>ID</th><th>Titre</th><th>Catégorie</th><th>Statut</th><th>Date</th><th>Vues</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($articles as $article): ?>
                        <tr data-titre="<?php echo strtolower(htmlspecialchars($article['titre'])); ?>">
                            <td><?php echo $article['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($article['titre']); ?></strong></td>
                            <td><span class="badge-category"><?php echo htmlspecialchars($article['categorie']); ?></span></td>
                            <td><span class="badge <?php echo $article['statut'] == 'publié' ? 'publie' : 'brouillon'; ?>"><?php echo $article['statut']; ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></td>
                            <td><?php echo $article['vue']; ?></td>
                            <td class="action-btns">
                                <a href="admin.php?action=editArticleForm&id=<?php echo $article['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Modifier</a>
                                <a href="admin.php?action=deleteArticle&id=<?php echo $article['id']; ?>" class="btn-delete" onclick="return confirm('Supprimer cet article ?')"><i class="fas fa-trash-alt"></i> Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript pour la recherche dynamique -->
<script>
const searchInput = document.getElementById('searchArticleInput');
const clearBtn = document.getElementById('clearSearchBtn');
const tableRows = document.querySelectorAll('#articlesTable tbody tr');
const searchCountSpan = document.getElementById('searchCount');

function filterArticles() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        const titleCell = row.querySelector('td:nth-child(2)');
        if (titleCell) {
            const title = titleCell.innerText.toLowerCase();
            if (searchTerm === '' || title.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    if (searchCountSpan) {
        if (searchTerm === '') {
            searchCountSpan.innerHTML = `📊 ${visibleCount} article(s) affiché(s)`;
        } else {
            searchCountSpan.innerHTML = `🔍 ${visibleCount} résultat(s) trouvé(s) pour "${searchTerm}"`;
        }
    }
}

if (searchInput) {
    searchInput.addEventListener('input', filterArticles);
    searchInput.addEventListener('keyup', filterArticles);
}

if (clearBtn) {
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        filterArticles();
        searchInput.focus();
    });
}

filterArticles();
</script>

</body>
</html>