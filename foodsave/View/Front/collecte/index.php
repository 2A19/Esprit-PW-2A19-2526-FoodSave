<?php if (session_status() === PHP_SESSION_NONE) session_start();
$searchQ = htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodSave – Collectes</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style-dechet.css">
<style>
.search-bar{display:flex;gap:10px;align-items:center;background:#1a2e1a;border-radius:12px;padding:14px 18px;margin-bottom:24px;border:1px solid #2e4d2e}
.search-bar input{flex:1;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;padding:9px 14px;color:#e8f5e9;font-size:14px;outline:none;transition:border .2s}
.search-bar input:focus{border-color:#4ade80}
.search-bar input::placeholder{color:#4a7a4a}
.search-bar button{padding:9px 18px;background:#4CAF50;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:13px;display:flex;align-items:center;gap:6px;white-space:nowrap}
.search-bar button:hover{background:#388e3c}
.btn-clear{padding:9px 14px;background:transparent;color:#81c784;border:1px solid #2e4d2e;border-radius:8px;cursor:pointer;font-size:13px;text-decoration:none;display:flex;align-items:center;gap:6px}
.btn-clear:hover{background:#1b3a1b}
.search-count{font-size:12px;color:#81c784;opacity:.7;white-space:nowrap}
.collecte-card.hidden-card{display:none}
</style>
</head>
<body>
<div style="padding:24px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
  <h1><i class="fas fa-truck"></i> Collectes Alimentaires</h1>
  <a href="index.php?action=collecte_recap" style="padding:10px 20px;background:#1565c0;color:#fff;border-radius:8px;text-decoration:none">
    <i class="fas fa-chart-bar"></i> Récapitulatif
  </a>
</div>

<?php if (!empty($_SESSION['success'])): ?>
<div style="background:#1b5e20;color:#a5d6a7;padding:12px 20px;border-radius:8px;margin-bottom:16px">
  <?= $_SESSION['success']; unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<!-- Formulaire d'ajout inline -->
<div style="background:#1a2e1a;padding:24px;border-radius:12px;margin-bottom:28px">
<h3 style="margin-bottom:16px;color:#a5d6a7"><i class="fas fa-plus"></i> Nouvelle Collecte</h3>
<form method="POST" action="index.php?action=collecte_store" novalidate style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
  <div>
    <label style="display:block;margin-bottom:6px;font-size:13px;color:#81c784">Titre *</label>
    <input type="text" name="titre" required style="width:100%;padding:9px 13px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
  </div>
  <div>
    <label style="display:block;margin-bottom:6px;font-size:13px;color:#81c784">Lieu *</label>
    <input type="text" name="lieu" required style="width:100%;padding:9px 13px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
  </div>
  <div>
    <label style="display:block;margin-bottom:6px;font-size:13px;color:#81c784">Date *</label>
    <input type="date" name="date_collecte" value="<?= date('Y-m-d') ?>" style="width:100%;padding:9px 13px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
  </div>
  <div>
    <label style="display:block;margin-bottom:6px;font-size:13px;color:#81c784">Statut</label>
    <select name="statut" style="width:100%;padding:9px 13px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
      <option value="planifiee">📅 Planifiée</option>
      <option value="en_cours">🔄 En cours</option>
      <option value="terminee">✅ Terminée</option>
      <option value="annulee">❌ Annulée</option>
    </select>
  </div>
  <div style="grid-column:1/-1">
    <textarea name="description" rows="2" placeholder="Description..." style="width:100%;padding:9px 13px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9;resize:vertical"></textarea>
  </div>
  <div style="grid-column:1/-1">
    <button type="submit" style="padding:10px 24px;background:#4CAF50;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600">
      <i class="fas fa-save"></i> Créer la collecte
    </button>
  </div>
</form>
</div>

<!-- Barre de recherche -->
<div class="search-bar">
  <i class="fas fa-search" style="color:#4a7a4a;font-size:16px"></i>
  <input type="text" id="searchInput" placeholder="Rechercher par titre, lieu, statut, description…"
         value="<?= $searchQ ?>" oninput="liveSearch(this.value)" onkeydown="if(event.key==='Enter')serverSearch()">
  <span class="search-count" id="searchCount"></span>
  <?php if ($searchQ !== ''): ?>
    <a href="index.php?action=collecte_index" class="btn-clear"><i class="fas fa-times"></i> Effacer</a>
  <?php endif; ?>
  <button onclick="serverSearch()"><i class="fas fa-search"></i> Rechercher</button>
</div>

<!-- Liste des collectes -->
<div style="display:grid;gap:16px" id="collecteList">
<?php if (!empty($collectes)): foreach($collectes as $c):
  $statusColors = ['planifiee'=>'#1565c0','en_cours'=>'#f57c00','terminee'=>'#2e7d32','annulee'=>'#c62828'];
  $statusLabels = ['planifiee'=>'📅 Planifiée','en_cours'=>'🔄 En cours','terminee'=>'✅ Terminée','annulee'=>'❌ Annulée'];
  $bg = $statusColors[$c->getStatut()] ?? '#333';
?>
<div class="collecte-card" style="background:#1a2e1a;border-radius:12px;padding:20px;display:flex;justify-content:space-between;align-items:center"
     data-search="<?= strtolower(htmlspecialchars($c->getTitre().' '.$c->getLieu().' '.$c->getStatut().' '.($c->getDescription??('')), ENT_QUOTES)) ?>">
  <div>
    <span style="display:inline-block;background:<?= $bg ?>;color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;margin-bottom:8px">
      <?= $statusLabels[$c->getStatut()] ?? $c->getStatut() ?>
    </span>
    <h3 style="margin:0 0 4px"><?= htmlspecialchars($c->getTitre()) ?></h3>
    <div style="color:#81c784;font-size:13px">
      <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($c->getLieu()) ?>
      &nbsp;·&nbsp;
      <i class="fas fa-calendar"></i> <?= htmlspecialchars($c->getDateCollecte()) ?>
      &nbsp;·&nbsp;
      <i class="fas fa-weight"></i> <?= $c->getQuantiteTotale() ?> <?= htmlspecialchars($c->getUnite()) ?>
    </div>
  </div>
  <div style="display:flex;gap:12px">
    <a href="index.php?action=collecte_show&id=<?= $c->getId() ?>" style="color:#64b5f6;font-size:18px" title="Voir les déchets liés">
      <i class="fas fa-eye"></i>
    </a>
    <a href="index.php?action=collecte_delete&id=<?= $c->getId() ?>" onclick="return confirm('Supprimer cette collecte ?')" style="color:#ef9a9a;font-size:18px">
      <i class="fas fa-trash"></i>
    </a>
  </div>
</div>
<?php endforeach; else: ?>
<div id="emptyMsg" style="text-align:center;padding:40px;background:#1a2e1a;border-radius:12px;color:#81c784;opacity:.6">
  Aucune collecte enregistrée.
</div>
<?php endif; ?>
<div id="noResultMsg" style="display:none;text-align:center;padding:40px;background:#1a2e1a;border-radius:12px;color:#81c784;opacity:.6">
  <i class="fas fa-search" style="font-size:24px;display:block;margin-bottom:8px"></i>
  Aucun résultat pour cette recherche.
</div>
</div>

<div style="margin-top:20px"><a href="index.php?action=dashboard" style="color:#81c784"><i class="fas fa-arrow-left"></i> Tableau de bord</a></div>
</div>

<script>
function liveSearch(q) {
    const cards  = document.querySelectorAll('.collecte-card');
    const noRes  = document.getElementById('noResultMsg');
    const count  = document.getElementById('searchCount');
    const term   = q.trim().toLowerCase();
    let visible  = 0;
    cards.forEach(c => {
        const match = term === '' || c.dataset.search.includes(term);
        c.classList.toggle('hidden-card', !match);
        if (match) visible++;
    });
    noRes.style.display = (visible === 0 && term !== '') ? 'block' : 'none';
    count.textContent   = term !== '' ? visible + ' résultat' + (visible !== 1 ? 's' : '') : '';
}

function serverSearch() {
    const q = document.getElementById('searchInput').value.trim();
    if (q === '') { window.location.href = 'index.php?action=collecte_index'; return; }
    window.location.href = 'index.php?action=collecte_search&q=' + encodeURIComponent(q);
}

document.addEventListener('DOMContentLoaded', () => {
    const q = document.getElementById('searchInput').value.trim();
    if (q) liveSearch(q);
});
</script>
</body></html>
