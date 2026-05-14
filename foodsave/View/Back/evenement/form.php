<?php
if(!isset($id) || !isset($ev) || !isset($errors)) {
    $id = null;
    $ev = null;
    $errors = [];
}
$categories = ['Atelier','Conference','Hackathon','Social','Formation','Autre'];
$statuses = ['upcoming'=>'A venir','ongoing'=>'En cours','past'=>'Termine'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Admin : <?php echo $id ? 'Modifier' : 'Ajouter'; ?> evenement</title>
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
        .form-card{background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:20px;padding:2rem;max-width:800px}
        .form-group{margin-bottom:1.5rem}
        .form-group label{display:block;font-size:13px;font-weight:600;color:rgba(255,255,255,0.6);margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em}
        .form-control{width:100%;padding:12px 16px;background:rgba(255,255,255,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:12px;font-family:"DM Sans",sans-serif;font-size:0.9rem;color:#fff;transition:border-color 0.2s}
        .form-control:focus{outline:none;border-color:rgba(74,222,128,0.5);background:rgba(74,222,128,0.05)}
        .form-control::placeholder{color:rgba(255,255,255,0.3)}
        .form-control[readonly]{opacity:0.6;cursor:not-allowed}
        select.form-control option{background:#0d1f14;color:#fff}
        textarea.form-control{min-height:120px;resize:vertical}
        .form-error{color:#f87171;font-size:12px;margin-top:4px;display:flex;align-items:center;gap:4px}
        .form-actions{display:flex;gap:12px;margin-top:2rem;padding-top:1.5rem;border-top:1px solid rgba(255,255,255,0.05)}
        .btn-submit,.btn-cancel{display:inline-flex;align-items:center;gap:8px;padding:10px 24px;border-radius:50px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;transition:all 0.2s;font-family:inherit;border:none}
        .btn-submit{background:#16a34a;color:#fff;box-shadow:0 0 20px rgba(22,163,74,0.35)}
        .btn-submit:hover{background:#15803d;transform:translateY(-2px)}
        .btn-cancel{background:rgba(255,255,255,0.05);color:rgba(255,255,255,0.6);border:1px solid rgba(255,255,255,0.1)}
        .btn-cancel:hover{background:rgba(255,255,255,0.1);color:#fff}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        @media (max-width:768px){
            .sidebar{width:80px}
            .sidebar-header .logo-area span,.sidebar-menu a span{display:none}
            .main-content{margin-left:80px}
            .form-row{grid-template-columns:1fr}
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
                    <a href="admin.php?action=evenements" class="nav-link active">Evenements</a>
                    <a href="admin.php?action=participants" class="nav-link">Participants</a>
                </div>
                <div class="user-actions">
                    <button class="login-btn login-outline"><i class="fas fa-user"></i> Profil</button>
                    <button class="login-btn login-primary"><i class="fas fa-sign-out-alt"></i> Deconnexion</button>
                </div>
            </div>
        </nav>
        <div class="content-header">
            <h1><i class="fas fa-<?php echo $id ? 'edit' : 'plus-circle'; ?>"></i> <?php echo $id ? 'Modifier' : 'Ajouter'; ?> un evenement</h1>
        </div>
        <div class="form-card">
            <form method="POST" action="admin.php?action=evenementForm<?php echo $id ? '&id='.$id : ''; ?>" novalidate>
                <?php if(!empty($errors)): ?>
                <div style="background:rgba(239,68,68,0.12);color:#f87171;padding:12px;border-radius:12px;margin-bottom:1.5rem;border:1px solid rgba(239,68,68,0.2)">
                    <i class="fas fa-exclamation-circle"></i> Veuillez corriger les erreurs ci-dessous.
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" name="titre" id="ev_titre" class="form-control" value="<?php echo htmlspecialchars($ev['titre'] ?? ''); ?>" placeholder="Titre de l'evenement" data-validate="required|minlen:3|maxlen:150">
                    <?php if(isset($errors['titre'])): ?><div class="form-error"><i class="fas fa-times-circle"></i> <?php echo $errors['titre']; ?></div><?php endif; ?>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Categorie</label>
                        <select name="categorie" id="ev_categorie" class="form-control" data-validate="required">
                            <option value="">Selectionner</option>
                            <?php foreach($categories as $c): ?>
                            <option value="<?php echo $c; ?>" <?php echo (($ev['categorie'] ?? '') === $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if(isset($errors['categorie'])): ?><div class="form-error"><i class="fas fa-times-circle"></i> <?php echo $errors['categorie']; ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Statut</label>
                        <select name="statut" id="statutSelect" class="form-control">
                            <?php foreach($statuses as $k => $v): ?>
                            <option value="<?php echo $k; ?>" <?php echo (($ev['statut'] ?? 'upcoming') === $k) ? 'selected' : ''; ?>><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if(isset($errors['statut'])): ?><div class="form-error"><i class="fas fa-times-circle"></i> <?php echo $errors['statut']; ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date_event" id="dateEvent" class="form-control" value="<?php echo htmlspecialchars($ev['date_event'] ?? ''); ?>" data-validate="date">
                        <?php if(isset($errors['date_event'])): ?><div class="form-error"><i class="fas fa-times-circle"></i> <?php echo $errors['date_event']; ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Heure</label>
                        <input type="time" name="heure" id="ev_heure" class="form-control" value="<?php echo htmlspecialchars($ev['heure'] ?? ''); ?>" data-validate="required|time">
                        <?php if(isset($errors['heure'])): ?><div class="form-error"><i class="fas fa-times-circle"></i> <?php echo $errors['heure']; ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Lieu</label>
                        <input type="text" name="lieu" id="ev_lieu" class="form-control" value="<?php echo htmlspecialchars($ev['lieu'] ?? ''); ?>" placeholder="Lieu de l'evenement" data-validate="required|minlen:2">
                        <?php if(isset($errors['lieu'])): ?><div class="form-error"><i class="fas fa-times-circle"></i> <?php echo $errors['lieu']; ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Organisateur</label>
                        <input type="text" name="organisateur" id="ev_organisateur" class="form-control" value="<?php echo htmlspecialchars($ev['organisateur'] ?? ''); ?>" placeholder="Nom de l'organisateur" data-validate="required|minlen:2">
                        <?php if(isset($errors['organisateur'])): ?><div class="form-error"><i class="fas fa-times-circle"></i> <?php echo $errors['organisateur']; ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Capacite</label>
                    <input type="number" name="capacite" id="ev_capacite" class="form-control" value="<?php echo htmlspecialchars($ev['capacite'] ?? ''); ?>" placeholder="Nombre de places" min="1" data-validate="required|number|min:1">
                    <?php if(isset($errors['capacite'])): ?><div class="form-error"><i class="fas fa-times-circle"></i> <?php echo $errors['capacite']; ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" placeholder="Description de l'evenement..."><?php echo htmlspecialchars($ev['description'] ?? ''); ?></textarea>
                    <?php if(isset($errors['description'])): ?><div class="form-error"><i class="fas fa-times-circle"></i> <?php echo $errors['description']; ?></div><?php endif; ?>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> <?php echo $id ? 'Enregistrer les modifications' : 'Creer l\'evenement'; ?></button>
                    <a href="admin.php?action=evenements" class="btn-cancel"><i class="fas fa-times"></i> Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function(){
    var statutSelect = document.getElementById('statutSelect');
    var dateInput = document.getElementById('dateEvent');
    function handleStatutChange(){
        if(statutSelect.value === 'ongoing'){
            if(!dateInput.value){
                dateInput.value = new Date().toISOString().split('T')[0];
            }
            dateInput.setAttribute('readonly', true);
        } else {
            dateInput.removeAttribute('readonly');
        }
    }
    statutSelect.addEventListener('change', handleStatutChange);
    handleStatutChange();
})();
</script>
<div id="toasts" style="position:fixed;top:20px;right:20px;z-index:99999;min-width:260px"></div>
<script src="./assets/js/features.js"></script>
<script src="./assets/js/validation_zalouni.js"></script>
</body>
</html>
