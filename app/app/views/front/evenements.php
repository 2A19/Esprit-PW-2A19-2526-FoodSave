<?php
require_once __DIR__ . '/../../controller/EvenementController.php';

$controller = new EvenementController();
$search    = trim($_GET['search'] ?? '');
$statut    = $_GET['statut'] ?? '';
$categorie = $_GET['categorie'] ?? '';

$rows = $controller->listEvents($search, $statut, $categorie);
$slabels  = ['upcoming' => 'A venir', 'ongoing' => 'En cours', 'past' => 'Termine'];
$sbadge   = ['upcoming' => 'b-green', 'ongoing' => 'b-orange', 'past' => 'b-gray'];
$currentUrl = 'evenements.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — Evenements</title>
<link rel="stylesheet" href="../../../public/css/style.css">
<style>
/* Timer front */
.timer-front { display:inline-flex;align-items:center;gap:3px;font-size:.72rem;margin-top:4px; }
.tf-unit { background:var(--g900);color:#fff;padding:2px 5px;border-radius:4px;font-weight:700;font-family:var(--fh); }
.tf-unit small { font-size:.5rem;opacity:.7; }
.tf-sep { color:var(--g500);font-weight:700; }
.timer-done { color:var(--green);font-weight:700;font-size:.75rem; }
/* QR sur carte */
.qr-front { display:none;margin-top:8px;text-align:center; }
/* Lang */
[data-lang-btn] { padding:3px 8px;border-radius:5px;border:1.5px solid var(--g300);background:#fff;font-size:.75rem;font-weight:600;cursor:pointer; }
[data-lang-btn].active-lang { background:var(--green);color:#fff;border-color:var(--green); }
/* TTS */
.tts-btn { padding:4px 10px;border-radius:6px;border:1.5px solid var(--g300);background:#fff;font-size:.75rem;cursor:pointer; }
</style>
</head>
<body style="background:var(--g100);display:flex;flex-direction:column;min-height:100vh">

<nav class="front-nav">
  <div class="fn-inner">
    <a href="accueil.php" class="fn-brand"><div class="bi">🌿</div><span><strong>Food</strong><em>Save</em></span></a>
    <div class="fn-links" style="display:flex;align-items:center;gap:10px">
      <a href="accueil.php">Accueil</a>
      <a href="evenements.php" class="on">Evenements</a>
      <!-- Traduction -->
      <button data-lang-btn="fr">🇫🇷</button>
      
      <button data-lang-btn="ar">🇹🇳</button>
      <!-- TTS page -->
      <button class="tts-btn" data-text="<?= htmlspecialchars('Page événements FoodSave. '.count($rows).' événements trouvés.') ?>">🔊</button>
      <a href="../back/evenements.php" class="btn btn-primary btn-sm">⚙ Admin</a>
    </div>
  </div>
</nav>

<section class="f-section" style="flex:1">
  <div class="f-container">
    <div class="sec-head" style="margin-bottom:22px">
      <div>
        <div class="sec-title" data-tr="Gestion des Evenements">📅 Tous les Evenements</div>
        <div class="sec-sub">Decouvrez nos actions et rejoignez-nous</div>
      </div>
    </div>

    <!-- Filtre (avec bouton — comme souhaité) -->
    <div class="filter-bar" style="margin-bottom:24px">
      <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
        <select name="statut">
          <option value="">Tous statuts</option>
          <option value="upcoming" <?= $statut==='upcoming'?'selected':'' ?>>A venir</option>
          <option value="ongoing"  <?= $statut==='ongoing' ?'selected':'' ?>>En cours</option>
          <option value="past"     <?= $statut==='past'    ?'selected':'' ?>>Termines</option>
        </select>
        <select name="categorie">
          <option value="">Toutes categories</option>
          <?php foreach (['Atelier','Conference','Hackathon','Social','Formation','Autre'] as $c): ?>
          <option value="<?= $c ?>" <?= $categorie===$c?'selected':'' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
        <a href="evenements.php" class="btn btn-outline btn-sm">Reset</a>
      </form>
    </div>

    <!-- Filtre instantané client -->
    <div style="margin-bottom:16px;display:flex;align-items:center;gap:10px">
      <input id="clientFilter" class="s-input" type="text" placeholder="⚡ Filtre instant dans les résultats..."
             style="max-width:300px">
      <span id="filterCount" style="font-size:.75rem;color:var(--g500)"></span>
    </div>

    <div class="ev-grid">
    <?php if (empty($rows)): ?>
      <div class="empty" style="grid-column:1/-1"><div class="empty-ic">📅</div><div class="empty-tt">Aucun evenement</div></div>
    <?php else: ?>
      <?php foreach ($rows as $ev):
        $pct    = $ev['capacite'] > 0 ? min(100, round($ev['nb_p']/$ev['capacite']*100)) : 0;
        $sLabel = $slabels[$ev['statut']] ?? $ev['statut'];
        $sBadge = $sbadge[$ev['statut']] ?? 'b-gray';
        $dateStr = $ev['date_event'].' '.substr($ev['heure'],0,5);
        $ttsText = htmlspecialchars($ev['titre'].'. '.$sLabel.'. Le '.date('d/m/Y',strtotime($ev['date_event'])).' à '.substr($ev['heure'],0,5).'. Lieu : '.$ev['lieu'].'. '.$ev['nb_p'].' inscrits sur '.$ev['capacite'].' places.');
      ?>
      <div class="ev-card" style="animation:fadeUp .4s ease both" data-filterable-card>
        <div class="ev-top <?= $ev['statut'] ?>"></div>
        <div class="ev-body">
          <div class="ev-head">
            <div class="ev-name"><?= htmlspecialchars($ev['titre']) ?></div>
            <span class="badge <?= $sBadge ?>"><?= $sLabel ?></span>
          </div>

          <div class="ev-meta">
            <div class="ev-meta-row"><span class="ev-icon">📅</span><?= date('d/m/Y', strtotime($ev['date_event'])) ?> — <?= substr($ev['heure'],0,5) ?></div>
            <div class="ev-meta-row"><span class="ev-icon">📍</span><?= htmlspecialchars($ev['lieu']) ?></div>
            <div class="ev-meta-row"><span class="ev-icon">👤</span><?= htmlspecialchars($ev['organisateur']) ?></div>
            <div class="ev-meta-row"><span class="ev-icon">🏷</span><span class="badge b-blue"><?= htmlspecialchars($ev['categorie']) ?></span></div>
          </div>

          <?php if (!empty($ev['description'])): ?>
          <p class="ev-desc"><?= htmlspecialchars(mb_substr($ev['description'],0,90)) ?>...</p>
          <?php endif; ?>

          <!-- Timer compte à rebours pour événements à venir -->
          <?php if ($ev['statut'] === 'upcoming'): ?>
          <div style="margin:8px 0">
            <span style="font-size:.7rem;color:var(--g500);font-weight:600">⏱ Commence dans :</span>
            <div class="timer-front" data-countdown="<?= $dateStr ?>"></div>
          </div>
          <?php endif; ?>

          <div class="ev-prog">
            <div class="progress-lbl"><span><?= $ev['nb_p'] ?>/<?= $ev['capacite'] ?></span><span><?= $pct ?>%</span></div>
            <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
          </div>

          <div class="ev-actions" style="display:flex;flex-wrap:wrap;gap:5px;margin-top:10px">
            <a href="ev_detail.php?id=<?= $ev['id'] ?>" class="btn btn-outline btn-sm">Details</a>

            <?php if ($ev['statut'] !== 'past' && $pct < 100): ?>
            <a href="inscription.php?id=<?= $ev['id'] ?>" class="btn btn-primary btn-sm">S'inscrire</a>
            <?php else: ?>
            <span class="badge b-gray" style="padding:6px 12px"><?= $pct>=100?'Complet':'Termine' ?></span>
            <?php endif; ?>

            <!-- QR Code public de l'événement -->
            <button class="btn btn-outline btn-sm" style="padding:5px 8px"
                    data-qr="<?= htmlspecialchars(implode('|',['FoodSave-Evenement','ID:'.$ev['id'],'Titre:'.$ev['titre'],'Categorie:'.$ev['categorie'],'Date:'.date('d/m/Y',strtotime($ev['date_event'])),'Heure:'.substr($ev['heure'],0,5),'Lieu:'.$ev['lieu'],'Organisateur:'.$ev['organisateur'],'Inscrits:'.$ev['nb_p'].'/'.$ev['capacite'],'Statut:'.($slabels[$ev['statut']]??$ev['statut'])])) ?>"
                    data-qr-target="qrF-<?= $ev['id'] ?>">📲 QR</button>

            <!-- Lecture vocale de la carte -->
            <button class="btn btn-outline btn-sm tts-btn" style="padding:5px 8px"
                    data-text="<?= $ttsText ?>">🔊</button>
          </div>

          <!-- QR container -->
          <div class="qr-front" id="qrF-<?= $ev['id'] ?>"></div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
    </div>
  </div>
</section>

<footer class="front-foot">
  <div class="ff-brand"><div style="width:28px;height:28px;background:var(--green);border-radius:6px;display:flex;align-items:center;justify-content:center">🌿</div><strong>Food</strong><em>Save</em></div>
  <p>© 2026 FoodSave — Equipe NextWave</p>
</footer>

<div class="toast-wrap" id="toasts"></div>
<script src="../../../public/js/validation.js"></script>
<script src="../../../public/js/features.js"></script>
<script>
// Adapter les timers pour le front (classes différentes)
document.querySelectorAll('.timer-front[data-countdown]').forEach(function(el) {
    var target = new Date(el.dataset.countdown).getTime();
    if (isNaN(target)) return;
    function tick() {
        var diff = target - Date.now();
        if (diff <= 0) { el.innerHTML = '<span class="timer-done">En cours!</span>'; return; }
        var d = Math.floor(diff/86400000);
        var h = Math.floor((diff%86400000)/3600000);
        var m = Math.floor((diff%3600000)/60000);
        var s = Math.floor((diff%60000)/1000);
        el.innerHTML =
            '<span class="tf-unit">'+d+'<small>j</small></span>:'+
            '<span class="tf-unit">'+String(h).padStart(2,'0')+'<small>h</small></span>:'+
            '<span class="tf-unit">'+String(m).padStart(2,'0')+'<small>m</small></span>:'+
            '<span class="tf-unit">'+String(s).padStart(2,'0')+'<small>s</small></span>';
    }
    tick(); setInterval(tick, 1000);
});

// QR géré par features.js initQRButtons()
</script>
</body>
</html>
