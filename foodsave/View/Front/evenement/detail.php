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
$nb = isset($nbParticipants) ? (int)$nbParticipants : (isset($ev['nb_p']) ? (int)$ev['nb_p'] : 0);
$pct = $cap > 0 ? min(100, round(($nb / $cap) * 100)) : 0;
$isFull = $nb >= $cap;
$isPast = $ev['statut'] === 'past';
$eventDate = strtotime($ev['date_event']);
$now = time();
$diff = $eventDate - $now;
$isUpcoming = $diff > 0 && $ev['statut'] === 'upcoming';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - <?php echo htmlspecialchars($ev['titre']); ?></title>
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

        .breadcrumb{position:relative;z-index:5;max-width:1200px;margin:1.5rem auto 0;padding:0 52px;font-size:0.85rem;color:rgba(255,255,255,0.4)}
        .breadcrumb a{color:rgba(255,255,255,0.4);text-decoration:none}
        .breadcrumb a:hover{color:#4ade80}
        .breadcrumb span{color:rgba(255,255,255,0.6)}

        .detail-section{position:relative;z-index:5;max-width:1200px;margin:1.5rem auto 3rem;padding:0 52px;display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start}
        .detail-left{min-width:0}
        .detail-right{position:sticky;top:2rem}

        .flash{margin-bottom:1.5rem;padding:14px 20px;border-radius:16px;display:flex;align-items:center;gap:12px;font-size:0.9rem;font-weight:500}
        .flash-success{background:rgba(34,197,94,0.12);color:#4ade80;border:1px solid rgba(34,197,94,0.2)}
        .flash-error{background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.2)}

        .detail-card{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:24px;padding:1.5rem;margin-bottom:1.5rem}
        .detail-card h2{font-size:1.1rem;font-weight:700;color:#fff;margin-bottom:1rem;display:flex;align-items:center;gap:8px}
        .detail-card h2 i{color:#4ade80}
        .event-title-lg{font-size:1.8rem;font-weight:700;color:#fff;margin-bottom:0.5rem;line-height:1.3}
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        .info-item{display:flex;align-items:flex-start;gap:10px}
        .info-item i{color:#4ade80;margin-top:2px;width:18px}
        .info-item-label{font-size:0.72rem;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:2px}
        .info-item-value{font-size:0.9rem;color:#fff;font-weight:500}
        .desc-content{color:rgba(255,255,255,0.7);line-height:1.7;font-size:0.92rem}

        .progress-box{background:rgba(74,222,128,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:16px;padding:1.2rem;margin-top:1.5rem}
        .progress-box-title{font-size:0.85rem;font-weight:600;color:#fff;margin-bottom:0.8rem}
        .progress-wrap{background:rgba(255,255,255,0.06);border-radius:50px;height:10px;overflow:hidden;margin-bottom:0.6rem}
        .progress-bar{height:100%;border-radius:50px;background:linear-gradient(90deg,#4ade80,#16a34a);transition:width 0.6s ease}
        .progress-stats{display:flex;justify-content:space-between;font-size:0.8rem;color:rgba(255,255,255,0.5)}
        .progress-stats strong{color:#fff}

        .sidebar-card{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:24px;padding:1.5rem;text-align:center;backdrop-filter:blur(8px)}
        .sidebar-icon{width:64px;height:64px;border-radius:50%;margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;font-size:1.8rem}
        .sidebar-icon.upcoming{background:rgba(34,197,94,0.15);color:#4ade80}
        .sidebar-icon.ongoing{background:rgba(251,191,36,0.15);color:#fbbf24}
        .sidebar-icon.past{background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.4)}
        .sidebar-status{font-size:0.95rem;font-weight:700;color:#fff;margin-bottom:0.5rem}
        .sidebar-desc{font-size:0.8rem;color:rgba(255,255,255,0.5);margin-bottom:1.5rem}
        .btn-cta{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 32px;border:none;border-radius:50px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;text-decoration:none;transition:all 0.2s;width:100%}
        .btn-cta-primary{background:#16a34a;color:#fff;box-shadow:0 0 25px rgba(22,163,74,0.4)}
        .btn-cta-primary:hover{background:#15803d;transform:translateY(-2px);box-shadow:0 0 35px rgba(22,163,74,0.6)}
        .btn-cta-disabled{background:rgba(239,68,68,0.12);color:#f87171;cursor:not-allowed}
        .btn-cta-secondary{background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.5);cursor:not-allowed}
        .btn-back{display:inline-flex;align-items:center;gap:6px;color:#4ade80;text-decoration:none;font-size:0.9rem;font-weight:500;margin-top:1rem;transition:gap 0.2s}
        .btn-back:hover{gap:10px}
        .countdown-side{background:rgba(251,191,36,0.08);border-radius:12px;padding:0.8rem;margin-bottom:1.2rem}
        .countdown-side .c-label{font-size:0.72rem;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:0.06em}
        .countdown-side .c-value{font-size:1.2rem;font-weight:700;color:#fbbf24;margin-top:4px}

        .footer{background:rgba(8,16,8,0.9);border-top:1px solid rgba(74,222,128,0.08);padding:3rem 52px 1rem;margin-top:3rem}
        .footer-bottom{text-align:center;padding-top:2rem;border-top:1px solid rgba(255,255,255,0.05);font-size:0.8rem;color:rgba(255,255,255,0.3)}

        @media (max-width:768px){
            .navbar{padding:22px 20px;flex-direction:column;gap:1rem}
            .nav-links{flex-wrap:wrap;justify-content:center}
            .breadcrumb{padding:0 20px}
            .detail-section{padding:0 20px;grid-template-columns:1fr}
            .detail-right{position:static}
            .event-title-lg{font-size:1.4rem}
            .info-grid{grid-template-columns:1fr}
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

<div class="breadcrumb">
    <a href="index.php?action=blog">Accueil</a> &gt;
    <a href="index.php?action=evenements">Événements</a> &gt;
    <span><?php echo htmlspecialchars($ev['titre']); ?></span>
</div>

<section class="detail-section">
    <div class="detail-left">
        <?php if(isset($flash) && $flash): ?>
            <div class="flash flash-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($flash['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="detail-card">
            <span class="badge <?php echo $badgeClass; ?>" style="margin-bottom:0.8rem;display:inline-block"><?php echo $statutLabel; ?></span>
            <h1 class="event-title-lg"><?php echo htmlspecialchars($ev['titre']); ?></h1>
        </div>

        <div class="detail-card">
            <h2><i class="fas fa-info-circle"></i> Informations</h2>
            <div class="info-grid">
                <div class="info-item">
                    <i class="far fa-calendar-alt"></i>
                    <div>
                        <div class="info-item-label">Date</div>
                        <div class="info-item-value"><?php echo date('d/m/Y', $eventDate); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <i class="far fa-clock"></i>
                    <div>
                        <div class="info-item-label">Heure</div>
                        <div class="info-item-value"><?php echo htmlspecialchars($ev['heure'] ?? ''); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <div class="info-item-label">Lieu</div>
                        <div class="info-item-value"><?php echo htmlspecialchars($ev['lieu']); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-user"></i>
                    <div>
                        <div class="info-item-label">Organisateur</div>
                        <div class="info-item-value"><?php echo htmlspecialchars($ev['organisateur']); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-tag"></i>
                    <div>
                        <div class="info-item-label">Catégorie</div>
                        <div class="info-item-value"><?php echo htmlspecialchars($ev['categorie']); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-flag"></i>
                    <div>
                        <div class="info-item-label">Statut</div>
                        <div class="info-item-value"><?php echo $statutLabel; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <h2><i class="fas fa-align-left"></i> Description</h2>
            <div class="desc-content">
                <?php echo nl2br(htmlspecialchars($ev['description'] ?? 'Aucune description disponible.')); ?>
            </div>
        </div>

        <div class="detail-card">
            <h2><i class="fas fa-users"></i> Inscriptions</h2>
            <div class="progress-box">
                <div class="progress-box-title">Places disponibles</div>
                <div class="progress-wrap">
                    <div class="progress-bar" style="width:<?php echo $pct; ?>%"></div>
                </div>
                <div class="progress-stats">
                    <span><strong><?php echo $nb; ?></strong> inscrit(s) / <strong><?php echo $cap; ?></strong> place(s)</span>
                    <span><?php echo $pct; ?>% rempli</span>
                </div>
            </div>
        </div>
    </div>

    <div class="detail-right">
        <div class="sidebar-card">
            <div class="sidebar-icon <?php echo $ev['statut']; ?>">
                <?php if($ev['statut'] === 'upcoming'): ?><i class="fas fa-calendar-check"></i>
                <?php elseif($ev['statut'] === 'ongoing'): ?><i class="fas fa-play-circle"></i>
                <?php else: ?><i class="fas fa-check-double"></i>
                <?php endif; ?>
            </div>
            <div class="sidebar-status">
                <?php if($isUpcoming): ?>Événement à venir
                <?php elseif($ev['statut'] === 'ongoing'): ?>En cours actuellement
                <?php else: ?>Événement terminé
                <?php endif; ?>
            </div>
            <div class="sidebar-desc">
                <?php if($isUpcoming): ?>Ne manquez pas cet événement ! Inscrivez-vous dès maintenant.
                <?php elseif($ev['statut'] === 'ongoing'): ?>Cet événement a déjà commencé.
                <?php else: ?>Merci d'avoir participé à cet événement.
                <?php endif; ?>
            </div>
            <?php if($isUpcoming): ?>
                <div class="countdown-side" id="sideCountdown">
                    <div class="c-label">Temps restant</div>
                    <div class="c-value" data-date="<?php echo $ev['date_event']; ?>T<?php echo $ev['heure'] ?? '00:00'; ?>">--</div>
                </div>
            <?php endif; ?>
            <?php if($isPast): ?>
                <div class="btn-cta btn-cta-secondary"><i class="fas fa-check-circle"></i> Événement terminé</div>
            <?php elseif($isFull): ?>
                <div class="btn-cta btn-cta-disabled"><i class="fas fa-times-circle"></i> Complet</div>
            <?php else: ?>
                <a href="index.php?action=evenementInscription&id=<?php echo $ev['id']; ?>" class="btn-cta btn-cta-primary"><i class="fas fa-sign-in-alt"></i> S'inscrire</a>
            <?php endif; ?>
            <a href="index.php?action=evenements" class="btn-back"><i class="fas fa-arrow-left"></i> Retour aux événements</a>
        </div>
    </div>
</section>

<div id="toasts" style="position:fixed;top:20px;right:20px;z-index:99999;min-width:260px"></div>

<footer class="footer">
    <div class="footer-bottom">
        <p>© 2025 FoodSave - Tous droits réservés</p>
    </div>
</footer>

<script src="./assets/js/features.js"></script>
<script>
function updateSideCountdown() {
    const el = document.querySelector('#sideCountdown .c-value');
    if (!el) return;
    const target = new Date(el.getAttribute('data-date')).getTime();
    const now = new Date().getTime();
    const diff = target - now;
    if (diff <= 0) { el.textContent = 'Commencé !'; return; }
    const d = Math.floor(diff / (1000*60*60*24));
    const h = Math.floor((diff % (1000*60*60*24)) / (1000*60*60));
    const m = Math.floor((diff % (1000*60*60)) / (1000*60));
    const s = Math.floor((diff % (1000*60)) / 1000);
    el.textContent = d + 'j ' + h + 'h ' + m + 'm ' + s + 's';
}
setInterval(updateSideCountdown, 1000);
updateSideCountdown();
</script>

</body>
</html>
