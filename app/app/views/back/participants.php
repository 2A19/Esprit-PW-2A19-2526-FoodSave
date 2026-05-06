<?php
session_start();
require_once __DIR__ . '/../../controller/ParticipantController.php';

$controller = new ParticipantController();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $controller->delete((int) $_POST['id']);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Participant supprimé.'];
    header('Location: participants.php');
    exit;
}

$search   = trim($_GET['search'] ?? '');
$statut   = $_GET['statut'] ?? '';
$evFilter = (int)($_GET['ev'] ?? 0);
$sort     = $_GET['sort'] ?? 'date_inscription';
$dir      = ($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$allowedSort = ['nom','prenom','email','statut','date_inscription','ev_titre'];
if (!in_array($sort, $allowedSort)) $sort = 'date_inscription';

$rows       = $controller->listParticipants($search, $statut, $evFilter, $sort, $dir);
$stats      = $controller->getStats();
$evenements = $controller->getEventList();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FoodSave — Gestion Participants</title>
<link rel="stylesheet" href="../../../public/css/style.css">
<style>
[data-lang-btn] { padding:4px 10px; border-radius:6px; border:1.5px solid var(--g300); background:#fff; font-size:.78rem; font-weight:600; cursor:pointer; transition:all .2s; }
[data-lang-btn].active-lang { background:var(--green); color:#fff; border-color:var(--green); }
.timer-wrap { display:inline-flex; align-items:center; gap:4px; font-family:var(--fh); font-size:.75rem; }
.timer-unit { background:var(--g900); color:#fff; padding:2px 5px; border-radius:4px; font-weight:700; display:inline-flex; flex-direction:column; align-items:center; }
.timer-unit small { font-size:.55rem; opacity:.7; font-weight:400; }
.timer-sep { color:var(--g500); font-weight:700; }
.timer-done { color:var(--green); font-weight:700; font-size:.8rem; }
.qr-container { display:none; padding:4px; text-align:center; background:#f8f9fa; border-radius:10px; margin-top:6px; border:1px solid #e0e0e0; }
.ai-panel { background:#fff; border:1px solid var(--g200); border-radius:12px; padding:18px; margin-bottom:20px; }
.ai-panel-title { font-family:var(--fh); font-size:.92rem; font-weight:800; color:var(--g900); margin-bottom:12px; display:flex; align-items:center; gap:8px; }
.ai-loading { display:flex; align-items:center; gap:8px; color:var(--g500); font-size:.85rem; }
.spinner { width:16px; height:16px; border:2px solid var(--g300); border-top-color:var(--green); border-radius:50%; animation:spin .7s linear infinite; display:inline-block; }
@keyframes spin { to { transform:rotate(360deg); } }
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; display:none; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:#fff; border-radius:16px; padding:28px; width:min(460px,94vw); box-shadow:0 20px 60px rgba(0,0,0,.25); }
.modal-title { font-family:var(--fh); font-size:1.05rem; font-weight:800; margin-bottom:18px; }
.modal-close { float:right; background:none; border:none; font-size:1.3rem; cursor:pointer; color:var(--g500); }
.form-group { margin-bottom:14px; }
.form-group label { display:block; font-size:.78rem; font-weight:600; color:var(--g700); margin-bottom:5px; }
.form-group input, .form-group textarea { width:100%; padding:9px 12px; border:1.5px solid var(--g300); border-radius:8px; font-family:var(--fb); font-size:.85rem; box-sizing:border-box; }
.form-group textarea { min-height:90px; resize:vertical; }
.gif-zone { text-align:center; padding:8px 0; }
</style>
</head>
<body class="back-wrap">

<!-- SIDEBAR -->
<aside class="sidebar" id="sb">
  <div class="sb-brand"><div class="sb-icon">🌿</div><div class="sb-name"><span>Food</span><em>Save</em><small>Admin</small></div></div>
  <nav class="sb-nav">
    <div class="nav-lbl" data-tr="Gestion">Gestion</div>
    <a href="evenements.php"   class="nav-a">📅 <span data-tr="Evenements">Evenements</span></a>
    <a href="participants.php" class="nav-a active">👥 <span data-tr="Participants">Participants</span></a>
    <a href="statistiques.php" class="nav-a">📊 <span data-tr="Statistiques">Statistiques</span></a>
    <div class="nav-lbl" style="margin-top:10px" data-tr="Acces rapide">Acces rapide</div>
    <a href="../front/accueil.php" class="nav-a">🌐 <span data-tr="Voir le site">Voir le site</span></a>
  </nav>
  <div class="sb-footer">
    <div class="user-card"><div class="u-av">AD</div>
      <div><div class="u-name" data-tr="Administrateur">Administrateur</div><div class="u-role">BackOffice</div></div>
    </div>
  </div>
</aside>

<!-- TOPBAR -->
<div class="main-wrap">
  <header class="topbar">
    <button class="ic-btn" onclick="document.getElementById('sb').classList.toggle('open')">☰</button>
    <div class="tb-title">🌿 FoodSave — BackOffice</div>
    <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
      <!-- Traduction FR / AR uniquement -->
      <button data-lang-btn="fr" title="Français">🇫🇷</button>
      <button data-lang-btn="ar" title="عربي">🇹🇳</button>
      <!-- Export PDF -->
      <a href="export_pdf.php?type=participants<?= $search ? '&search='.urlencode($search) : '' ?><?= $statut ? '&statut='.urlencode($statut) : '' ?><?= $evFilter ? '&ev='.$evFilter : '' ?>"
         class="btn btn-sm" style="background:linear-gradient(135deg,#e53935,#c62828);color:#fff">📄 PDF</a>
      <button class="btn btn-outline btn-sm" onclick="pushNotif('FoodSave','Notifications activées !')">🔔</button>
      <a href="statistiques.php" class="btn btn-outline btn-sm">📊 Stats</a>
      <a href="../front/accueil.php" class="btn btn-outline btn-sm">🌐 Front</a>
    </div>
  </header>

  <div class="content">

    <!-- Flash -->
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
      <?= htmlspecialchars($flash['msg']) ?>
      <button class="alert-close" onclick="this.parentElement.remove()">✕</button>
    </div>
    <?php endif; ?>

    <!-- Page header -->
    <div class="page-strip">
      <div>
        <div class="pg-title" data-tr="Gestion des Participants">👥 Gestion des Participants</div>
        <div class="pg-sub">CRUD · Filtre · Tri · QR · SMS · IA</div>
      </div>
      <a href="p_form.php" class="btn btn-primary">＋ <span data-tr="Nouveau participant">Nouveau participant</span></a>
    </div>

    <!-- KPIs -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon si-green">👥</div>
        <div><div class="stat-value"><?= $stats['total'] ?></div><div class="stat-label" data-tr="Total">Total</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-green">✔</div>
        <div><div class="stat-value"><?= $stats['confirmed'] ?></div><div class="stat-label" data-tr="Confirmes">Confirmes</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-orange">⏳</div>
        <div><div class="stat-value"><?= $stats['pending'] ?></div><div class="stat-label" data-tr="En attente">En attente</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-gray">✕</div>
        <div><div class="stat-value"><?= $stats['cancelled'] ?></div><div class="stat-label" data-tr="Annules">Annules</div></div>
      </div>
    </div>

    <!-- IA : Recommandations -->
    <div class="ai-panel">
      <div class="ai-panel-title">🤖 <span data-tr="Recommandations IA">Recommandations IA</span>
        <button class="btn btn-outline btn-sm" onclick="loadRecommendations()" style="margin-left:auto" data-tr="Analyser">Analyser</button>
      </div>
      <div id="aiReco" style="color:var(--g500);font-size:.83rem">Cliquez sur « Analyser » pour obtenir des recommandations basées sur les données actuelles.</div>
    </div>

    <!-- Filtres — seulement Filtre instant (liveSearch supprimé) -->
    <div class="filter-bar">
      <div style="display:flex;gap:10px;flex-wrap:wrap;flex:1;align-items:center">

        <!-- FILTRE INSTANT côté client uniquement -->
        <input id="clientFilter" class="s-input" type="text"
               placeholder="⚡ Filtre instant..."
               style="min-width:220px">
        <span id="filterCount" style="font-size:.75rem;color:var(--g500)"></span>

        <!-- Filtre statut + événement (avec rechargement serveur) -->
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <select name="statut" onchange="this.form.submit()">
            <option value="" data-tr="Tous statuts">Tous statuts</option>
            <option value="confirmed" <?= $statut==='confirmed'?'selected':'' ?>>Confirmes</option>
            <option value="pending"   <?= $statut==='pending'  ?'selected':'' ?>>En attente</option>
            <option value="cancelled" <?= $statut==='cancelled'?'selected':'' ?>>Annules</option>
          </select>
          <select name="ev" onchange="this.form.submit()">
            <option value="" data-tr="Tous evenements">Tous evenements</option>
            <?php foreach ($evenements as $e): ?>
            <option value="<?= $e['id'] ?>" <?= $evFilter===(int)$e['id']?'selected':'' ?>><?= htmlspecialchars($e['titre']) ?></option>
            <?php endforeach; ?>
          </select>
          <a href="participants.php" class="btn btn-outline btn-sm" data-tr="Reset">Reset</a>
        </form>

        <button class="btn btn-outline btn-sm tts-btn"
                data-text="<?= htmlspecialchars('Liste des participants. Total : '.$stats['total'].'. Confirmés : '.$stats['confirmed'].'. En attente : '.$stats['pending'].'. Annulés : '.$stats['cancelled'].'.') ?>">
          🔊 Lire
        </button>

      </div>
    </div>

    <!-- Tableau -->
    <div class="card">
      <div class="card-body" style="padding:0">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th data-sort="prenom">Participant ↕</th>
                <th data-sort="email">Email ↕</th>
                <th>Téléphone</th>
                <th data-sort="ev_titre">Evénement ↕</th>
                <th data-sort="statut">Statut ↕</th>
                <th data-sort="date_inscription">Inscrit le ↕</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="8"><div class="empty"><div class="empty-ic">👥</div><div class="empty-tt" data-tr="Aucun participant">Aucun participant</div></div></td></tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
              <?php
                // Construire les données QR complètes pour ce participant
                $qrData = implode('|', [
                    'FoodSave-Participant',
                    'ID:'.$r['id'],
                    'Nom:'.$r['prenom'].' '.$r['nom'],
                    'Email:'.$r['email'],
                    'Tel:'.($r['telephone']?:'N/A'),
                    'Statut:'.$r['statut'],
                    'Evenement:'.($r['ev_titre']??'N/A'),
                    'Date_Ev:'.($r['ev_date']??'N/A'),
                    'Lieu:'.($r['ev_lieu']??'N/A'),
                    'Inscription:'.date('d/m/Y',strtotime($r['date_inscription']))
                ]);
              ?>
              <tr data-filterable>
                <td style="color:var(--g500);font-size:.78rem"><?= $r['id'] ?></td>

                <!-- Participant -->
                <td>
                  <div style="display:flex;align-items:center;gap:8px">
                    <div class="av"><?= strtoupper(substr($r['prenom'],0,1).substr($r['nom'],0,1)) ?></div>
                    <div>
                      <strong><?= htmlspecialchars($r['prenom'].' '.$r['nom']) ?></strong>
                    </div>
                  </div>
                </td>

                <td><?= htmlspecialchars($r['email']) ?></td>
                <td><?= htmlspecialchars($r['telephone'] ?: '—') ?></td>

                <!-- Evénement + QR + Timer -->
                <td>
                  <span class="badge b-blue" style="font-size:.68rem"><?= htmlspecialchars($r['ev_titre'] ?? '—') ?></span>
                  <?php if (!empty($r['ev_date']) && $r['ev_statut'] === 'upcoming'): ?>
                  <div class="timer-wrap" data-countdown="<?= $r['ev_date'].' '.(substr($r['ev_heure']??'00:00',0,5)) ?>"></div>
                  <?php endif; ?>
                  <!-- QR avec toutes les données participant + événement -->
                  <div>
                    <button class="btn btn-outline btn-sm" style="padding:2px 8px;font-size:.65rem;margin-top:4px"
                            data-qr="<?= htmlspecialchars($qrData) ?>"
                            data-qr-target="qr-<?= $r['id'] ?>">📲 QR</button>
                  </div>
                  <div class="qr-container" id="qr-<?= $r['id'] ?>"></div>
                </td>

                <!-- Statut -->
                <td>
                  <?php if ($r['statut']==='confirmed'): ?>
                    <span class="badge b-green">✔ Confirme</span>
                  <?php elseif ($r['statut']==='pending'): ?>
                    <span class="badge b-orange">⏳ En attente</span>
                  <?php else: ?>
                    <span class="badge b-gray">✕ Annule</span>
                  <?php endif; ?>
                </td>

                <td style="font-size:.78rem;color:var(--g500)"><?= date('d/m/Y', strtotime($r['date_inscription'])) ?></td>

                <!-- Actions -->
                <td>
                  <div style="display:flex;gap:4px;flex-wrap:wrap">
                    <a href="p_form.php?id=<?= $r['id'] ?>" class="btn btn-outline btn-sm" title="Modifier">✏</a>

                    <?php if ($r['email']): ?>
                    <button class="btn btn-outline btn-sm" title="Email"
                            onclick="openEmailModal('<?= htmlspecialchars($r['email']) ?>','<?= htmlspecialchars($r['prenom'].' '.$r['nom']) ?>')">✉</button>
                    <?php endif; ?>

                    <button class="btn btn-outline btn-sm" title="Analyse sentimentale"
                            onclick="openSentimentModal('<?= htmlspecialchars(addslashes($r['prenom'].' '.$r['nom'])) ?>','<?= htmlspecialchars(addslashes($r['ev_titre']??'')) ?>')">🧠</button>

                    <form method="POST" style="display:inline" onsubmit="return confirmDel('Supprimer ce participant ?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main-wrap -->

<!-- MODAL SMS -->
<div class="modal-overlay" id="smsModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeSmsModal()">✕</button>
    <div class="modal-title">📱 Envoyer un SMS</div>
    <div class="form-group">
      <label>Destinataire</label>
      <input type="text" id="smsPhone" readonly style="background:var(--g100)">
    </div>
    <div class="form-group">
      <label>Message</label>
      <textarea id="smsMsg">Bonjour, rappel de votre inscription à l'événement FoodSave. Merci !</textarea>
    </div>
    <div id="gifZone" class="gif-zone"></div>
    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:14px">
      <button class="btn btn-outline" onclick="closeSmsModal()">Annuler</button>
      <button class="btn btn-primary" id="smsSendBtn" onclick="doSendEmail()">📤 Envoyer</button>
    </div>
  </div>
</div>

<!-- MODAL SENTIMENT -->
<div class="modal-overlay" id="sentimentModal">
  <div class="modal-box">
    <button class="modal-close" onclick="document.getElementById('sentimentModal').classList.remove('open')">✕</button>
    <div class="modal-title">🧠 Analyse Sentimentale IA</div>
    <p id="sentimentSubject" style="font-size:.85rem;color:var(--g700);margin-bottom:14px"></p>
    <div class="form-group">
      <label>Texte à analyser (commentaire, retour...)</label>
      <textarea id="sentimentText" placeholder="Ex: L'événement était fantastique, j'ai adoré l'ambiance..."></textarea>
    </div>
    <button class="btn btn-primary" onclick="doSentiment()" style="width:100%;justify-content:center">🔍 Analyser</button>
    <div id="sentimentResult" style="margin-top:14px"></div>
  </div>
</div>

<div class="toast-wrap" id="toasts"></div>

<script src="../../../public/js/validation.js"></script>
<script src="../../../public/js/features.js"></script>
<script>
const fsStats = {
    total_participants: <?= $stats['total'] ?>,
    confirmes:  <?= $stats['confirmed'] ?>,
    en_attente: <?= $stats['pending'] ?>,
    annules:    <?= $stats['cancelled'] ?>,
    taux_confirmation: <?= $stats['total'] > 0 ? round($stats['confirmed']/$stats['total']*100) : 0 ?>
};

function loadRecommendations() {
    getRecommendations({
        type: 'participants',
        stats: fsStats,
        contexte: 'Application FoodSave anti-gaspillage alimentaire en Tunisie'
    }, document.getElementById('aiReco'));
}

let currentEmail = '';
function openEmailModal(email, name) {
    currentEmail = email;
    document.getElementById('smsPhone').value = email + ' (' + name + ')';
    document.getElementById('smsMsg').value = 'Bonjour ' + name + ', rappel de votre inscription FoodSave. Merci !';
    document.getElementById('smsModal').classList.add('open');
    showGif('gifZone', 'food');
}
function closeEmailModal() {
    document.getElementById('smsModal').classList.remove('open');
    document.getElementById('gifZone').innerHTML = '';
}
function doSendEmail() {
    const msg = document.getElementById('smsMsg').value;
    sendEmail(currentEmail, msg, document.getElementById('smsSendBtn'));
}

function openSentimentModal(name, evTitre) {
    document.getElementById('sentimentSubject').textContent = 'Participant : ' + name + ' — Événement : ' + evTitre;
    document.getElementById('sentimentText').value = '';
    document.getElementById('sentimentResult').innerHTML = '';
    document.getElementById('sentimentModal').classList.add('open');
}
function doSentiment() {
    const text = document.getElementById('sentimentText').value.trim();
    if (!text) { toast('Entrez un texte à analyser', 'warning'); return; }
    analyzeSentiment(text, document.getElementById('sentimentResult'));
}
</script>
</body>
</html>
