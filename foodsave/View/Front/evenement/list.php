<?php
if (!isset($rows) || !isset($slabels)) {
    header('Location: index.php?action=evenements');
    exit;
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Événements</title>
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
        .hero{position:relative;z-index:5;padding:3rem 52px 2rem;text-align:center}
        .hero h1{font-size:3rem;font-weight:700;color:#fff;margin-bottom:1rem}
        .hero h1 span{color:#4ade80}
        .hero p{color:rgba(255,255,255,0.5);max-width:600px;margin:0 auto}
        .events-section{position:relative;z-index:5;padding:0 52px 4rem}
        .filter-bar{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:2rem;padding:1rem 1.5rem;background:rgba(255,255,255,0.03);border:1px solid rgba(74,222,128,0.1);border-radius:20px}
        .filter-bar select,.filter-bar input{padding:10px 16px;background:rgba(255,255,255,0.05);border:1px solid rgba(74,222,128,0.15);border-radius:50px;font-family:"DM Sans",sans-serif;font-size:13px;color:#fff;min-width:160px}
        .filter-bar select option{background:#0d1f14;color:#fff}
        .filter-bar select:focus,.filter-bar input:focus{outline:none;border-color:#4ade80}
        .filter-bar input::placeholder{color:rgba(255,255,255,0.3)}
        .filter-bar button{padding:10px 22px;border:none;border-radius:50px;cursor:pointer;font-weight:600;font-family:inherit;font-size:13px;transition:all 0.2s}
        .btn-filter{background:#16a34a;color:#fff}
        .btn-filter:hover{background:#15803d;transform:translateY(-1px)}
        .btn-reset{background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.6);border:1px solid rgba(255,255,255,0.1)}
        .btn-reset:hover{background:rgba(255,255,255,0.1);color:#fff}
        .search-count{margin-bottom:1rem;font-size:0.85rem;color:rgba(255,255,255,0.4)}
        .events-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:1.5rem;max-width:1400px;margin:0 auto}
        .event-card{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:24px;overflow:hidden;transition:all 0.4s cubic-bezier(0.2,0.9,0.4,1.1);backdrop-filter:blur(8px);position:relative;animation:fadeInUp 0.6s cubic-bezier(0.2,0.9,0.4,1.1) both}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(40px)}to{opacity:1;transform:translateY(0)}}
        .event-card:hover{transform:translateY(-6px);background:rgba(74,222,128,0.06);border-color:rgba(74,222,128,0.3);box-shadow:0 20px 40px rgba(0,0,0,0.3)}
        .event-card-inner{padding:1.5rem}
        .event-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;gap:0.5rem}
        .event-title{font-size:1.15rem;font-weight:700;color:#fff;line-height:1.3;flex:1}
        .event-card:hover .event-title{color:#4ade80}
        .badge-green{background:rgba(34,197,94,0.15);color:#4ade80;border:1px solid rgba(34,197,94,0.3)}
        .badge-orange{background:rgba(251,191,36,0.15);color:#fbbf24;border:1px solid rgba(251,191,36,0.3)}
        .badge-gray{background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.45);border:1px solid rgba(255,255,255,0.1)}
        .badge{padding:4px 12px;border-radius:50px;font-size:11px;font-weight:700;white-space:nowrap}
        .event-meta{display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;margin-bottom:1rem}
        .meta-item{display:flex;align-items:center;gap:8px;font-size:0.8rem;color:rgba(255,255,255,0.55)}
        .meta-item i{width:16px;color:#4ade80;font-size:0.8rem}
        .event-category{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;background:rgba(74,222,128,0.12);color:#4ade80;border-radius:50px;font-size:0.7rem;font-weight:600;margin-bottom:0.8rem}
        .event-desc{color:rgba(255,255,255,0.5);font-size:0.82rem;line-height:1.5;margin-bottom:1rem}
        .countdown-wrap{background:rgba(74,222,128,0.06);border-radius:12px;padding:0.6rem 1rem;margin-bottom:0.8rem;display:flex;align-items:center;gap:10px}
        .countdown-wrap i{color:#fbbf24}
        .countdown-text{font-size:0.8rem;font-weight:600;color:#fbbf24}
        .countdown-timer{font-size:0.85rem;font-weight:700;color:#fff;margin-left:auto}
        .progress-wrap{background:rgba(255,255,255,0.06);border-radius:50px;height:8px;overflow:hidden;margin-bottom:0.8rem}
        .progress-bar{height:100%;border-radius:50px;background:linear-gradient(90deg,#4ade80,#16a34a);transition:width 0.6s ease}
        .progress-label{display:flex;justify-content:space-between;font-size:0.72rem;color:rgba(255,255,255,0.45);margin-bottom:0.4rem}
        .event-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,0.05)}
        .btn-sm{padding:8px 16px;border-radius:50px;border:none;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:all 0.2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
        .btn-primary{background:#16a34a;color:#fff}
        .btn-primary:hover{background:#15803d;transform:translateY(-1px)}
        .btn-outline{background:transparent;border:1px solid rgba(74,222,128,0.3);color:#4ade80}
        .btn-outline:hover{background:rgba(74,222,128,0.08)}
        .btn-tts{background:rgba(74,222,128,0.1);color:#4ade80}
        .btn-tts:hover{background:rgba(74,222,128,0.2)}
        .btn-qr{background:rgba(59,130,246,0.12);color:#60a5fa}
        .btn-qr:hover{background:rgba(59,130,246,0.2)}
        .btn-complet{background:rgba(239,68,68,0.12);color:#f87171;cursor:not-allowed;opacity:0.6}
        .qr-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px)}
        .qr-modal.open{display:flex}
        .qr-content{background:#0d1f14;border:1px solid rgba(74,222,128,0.2);border-radius:24px;padding:2rem;text-align:center;max-width:360px;width:90%}
        .qr-content h3{color:#fff;margin-bottom:1rem}
        .qr-content img{max-width:240px;border-radius:12px;margin-bottom:1rem}
        .qr-content button{padding:10px 24px;background:#16a34a;border:none;border-radius:50px;color:#fff;font-weight:600;cursor:pointer;font-family:inherit}
        .no-events{text-align:center;padding:60px 20px;grid-column:1/-1}
        .no-events i{font-size:3rem;color:rgba(255,255,255,0.2);margin-bottom:1rem}
        .no-events p{color:rgba(255,255,255,0.5)}
        .footer{position:relative;z-index:5;background:rgba(8,16,8,0.9);border-top:1px solid rgba(74,222,128,0.08);padding:3rem 52px 1rem;margin-top:3rem}
        .footer-bottom{text-align:center;padding-top:2rem;border-top:1px solid rgba(255,255,255,0.05);font-size:0.8rem;color:rgba(255,255,255,0.3)}
        @media (max-width:768px){
            .navbar{padding:22px 20px;flex-direction:column;gap:1rem}
            .nav-links{flex-wrap:wrap;justify-content:center}
            .hero{padding:2rem 20px}
            .hero h1{font-size:2rem}
            .events-section{padding:0 20px 3rem}
            .events-grid{grid-template-columns:1fr}
            .filter-bar{flex-direction:column}
            .filter-bar select,.filter-bar input{width:100%}
            .event-meta{grid-template-columns:1fr}
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

<section class="hero">
    <h1>📅 <span>Événements</span> FoodSave</h1>
    <p>Découvrez nos événements anti-gaspillage et inscrivez-vous</p>
</section>

<section class="events-section">
    <form class="filter-bar" method="GET" action="index.php" id="filterForm">
        <input type="hidden" name="action" value="evenements">
        <select name="statut">
            <option value="">Tous les statuts</option>
            <option value="upcoming" <?php echo $statut === 'upcoming' ? 'selected' : ''; ?>>À venir</option>
            <option value="ongoing" <?php echo $statut === 'ongoing' ? 'selected' : ''; ?>>En cours</option>
            <option value="past" <?php echo $statut === 'past' ? 'selected' : ''; ?>>Terminé</option>
        </select>
        <select name="categorie">
            <option value="">Toutes les catégories</option>
            <?php
            $categories = ['Atelier', 'Conférence', 'Collecte', 'Formation', 'Dégustation', 'Autre'];
            foreach ($categories as $cat):
            ?>
                <option value="<?php echo $cat; ?>" <?php echo $categorie === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="search" placeholder="🔍 Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filtrer</button>
        <a href="index.php?action=evenements" class="btn-reset" style="padding:10px 22px;border-radius:50px;cursor:pointer;font-weight:600;font-family:inherit;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.6);border:1px solid rgba(255,255,255,0.1)"><i class="fas fa-times"></i> Réinitialiser</a>
    </form>

    <div class="search-count" id="searchCount">
        📊 <?php echo count($rows); ?> événement(s) trouvé(s)
    </div>

    <div class="events-grid" id="eventsGrid">
        <?php if(empty($rows)): ?>
            <div class="no-events">
                <i class="fas fa-calendar-times"></i>
                <p>Aucun événement trouvé</p>
                <a href="index.php?action=evenements" style="color:#4ade80;text-decoration:none;font-weight:600;margin-top:1rem;display:inline-block">Voir tous les événements</a>
            </div>
        <?php else: ?>
            <?php foreach($rows as $index => $ev):
                $statutLabel = $slabels[$ev['statut']] ?? $ev['statut'];
                $badgeClass = $sbadge[$ev['statut']] ?? 'badge-gray';
                $cap = (int)$ev['capacite'];
                $nb = isset($ev['nb_p']) ? (int)$ev['nb_p'] : 0;
                $pct = $cap > 0 ? min(100, round(($nb / $cap) * 100)) : 0;
                $isFull = $nb >= $cap;
                $isPast = $ev['statut'] === 'past';
                $eventDate = strtotime($ev['date_event']);
                $now = time();
                $diff = $eventDate - $now;
                $isUpcoming = $diff > 0 && $ev['statut'] === 'upcoming';
                $descExcerpt = htmlspecialchars(substr(strip_tags($ev['description'] ?? ''), 0, 120));
                if (strlen(strip_tags($ev['description'] ?? '')) > 120) $descExcerpt .= '...';
            ?>
            <div class="event-card" data-titre="<?php echo strtolower(htmlspecialchars($ev['titre'])); ?>" style="animation-delay:<?php echo $index * 0.05; ?>s">
                <div class="event-card-inner">
                    <div class="event-header">
                        <h3 class="event-title"><?php echo htmlspecialchars($ev['titre']); ?></h3>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $statutLabel; ?></span>
                    </div>
                    <span class="event-category"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($ev['categorie']); ?></span>
                    <div class="event-meta">
                        <span class="meta-item"><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', $eventDate); ?></span>
                        <span class="meta-item"><i class="far fa-clock"></i> <?php echo htmlspecialchars($ev['heure'] ?? ''); ?></span>
                        <span class="meta-item"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ev['lieu']); ?></span>
                        <span class="meta-item"><i class="fas fa-user"></i> <?php echo htmlspecialchars($ev['organisateur']); ?></span>
                    </div>
                    <?php if(!empty($descExcerpt)): ?>
                        <p class="event-desc"><?php echo $descExcerpt; ?></p>
                    <?php endif; ?>
                    <?php if($isUpcoming): ?>
                    <div class="countdown-wrap">
                        <i class="fas fa-hourglass-half"></i>
                        <span class="countdown-text">Temps restant :</span>
                        <span class="countdown-timer" data-date="<?php echo $ev['date_event']; ?>T<?php echo $ev['heure'] ?? '00:00'; ?>">--</span>
                    </div>
                    <?php endif; ?>
                    <div class="progress-label">
                        <span><i class="fas fa-users"></i> <?php echo $nb; ?>/<?php echo $cap; ?> inscrits</span>
                        <span><?php echo $pct; ?>%</span>
                    </div>
                    <div class="progress-wrap">
                        <div class="progress-bar" style="width:<?php echo $pct; ?>%"></div>
                    </div>
                    <div class="event-actions">
                        <a href="index.php?action=evenementDetail&id=<?php echo $ev['id']; ?>" class="btn-sm btn-outline"><i class="fas fa-info-circle"></i> Détail</a>
                        <?php if($isPast): ?>
                            <span class="btn-sm btn-complet"><i class="fas fa-check-circle"></i> Terminé</span>
                        <?php elseif($isFull): ?>
                            <span class="btn-sm btn-complet"><i class="fas fa-times-circle"></i> Complet</span>
                        <?php else: ?>
                            <a href="index.php?action=evenementInscription&id=<?php echo $ev['id']; ?>" class="btn-sm btn-primary"><i class="fas fa-sign-in-alt"></i> S'inscrire</a>
                        <?php endif; ?>
                        <button class="btn-sm btn-qr" onclick="showQR(<?php echo $ev['id']; ?>, '<?php echo htmlspecialchars(addslashes($ev['titre'])); ?>')"><i class="fas fa-qrcode"></i></button>
                        <button class="btn-sm btn-tts" onclick="speakEvent(<?php echo $ev['id']; ?>)"><i class="fas fa-volume-up"></i></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<div class="qr-modal" id="qrModal">
    <div class="qr-content">
        <h3 id="qrTitle">QR Code</h3>
        <div id="qrCodeContainer"></div>
        <button onclick="closeQR()">Fermer</button>
    </div>
</div>

<div id="toasts" style="position:fixed;top:20px;right:20px;z-index:99999;min-width:260px"></div>

<footer class="footer">
    <div class="footer-bottom">
        <p>© 2025 FoodSave - Tous droits réservés</p>
    </div>
</footer>

<script src="./assets/js/features.js"></script>
<script>
const filterInput = document.querySelector('.filter-bar input[name="search"]');
const cards = document.querySelectorAll('.event-card');
const searchCount = document.getElementById('searchCount');
const grid = document.getElementById('eventsGrid');

function filterCards() {
    const term = filterInput.value.toLowerCase().trim();
    let visible = 0;
    cards.forEach(c => {
        const title = c.getAttribute('data-titre') || '';
        if (term === '' || title.includes(term)) {
            c.style.display = '';
            visible++;
        } else {
            c.style.display = 'none';
        }
    });
    if (searchCount) {
        if (term === '') {
            searchCount.innerHTML = '📊 ' + visible + ' événement(s) trouvé(s)';
        } else {
            searchCount.innerHTML = '🔍 ' + visible + ' résultat(s) pour "' + term + '"';
        }
    }
}

if (filterInput) {
    filterInput.addEventListener('input', filterCards);
    filterInput.addEventListener('keyup', filterCards);
}

function updateCountdowns() {
    document.querySelectorAll('.countdown-timer').forEach(el => {
        const target = new Date(el.getAttribute('data-date')).getTime();
        const now = new Date().getTime();
        const diff = target - now;
        if (diff <= 0) { el.textContent = 'Terminé'; return; }
        const d = Math.floor(diff / (1000*60*60*24));
        const h = Math.floor((diff % (1000*60*60*24)) / (1000*60*60));
        const m = Math.floor((diff % (1000*60*60)) / (1000*60));
        const s = Math.floor((diff % (1000*60)) / 1000);
        el.textContent = d + 'j ' + h + 'h ' + m + 'm ' + s + 's';
    });
}
setInterval(updateCountdowns, 1000);
updateCountdowns();

function showQR(id, title) {
    document.getElementById('qrTitle').textContent = 'QR Code - ' + title;
    const container = document.getElementById('qrCodeContainer');
    const url = window.location.origin + '/integrationWebF/foodsave/index.php?action=evenementDetail&id=' + id;
    container.innerHTML = '<p style="color:rgba(255,255,255,0.5);margin-bottom:1rem;font-size:0.85rem">Scannez pour voir l\'événement</p><div style="background:#fff;display:inline-block;padding:16px;border-radius:12px"><img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(url) + '" alt="QR Code"></div>';
    document.getElementById('qrModal').classList.add('open');
}

function closeQR() {
    document.getElementById('qrModal').classList.remove('open');
}

document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) closeQR();
});

function speakEvent(id) {
    const card = document.querySelector('.event-card[data-titre]');
    if (!card) return;
    const title = card.querySelector('.event-title')?.textContent || '';
    const meta = card.querySelector('.event-meta')?.textContent?.replace(/\s+/g, ' ') || '';
    const desc = card.querySelector('.event-desc')?.textContent || '';
    const text = title + '. ' + meta + '. ' + desc;
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'fr-FR';
        u.rate = 0.85;
        window.speechSynthesis.speak(u);
    }
}
</script>

</body>
</html>
