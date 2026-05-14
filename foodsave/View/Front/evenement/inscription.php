<?php
if (!isset($ev) || !isset($slabels)) {
    header('Location: index.php?action=evenements');
    exit;
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$statutLabel = $slabels[$ev['statut']] ?? $ev['statut'];
$badgeClass = $sbadge[$ev['statut']] ?? 'badge-gray';
$cap = (int)$ev['capacite'];
$nb = isset($ev['nb_p']) ? (int)$ev['nb_p'] : 0;
$pct = $cap > 0 ? min(100, round(($nb / $cap) * 100)) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Inscription : <?php echo htmlspecialchars($ev['titre']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .navbar{position:relative;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:22px 52px;border-bottom:1px solid rgba(34,197,94,0.1);background:rgba(13,31,20,0.6);backdrop-filter:blur(16px)}
        .logo-wrap{display:flex;align-items:center;gap:10px;cursor:pointer}
        .logo-wrap img{height:44px}
        .logo-wrap span{font-weight:700;font-size:1.4rem;background:linear-gradient(135deg,#4caf50,#ff6b35);-webkit-background-clip:text;background-clip:text;color:transparent}
        .nav-links{display:flex;gap:32px;list-style:none}
        .nav-links a{text-decoration:none;font-size:14px;font-weight:500;color:rgba(255,255,255,0.65);transition:color 0.2s}
        .nav-links a.active{color:#4ade80}
        .nav-links a:hover{color:#fff}
        .nav-btns{display:flex;gap:10px}
        .btn-ghost{padding:8px 20px;border:1px solid rgba(74,222,128,0.35);border-radius:50px;background:transparent;color:#4ade80;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit}
        .btn-ghost:hover{background:rgba(74,222,128,0.08)}
        .btn-fill{padding:8px 22px;border:none;border-radius:50px;background:#16a34a;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;box-shadow:0 0 20px rgba(22,163,74,0.45)}
        .btn-fill:hover{background:#15803d;transform:translateY(-1px)}
        .badge-green{background:rgba(34,197,94,0.15);color:#4ade80;border:1px solid rgba(34,197,94,0.3)}
        .badge-orange{background:rgba(251,191,36,0.15);color:#fbbf24;border:1px solid rgba(251,191,36,0.3)}
        .badge-gray{background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.45);border:1px solid rgba(255,255,255,0.1)}
        .badge{padding:4px 12px;border-radius:50px;font-size:11px;font-weight:700;white-space:nowrap}

        .inscription-section{position:relative;z-index:5;max-width:720px;margin:2rem auto 3rem;padding:0 52px}

        .event-summary{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:24px;padding:1.5rem;margin-bottom:2rem;display:flex;align-items:center;gap:1.2rem;flex-wrap:wrap}
        .event-summary-icon{width:52px;height:52px;border-radius:50%;background:rgba(74,222,128,0.12);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#4ade80;flex-shrink:0}
        .event-summary-info{flex:1;min-width:200px}
        .event-summary-title{font-weight:700;color:#fff;font-size:1.05rem;margin-bottom:4px}
        .event-summary-meta{font-size:0.8rem;color:rgba(255,255,255,0.5);display:flex;gap:16px;flex-wrap:wrap}
        .event-summary-meta span{display:inline-flex;align-items:center;gap:5px}

        .form-card{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:24px;padding:2rem}
        .form-card h2{font-size:1.3rem;font-weight:700;color:#fff;margin-bottom:0.3rem}
        .form-card p.sub{color:rgba(255,255,255,0.45);font-size:0.85rem;margin-bottom:1.5rem}
        .form-group{margin-bottom:1.2rem}
        .form-group label{display:block;font-size:0.82rem;font-weight:600;color:rgba(255,255,255,0.6);margin-bottom:6px}
        .form-group label .required{color:#f87171}
        .form-control{width:100%;padding:12px 16px;background:rgba(255,255,255,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:12px;font-family:"DM Sans",sans-serif;font-size:0.9rem;color:#fff;transition:border-color 0.2s}
        .form-control:focus{outline:none;border-color:#4ade80;background:rgba(74,222,128,0.05)}
        .form-control::placeholder{color:rgba(255,255,255,0.25)}
        .form-error{color:#f87171;font-size:0.78rem;margin-top:6px;display:flex;align-items:center;gap:5px}
        .form-error i{font-size:0.7rem}
        .form-group.has-error .form-control{border-color:#f87171;background:rgba(239,68,68,0.05)}

        .btn-submit{width:100%;padding:14px 28px;background:#16a34a;color:#fff;border:none;border-radius:50px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;box-shadow:0 0 25px rgba(22,163,74,0.4);transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px}
        .btn-submit:hover{background:#15803d;transform:translateY(-2px);box-shadow:0 0 35px rgba(22,163,74,0.6)}
        .btn-submit:disabled{opacity:0.5;cursor:not-allowed;transform:none}

        .success-card{background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.2);border-radius:24px;padding:3rem 2rem;text-align:center}
        .success-card .icon{font-size:3.5rem;color:#4ade80;margin-bottom:1rem}
        .success-card h2{font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:0.5rem}
        .success-card p{color:rgba(255,255,255,0.6);margin-bottom:1.5rem;font-size:0.9rem}
        .success-card .btn-return{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#16a34a;color:#fff;border:none;border-radius:50px;font-weight:700;cursor:pointer;font-family:inherit;text-decoration:none;transition:all 0.2s}
        .success-card .btn-return:hover{background:#15803d;transform:translateY(-2px)}

        .footer{background:rgba(8,16,8,0.9);border-top:1px solid rgba(74,222,128,0.08);padding:3rem 52px 1rem;margin-top:3rem}
        .footer-bottom{text-align:center;padding-top:2rem;border-top:1px solid rgba(255,255,255,0.05);font-size:0.8rem;color:rgba(255,255,255,0.3)}

        @media (max-width:768px){
            .navbar{padding:22px 20px;flex-direction:column;gap:1rem}
            .nav-links{flex-wrap:wrap;justify-content:center}
            .inscription-section{padding:0 20px}
            .event-summary{flex-direction:column;text-align:center}
            .event-summary-meta{justify-content:center}
            .form-card{padding:1.5rem}
        }
    </style>
</head>
<body>

<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div><div class="glow-3"></div>
</div>

<nav class="navbar">
    <div class="logo-wrap" onclick="location.href='index.php?action=blog'">
        <img src="./assets/images/logo-foodsave.png" alt="FoodSave">
        <span>FoodSave</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php?action=blog">Accueil</a></li>
        <li><a href="index.php?action=evenements" class="active">Événements</a></li>
        <li><a href="admin.php?action=evenements">Admin</a></li>
    </ul>
    <div class="nav-btns">
        <?php if(isset($_SESSION['user_id'])): ?>
            <button class="btn-ghost" onclick="location.href='index.php?action=profile'">👤 <?php echo $_SESSION['user_prenom'] ?? 'Mon compte'; ?></button>
            <button class="btn-fill" onclick="location.href='index.php?action=logout'">Déconnexion</button>
        <?php else: ?>
            <button class="btn-ghost" onclick="location.href='index.php?action=login'">Connexion</button>
            <button class="btn-fill" onclick="location.href='index.php?action=register'">Inscription</button>
        <?php endif; ?>
    </div>
</nav>

<section class="inscription-section">
    <?php if($success): ?>
        <div class="success-card">
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <h2>Inscription confirmée !</h2>
            <p>Merci pour votre inscription à <strong><?php echo htmlspecialchars($ev['titre']); ?></strong>. Vous recevrez un email de confirmation avec les détails pratiques.</p>
            <a href="index.php?action=evenementDetail&id=<?php echo $ev['id']; ?>" class="btn-return"><i class="fas fa-arrow-left"></i> Retour à l'événement</a>
        </div>
    <?php else: ?>
        <div class="event-summary">
            <div class="event-summary-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="event-summary-info">
                <div class="event-summary-title"><?php echo htmlspecialchars($ev['titre']); ?></div>
                <div class="event-summary-meta">
                    <span><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($ev['date_event'])); ?></span>
                    <span><i class="far fa-clock"></i> <?php echo htmlspecialchars($ev['heure'] ?? ''); ?></span>
                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ev['lieu']); ?></span>
                    <span><span class="badge <?php echo $badgeClass; ?>"><?php echo $statutLabel; ?></span></span>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h2><i class="fas fa-pen"></i> Formulaire d'inscription</h2>
            <p class="sub">Remplissez vos coordonnées pour participer à cet événement</p>

            <form method="POST" action="index.php?action=evenementInscription&id=<?php echo $ev['id']; ?>" novalidate>
                <div class="form-group <?php echo isset($errors['prenom']) ? 'has-error' : ''; ?>">
                    <label>Prénom <span class="required">*</span></label>
                    <input type="text" name="prenom" id="prenom" class="form-control" placeholder="Votre prénom" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" data-validate="required|minlen:2|letters" required>
                    <div id="e-prenom" class="form-error" style="display:none"></div>
                    <?php if(isset($errors['prenom'])): ?>
                        <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errors['prenom']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group <?php echo isset($errors['nom']) ? 'has-error' : ''; ?>">
                    <label>Nom <span class="required">*</span></label>
                    <input type="text" name="nom" id="nom" class="form-control" placeholder="Votre nom" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" data-validate="required|minlen:2|letters" required>
                    <div id="e-nom" class="form-error" style="display:none"></div>
                    <?php if(isset($errors['nom'])): ?>
                        <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errors['nom']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="votre@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" data-validate="required|email" required>
                    <div id="e-email" class="form-error" style="display:none"></div>
                    <?php if(isset($errors['email'])): ?>
                        <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group <?php echo isset($errors['telephone']) ? 'has-error' : ''; ?>">
                    <label>Téléphone <span style="color:rgba(255,255,255,0.3)">(optionnel)</span></label>
                    <input type="tel" name="telephone" id="telephone" class="form-control" placeholder="+33 6 XX XX XX XX" value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>" data-validate="phone">
                    <div id="e-telephone" class="form-error" style="display:none"></div>
                    <?php if(isset($errors['telephone'])): ?>
                        <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errors['telephone']); ?></div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-check"></i> Confirmer l'inscription</button>
                <div style="text-align:center;margin-top:1rem">
                    <a href="index.php?action=evenementDetail&id=<?php echo $ev['id']; ?>" style="color:rgba(255,255,255,0.4);text-decoration:none;font-size:0.85rem"><i class="fas fa-arrow-left"></i> Retour à l'événement</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</section>

<div id="toasts" style="position:fixed;top:20px;right:20px;z-index:99999;min-width:260px"></div>

<footer class="footer">
    <div class="footer-bottom">
        <p>© 2025 FoodSave - Tous droits réservés</p>
    </div>
</footer>

<script src="./assets/js/features.js"></script>
<script src="./assets/js/validation_zalouni.js"></script>
</body>
</html>
