<?php
if(!isset($ev) || !isset($participants) || !isset($slabels) || !isset($sbadge) || !isset($plabels) || !isset($pbadge)) {
    header('Location: admin.php?action=evenements');
    exit;
}
$nbParticipants = $nbParticipants ?? count($participants);
$totalCap = $ev['capacite'] ?? 1;
$progress = $totalCap > 0 ? round(($nbParticipants / $totalCap) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Admin : <?php echo htmlspecialchars($ev['titre']); ?></title>
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
        .btn-back{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:50px;color:rgba(255,255,255,0.6);font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all 0.2s}
        .btn-back:hover{background:rgba(255,255,255,0.1);color:#fff}
        .details-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem}
        .detail-card{background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:20px;padding:1.5rem}
        .detail-card h3{font-size:15px;font-weight:700;color:#fff;margin-bottom:1rem;display:flex;align-items:center;gap:8px}
        .detail-card h3 i{color:#4ade80}
        .detail-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.04);font-size:13px}
        .detail-row:last-child{border-bottom:none}
        .detail-label{color:rgba(255,255,255,0.4);font-weight:500}
        .detail-value{color:rgba(255,255,255,0.8);font-weight:600;text-align:right}
        .badge{padding:4px 12px;border-radius:50px;font-size:11px;font-weight:700}
        .progress-bar{height:8px;background:rgba(255,255,255,0.05);border-radius:10px;overflow:hidden;margin:12px 0}
        .progress-fill{height:100%;border-radius:10px;background:linear-gradient(90deg,#16a34a,#4ade80);transition:width 0.5s ease}
        .desc-text{color:rgba(255,255,255,0.7);font-size:13px;line-height:1.7;margin-top:12px}
        .table-wrap{background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:20px;overflow:hidden;margin-top:1.5rem}
        .table-head{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid rgba(255,255,255,0.05)}
        .table-head-title{font-size:15px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px}
        table{width:100%;border-collapse:collapse}
        thead tr{background:rgba(74,222,128,0.05)}
        th{padding:14px 20px;text-align:left;font-size:11px;font-weight:700;color:rgba(255,255,255,0.35);letter-spacing:0.1em;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,0.05)}
        td{padding:14px 20px;font-size:13px;color:rgba(255,255,255,0.75);border-bottom:1px solid rgba(255,255,255,0.04)}
        tr:hover td{background:rgba(74,222,128,0.04)}
        .action-btns{display:flex;gap:6px}
        .btn-edit,.btn-delete{display:inline-flex;align-items:center;justify-content:center;padding:6px 12px;border-radius:8px;border:none;cursor:pointer;font-size:12px;font-weight:500;transition:all 0.2s;text-decoration:none;font-family:inherit}
        .btn-edit{background:rgba(251,191,36,0.12);color:#fbbf24}
        .btn-edit:hover{background:rgba(251,191,36,0.25);transform:scale(1.02)}
        .btn-delete{background:rgba(239,68,68,0.12);color:#f87171}
        .btn-delete:hover{background:rgba(239,68,68,0.25);transform:scale(1.02)}
        @media (max-width:768px){
            .sidebar{width:80px}
            .sidebar-header .logo-area span,.sidebar-menu a span{display:none}
            .main-content{margin-left:80px}
            .details-grid{grid-template-columns:1fr}
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
            <li><a href="admin.php?action=evenements" class="active"><i class="fas fa-calendar-alt"></i> <span>Evenements</span></a></li>
            <li><a href="admin.php?action=participants"><i class="fas fa-users"></i> <span>Participants</span></a></li>
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
                    <a href="admin.php?action=participants" class="nav-link">Participants</a>
                </div>
                <div class="user-actions">
                    <button class="login-btn login-outline"><i class="fas fa-user"></i> Profil</button>
                    <button class="login-btn login-primary"><i class="fas fa-sign-out-alt"></i> Deconnexion</button>
                </div>
            </div>
        </nav>
        <div class="content-header">
            <h1><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($ev['titre']); ?></h1>
            <a href="admin.php?action=evenements" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
        <div class="details-grid">
            <div class="detail-card">
                <h3><i class="fas fa-info-circle"></i> Informations</h3>
                <div class="detail-row">
                    <span class="detail-label">Categorie</span>
                    <span class="detail-value"><?php echo htmlspecialchars($ev['categorie'] ?? '-'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Statut</span>
                    <span class="detail-value"><span class="badge" style="background:rgba(74,222,128,0.12);color:#4ade80"><?php echo $slabels[$ev['statut']] ?? $ev['statut']; ?></span></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date</span>
                    <span class="detail-value"><?php echo date('d/m/Y', strtotime($ev['date_event'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Lieu</span>
                    <span class="detail-value"><?php echo htmlspecialchars($ev['lieu'] ?? '-'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Organisateur</span>
                    <span class="detail-value"><?php echo htmlspecialchars($ev['organisateur'] ?? '-'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Cree le</span>
                    <span class="detail-value"><?php echo isset($ev['created_at']) ? date('d/m/Y H:i', strtotime($ev['created_at'])) : '-'; ?></span>
                </div>
            </div>
            <div class="detail-card">
                <h3><i class="fas fa-users"></i> Inscriptions</h3>
                <div class="detail-row">
                    <span class="detail-label">Participants</span>
                    <span class="detail-value"><?php echo $nbParticipants; ?> / <?php echo $totalCap; ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width:<?php echo min($progress,100); ?>%"></div>
                </div>
                <div style="text-align:right;font-size:12px;color:rgba(255,255,255,0.4)"><?php echo $progress; ?>% rempli</div>
                <?php if(!empty($ev['description'])): ?>
                <h3 style="margin-top:1.5rem"><i class="fas fa-align-left"></i> Description</h3>
                <div class="desc-text"><?php echo nl2br(htmlspecialchars($ev['description'])); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="table-wrap">
            <div class="table-head">
                <div class="table-head-title"><i class="fas fa-list-ul"></i> Participants inscrits (<?php echo $nbParticipants; ?>)</div>
            </div>
            <div style="overflow-x:auto">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>Nom</th><th>Email</th><th>Tel</th><th>Statut</th><th>Date inscription</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($participants) > 0): ?>
                        <?php foreach($participants as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '')); ?></strong></td>
                            <td><?php echo htmlspecialchars($p['email'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($p['telephone'] ?? '-'); ?></td>
                            <td><span class="badge" style="background:<?php echo $pbadge[$p['statut']] ?? 'rgba(74,222,128,0.12)'; ?>;color:#fff"><?php echo $plabels[$p['statut']] ?? $p['statut']; ?></span></td>
                            <td><?php echo isset($p['date_inscription']) ? date('d/m/Y', strtotime($p['date_inscription'])) : '-'; ?></td>
                            <td class="action-btns">
                                <a href="admin.php?action=participantForm&id=<?php echo $p['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                                <button class="btn-edit analyze-sentiment" title="Analyser sentiment" data-email="<?php echo htmlspecialchars($p['email'] ?? ''); ?>" data-name="<?php echo htmlspecialchars(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '')); ?>" data-event="<?php echo htmlspecialchars($ev['titre']); ?>" data-result="sentiment-<?php echo $p['id']; ?>"><i class="fas fa-smile"></i></button>
                                <form method="POST" action="admin.php?action=participants" style="display:inline" onsubmit="return confirm('Supprimer ce participant ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr id="sentiment-row-<?php echo $p['id']; ?>">
                            <td colspan="7"><div id="sentiment-<?php echo $p['id']; ?>"></div></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr><td colspan="7" style="text-align:center;padding:32px;color:rgba(255,255,255,0.4)">Aucun participant inscrit.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div id="toasts" style="position:fixed;top:20px;right:20px;z-index:99999;min-width:260px"></div>
<script src="./assets/js/features.js"></script>
<script>
// Sentiment analysis for participants
document.querySelectorAll('.analyze-sentiment').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var email = this.dataset.email || '';
        var name = this.dataset.name || '';
        var eventName = this.dataset.event || '';
        var resultId = this.dataset.result || '';
        var resultEl = document.getElementById(resultId);
        if (!resultEl) return;
        var text = prompt('Entrez le feedback de ' + name + ' a analyser:');
        if (text) {
            var fd = new FormData();
            fd.append('action', 'sentiment');
            fd.append('data', JSON.stringify({ text: text, participant_name: name, event_name: eventName }));
            resultEl.innerHTML = '<div style="padding:10px;color:rgba(255,255,255,0.5)">Analyse...</div>';
            fetch('ajax/ai_proxy.php', { method:'POST', body:fd })
                .then(function(r){return r.json()})
                .then(function(j){
                    if(j.error) throw new Error(j.error);
                    var d = j.data;
                    var c = {'positif':'#4CAF50','neutre':'#FFA726','negatif':'#ef5350'};
                    var color = c[d.sentiment] || '#90A4AE';
                    resultEl.innerHTML = '<div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(255,255,255,0.05);border-radius:8px;border-left:3px solid '+color+'">'+
                        '<span style="font-size:1.5rem">'+(d.emoji||'😐')+'</span>'+
                        '<div><div style="font-weight:700;color:'+color+';font-size:.8rem">'+(d.sentiment||'').toUpperCase()+' - '+(d.score||0)+'%</div>'+
                        '<div style="font-size:.75rem;color:rgba(255,255,255,0.5)">'+(d.resume||'')+'</div></div></div>';
                })
                .catch(function(e){ resultEl.innerHTML = '<span style="color:#ef5350">⚠ '+e.message+'</span>'; });
        }
    });
});
</script>
</body>
</html>
