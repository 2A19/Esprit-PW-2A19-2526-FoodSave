<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$searchQ = htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodSave – Gestion des Déchets</title>
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
tr.hidden-row{display:none}
</style>
</head>
<body>
<div style="padding:24px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
  <h1><i class="fas fa-trash-alt"></i> Gestion des Déchets</h1>
  <a href="index.php?action=dechet_create" class="btn-primary" style="padding:10px 20px;background:#4CAF50;color:#fff;border-radius:8px;text-decoration:none">
    <i class="fas fa-plus"></i> Nouveau déchet
  </a>
</div>

<?php if (!empty($_SESSION['success'])): ?>
<div style="background:#1b5e20;color:#a5d6a7;padding:12px 20px;border-radius:8px;margin-bottom:16px">
  <?= $_SESSION['success']; unset($_SESSION['success']); ?>
</div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
<div style="background:#b71c1c;color:#ffcdd2;padding:12px 20px;border-radius:8px;margin-bottom:16px">
  <?= $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px">
  <?php if (!empty($stats)): foreach($stats as $k=>$v): ?>
  <div style="background:#1a2e1a;border-radius:12px;padding:16px">
    <div style="color:#81c784;font-size:12px;text-transform:uppercase;letter-spacing:1px"><?= htmlspecialchars($k) ?></div>
    <div style="font-size:22px;font-weight:700;margin-top:4px"><?= htmlspecialchars((string)$v) ?></div>
  </div>
  <?php endforeach; endif; ?>
</div>

<!-- Barre de recherche -->
<div class="search-bar">
  <i class="fas fa-search" style="color:#4a7a4a;font-size:16px"></i>
  <input type="text" id="searchInput" placeholder="Rechercher par aliment, raison, catégorie, unité…"
         value="<?= $searchQ ?>" oninput="liveSearch(this.value)" onkeydown="if(event.key==='Enter')serverSearch()">
  <span class="search-count" id="searchCount"></span>
  <?php if ($searchQ !== ''): ?>
    <a href="index.php?action=dechet_index" class="btn-clear"><i class="fas fa-times"></i> Effacer</a>
  <?php endif; ?>
  <button onclick="serverSearch()"><i class="fas fa-search"></i> Rechercher</button>
</div>

<!-- Table -->
<div style="background:#1a2e1a;border-radius:12px;overflow:hidden">
<table style="width:100%;border-collapse:collapse" id="dechetTable">
<thead>
  <tr style="background:#243d24;color:#a5d6a7;font-size:12px;text-transform:uppercase;letter-spacing:1px">
    <th style="padding:14px 16px;text-align:left">ID</th>
    <th style="padding:14px 16px;text-align:left">Aliment</th>
    <th style="padding:14px 16px;text-align:left">Quantité</th>
    <th style="padding:14px 16px;text-align:left">Date</th>
    <th style="padding:14px 16px;text-align:left">Raison</th>
    <th style="padding:14px 16px;text-align:left">Actions</th>
  </tr>
</thead>
<tbody id="dechetBody">
<?php if (!empty($dechets)): foreach($dechets as $d): ?>
<tr style="border-top:1px solid #2e4d2e" class="dechet-row"
    data-search="<?= strtolower(htmlspecialchars($d->getTypeAliment().' '.$d->getRaison().' '.$d->getNotes().' '.$d->getUnite(), ENT_QUOTES)) ?>">
  <td style="padding:12px 16px;color:#81c784">#<?= $d->getId() ?></td>
  <td style="padding:12px 16px;font-weight:500"><?= htmlspecialchars($d->getTypeAliment()) ?></td>
  <td style="padding:12px 16px"><?= $d->getQuantite() ?> <?= htmlspecialchars($d->getUnite()) ?></td>
  <td style="padding:12px 16px"><?= htmlspecialchars($d->getDateDechet()) ?></td>
  <td style="padding:12px 16px"><?= htmlspecialchars($d->getRaison()) ?></td>
  <td style="padding:12px 16px">
    <a href="index.php?action=dechet_edit&id=<?= $d->getId() ?>" style="color:#64b5f6;margin-right:12px"><i class="fas fa-edit"></i></a>
    <a href="index.php?action=dechet_delete&id=<?= $d->getId() ?>" onclick="return confirm('Supprimer ce déchet ?')" style="color:#ef9a9a"><i class="fas fa-trash"></i></a>
  </td>
</tr>
<?php endforeach; else: ?>
<tr id="emptyRow"><td colspan="6" style="padding:32px;text-align:center;color:#81c784;opacity:.6">Aucun déchet enregistré.</td></tr>
<?php endif; ?>
<tr id="noResultRow" style="display:none">
  <td colspan="6" style="padding:32px;text-align:center;color:#81c784;opacity:.6">
    <i class="fas fa-search" style="font-size:24px;display:block;margin-bottom:8px"></i>
    Aucun résultat pour cette recherche.
  </td>
</tr>
</tbody>
</table>
</div>
<div style="margin-top:16px"><a href="index.php?action=dashboard" style="color:#81c784"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a></div>
</div>

<script>
function liveSearch(q) {
    const rows   = document.querySelectorAll('.dechet-row');
    const noRes  = document.getElementById('noResultRow');
    const count  = document.getElementById('searchCount');
    const term   = q.trim().toLowerCase();
    let visible  = 0;
    rows.forEach(r => {
        const match = term === '' || r.dataset.search.includes(term);
        r.classList.toggle('hidden-row', !match);
        if (match) visible++;
    });
    noRes.style.display = (visible === 0 && term !== '') ? '' : 'none';
    count.textContent   = term !== '' ? visible + ' résultat' + (visible !== 1 ? 's' : '') : '';
}

function serverSearch() {
    const q = document.getElementById('searchInput').value.trim();
    if (q === '') { window.location.href = 'index.php?action=dechet_index'; return; }
    window.location.href = 'index.php?action=dechet_search&q=' + encodeURIComponent(q);
}

// Applique le filtre live si une recherche serveur est déjà active
document.addEventListener('DOMContentLoaded', () => {
    const q = document.getElementById('searchInput').value.trim();
    if (q) liveSearch(q);
});
</script>
</body></html>
