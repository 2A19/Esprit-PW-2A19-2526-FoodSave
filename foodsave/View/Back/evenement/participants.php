<?php
if(!isset($rows) || !isset($stats) || !isset($plabels) || !isset($pbadge) || !isset($slabels) || !isset($sbadge)) {
    header('Location: admin.php?action=participants');
    exit;
}
$events = $events ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Admin : Participants</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .btn-add{display:flex;align-items:center;gap:8px;padding:10px 22px;background:#16a34a;border:none;border-radius:50px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;box-shadow:0 0 20px rgba(22,163,74,0.35);transition:all 0.2s;text-decoration:none}
        .btn-add:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(22,163,74,0.5)}
        .stats-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem}
        .stat-card{background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:16px;padding:20px;display:flex;align-items:center;gap:16px;transition:all 0.25s;position:relative;overflow:hidden}
        .stat-card::before{content:"";position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#16a34a,#fbbf24,#ef4444);opacity:0.6}
        .stat-card:hover{background:rgba(74,222,128,0.05);border-color:rgba(74,222,128,0.22);transform:translateY(-3px)}
        .stat-icon-wrap{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
        .si-green{background:rgba(74,222,128,0.12)}
        .si-blue{background:rgba(59,130,246,0.12)}
        .si-orange{background:rgba(251,191,36,0.12)}
        .si-red{background:rgba(239,68,68,0.12)}
        .stat-val{font-size:30px;font-weight:700;color:#fff;line-height:1}
        .stat-lbl{font-size:11px;font-weight:600;color:rgba(255,255,255,0.35);letter-spacing:0.08em;text-transform:uppercase;margin-top:3px}
        .filter-bar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:14px 20px;border-bottom:1px solid rgba(255,255,255,0.05)}
        .filter-input{flex:1;min-width:200px;padding:10px 16px;background:rgba(255,255,255,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:30px;font-family:"DM Sans",sans-serif;font-size:0.85rem;color:#fff}
        .filter-input:focus{outline:none;border-color:rgba(74,222,128,0.5);background:rgba(74,222,128,0.05)}
        .filter-input::placeholder{color:rgba(255,255,255,0.3)}
        .filter-select{padding:10px 16px;background:rgba(255,255,255,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:30px;font-family:"DM Sans",sans-serif;font-size:0.85rem;color:#fff;min-width:150px}
        .filter-select option{background:#0d1f14;color:#fff}
        .table-wrap{background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:20px;overflow:hidden}
        .table-head{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid rgba(255,255,255,0.05)}
        .table-head-title{font-size:15px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px}
        .success{background:rgba(34,197,94,0.12);color:#4ade80;padding:12px;border-radius:12px;margin-bottom:1rem;display:flex;align-items:center;gap:10px;border:1px solid rgba(34,197,94,0.2)}
        table{width:100%;border-collapse:collapse}
        thead tr{background:rgba(74,222,128,0.05)}
        th{padding:14px 20px;text-align:left;font-size:11px;font-weight:700;color:rgba(255,255,255,0.35);letter-spacing:0.1em;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,0.05);cursor:pointer;user-select:none}
        th:hover{color:#4ade80}
        td{padding:14px 20px;font-size:13px;color:rgba(255,255,255,0.75);border-bottom:1px solid rgba(255,255,255,0.04)}
        tr:hover td{background:rgba(74,222,128,0.04)}
        .badge{padding:4px 12px;border-radius:50px;font-size:11px;font-weight:700}
        .action-btns{display:flex;gap:6px}
        .btn-edit,.btn-delete{display:inline-flex;align-items:center;justify-content:center;padding:6px 12px;border-radius:8px;border:none;cursor:pointer;font-size:12px;font-weight:500;transition:all 0.2s;text-decoration:none;font-family:inherit}
        .btn-edit{background:rgba(251,191,36,0.12);color:#fbbf24}
        .btn-edit:hover{background:rgba(251,191,36,0.25);transform:scale(1.02)}
        .btn-delete{background:rgba(239,68,68,0.12);color:#f87171}
        .btn-delete:hover{background:rgba(239,68,68,0.25);transform:scale(1.02)}
        .sort-indicator{font-size:0.7rem;opacity:0.5;margin-left:4px}
        .main-content::-webkit-scrollbar{width:6px}
        .main-content::-webkit-scrollbar-thumb{background:rgba(74,222,128,0.2);border-radius:3px}
        @media (max-width:768px){
            .sidebar{width:80px}
            .sidebar-header .logo-area span,.sidebar-menu a span{display:none}
            .main-content{margin-left:80px}
            .stats-cards{grid-template-columns:1fr}
            .filter-bar{flex-direction:column}
            .filter-input,.filter-select{width:100%}
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
            <li><a href="admin.php?action=participants" class="active"><i class="fas fa-users"></i> <span>Participants</span></a></li>
            <li><a href="admin.php?action=evenementStats"><i class="fas fa-chart-bar"></i> <span>Statistiques</span></a></li>
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
                    <a href="admin.php?action=participants" class="nav-link active">Participants</a>
                </div>
                <div class="user-actions">
                    <button class="login-btn login-outline"><i class="fas fa-user"></i> Profil</button>
                    <button class="login-btn login-primary"><i class="fas fa-sign-out-alt"></i> Deconnexion</button>
                </div>
            </div>
        </nav>
        <div class="content-header">
            <h1><i class="fas fa-users"></i> Gestion des participants</h1>
            <a href="admin.php?action=participantForm" class="btn-add"><i class="fas fa-plus"></i> Ajouter un participant</a>
        </div>
        <?php if($flash): ?>
        <div class="alert alert-<?php echo $flash['type'] ?? 'success'; ?>"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($flash['msg'] ?? ''); ?></div>
        <?php endif; ?>
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon-wrap si-green"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-val"><?php echo $stats['total'] ?? 0; ?></div>
                    <div class="stat-lbl">Total participants</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-blue"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-val"><?php echo $stats['confirmed'] ?? 0; ?></div>
                    <div class="stat-lbl">Confirmes</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-orange"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="stat-val"><?php echo $stats['pending'] ?? 0; ?></div>
                    <div class="stat-lbl">En attente</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrap si-red"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-val"><?php echo $stats['cancelled'] ?? 0; ?></div>
                    <div class="stat-lbl">Annules</div>
                </div>
            </div>
        </div>
        <div class="table-wrap">
            <div class="table-head">
                <div class="table-head-title"><i class="fas fa-list-ul"></i> Liste des participants</div>
            </div>
            <div class="filter-bar">
                <input type="text" id="searchInput" class="filter-input" placeholder="Rechercher un participant..." value="<?php echo htmlspecialchars($search); ?>">
                <select id="statutFilter" class="filter-select">
                    <option value="">Tous les statuts</option>
                    <?php foreach($plabels as $k => $v): ?>
                    <option value="<?php echo $k; ?>" <?php echo ($statut === $k) ? 'selected' : ''; ?>><?php echo $v; ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="eventFilter" class="filter-select">
                    <option value="">Tous les evenements</option>
                    <?php foreach($events as $e): ?>
                    <option value="<?php echo $e['id']; ?>" <?php echo ($eventId == $e['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($e['titre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="overflow-x:auto">
                <table id="participantsTable">
                    <thead>
                        <tr>
                            <th data-sort="id"># <span class="sort-indicator">⇅</span></th>
                            <th data-sort="nom">Participant <span class="sort-indicator">⇅</span></th>
                            <th data-sort="email">Email <span class="sort-indicator">⇅</span></th>
                            <th data-sort="tel">Telephone <span class="sort-indicator">⇅</span></th>
                            <th data-sort="event">Evenement <span class="sort-indicator">⇅</span></th>
                            <th data-sort="statut">Statut <span class="sort-indicator">⇅</span></th>
                            <th data-sort="date">Inscrit le <span class="sort-indicator">⇅</span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($rows) > 0): ?>
                        <?php foreach($rows as $r): ?>
                        <tr>
                            <td><?php echo $r['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars(($r['prenom'] ?? '') . ' ' . ($r['nom'] ?? '')); ?></strong></td>
                            <td><?php echo htmlspecialchars($r['email'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($r['telephone'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($r['ev_titre'] ?? '-'); ?></td>
                            <td><span class="badge" style="background:<?php echo $pbadge[$r['statut']] ?? 'rgba(74,222,128,0.12)'; ?>;color:#fff"><?php echo $plabels[$r['statut']] ?? $r['statut']; ?></span></td>
                            <td><?php echo isset($r['date_inscription']) ? date('d/m/Y', strtotime($r['date_inscription'])) : '-'; ?></td>
                            <td class="action-btns">
                                <a href="admin.php?action=participantForm&id=<?php echo $r['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                                <button class="btn-edit" title="Envoyer email" onclick="sendEmailModal(<?php echo $r['id']; ?>, '<?php echo htmlspecialchars(addslashes($r['email'] ?? '')); ?>', '<?php echo htmlspecialchars(addslashes(($r['prenom'] ?? '') . ' ' . ($r['nom'] ?? ''))); ?>')"><i class="fas fa-envelope"></i></button>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer ce participant ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr><td colspan="8" style="text-align:center;padding:32px;color:rgba(255,255,255,0.4)">Aucun participant trouve.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    var searchInput = document.getElementById('searchInput');
    var statutFilter = document.getElementById('statutFilter');
    var eventFilter = document.getElementById('eventFilter');
    var tbody = document.querySelector('#participantsTable tbody');
    var rows = Array.from(tbody.querySelectorAll('tr'));
    var sortState = {column:null,direction:'asc'};

    function filterRows(){
        var term = searchInput.value.toLowerCase().trim();
        var st = statutFilter.value;
        var ev = eventFilter.value;
        rows.forEach(function(row){
            if(row.cells.length < 2) return;
            var nom = (row.cells[1] ? row.cells[1].innerText : '').toLowerCase();
            var email = (row.cells[2] ? row.cells[2].innerText : '').toLowerCase();
            var statutTxt = (row.cells[5] ? row.cells[5].innerText : '').toLowerCase();
            var eventTxt = (row.cells[4] ? row.cells[4].innerText : '').toLowerCase();
            var matchT = term === '' || nom.includes(term) || email.includes(term);
            var matchS = st === '' || statutTxt.indexOf(st) !== -1;
            var matchE = ev === '' || true;
            row.style.display = matchT && matchS ? '' : 'none';
        });
    }

    function sortRows(){
        if(!sortState.column) return;
        var colIdx = {'id':0,'nom':1,'email':2,'tel':3,'event':4,'statut':5,'date':6}[sortState.column];
        if(colIdx === undefined) return;
        var visible = rows.filter(function(r){return r.style.display !== 'none';});
        var hidden = rows.filter(function(r){return r.style.display === 'none';});
        visible.sort(function(a,b){
            var va = a.cells[colIdx] ? a.cells[colIdx].innerText.trim() : '';
            var vb = b.cells[colIdx] ? b.cells[colIdx].innerText.trim() : '';
            if(sortState.column === 'id'){
                va = parseInt(va) || 0;
                vb = parseInt(vb) || 0;
                return sortState.direction === 'asc' ? va - vb : vb - va;
            }
            return sortState.direction === 'asc' ? va.localeCompare(vb) : vb.localeCompare(va);
        });
        visible.forEach(function(r){tbody.appendChild(r);});
        hidden.forEach(function(r){tbody.appendChild(r);});
        updateSortIndicators();
    }

    function updateSortIndicators(){
        document.querySelectorAll('th .sort-indicator').forEach(function(s){s.textContent='⇅';s.style.opacity='0.5';});
        if(sortState.column){
            var th = document.querySelector('th[data-sort="'+sortState.column+'"]');
            if(th){
                var ind = th.querySelector('.sort-indicator');
                ind.textContent = sortState.direction === 'asc' ? '▲' : '▼';
                ind.style.opacity = '1';
            }
        }
    }

    searchInput.addEventListener('input',function(){filterRows();sortRows();});
    statutFilter.addEventListener('change',function(){filterRows();sortRows();});
    eventFilter.addEventListener('change',function(){filterRows();sortRows();});
    document.querySelectorAll('th[data-sort]').forEach(function(th){
        th.addEventListener('click',function(){
            var col = th.dataset.sort;
            if(sortState.column === col){
                sortState.direction = sortState.direction === 'asc' ? 'desc' : 'asc';
            } else {
                sortState.column = col;
                sortState.direction = 'asc';
            }
            sortRows();
        });
    });
})();
</script>
<div id="toasts" style="position:fixed;top:20px;right:20px;z-index:99999;min-width:260px"></div>
<div id="gifZone" style="position:fixed;bottom:20px;left:20px;z-index:99999"></div>
<script src="./assets/js/features.js"></script>
<script>
function sendEmailModal(id, email, name) {
    var msg = prompt('Message a envoyer a ' + name + ' (' + email + '):', 'Bonjour ' + name + ', nous vous confirmons votre participation a notre evenement FoodSave. Merci!');
    if (msg) {
        sendEmail(email, msg, null);
    }
}
</script>
</body>
</html>
