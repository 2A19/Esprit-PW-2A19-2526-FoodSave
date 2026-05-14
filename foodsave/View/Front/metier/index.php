<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodSave – Métiers</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style-dechet.css">
</head>
<body>
<div style="padding:24px">
<h1 style="margin-bottom:24px"><i class="fas fa-briefcase"></i> Métiers</h1>

<?php if (!empty($_SESSION['success'])): ?>
<div style="background:#1b5e20;color:#a5d6a7;padding:12px 20px;border-radius:8px;margin-bottom:16px"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:24px">
<div style="background:#1a2e1a;padding:24px;border-radius:12px;align-self:start">
  <h3 style="margin-bottom:16px;color:#a5d6a7">Nouveau métier</h3>
  <form method="POST" action="index.php?action=metier_store" novalidate>
    <div style="margin-bottom:14px">
      <label style="display:block;margin-bottom:6px;font-size:13px;color:#81c784">Nom *</label>
      <input type="text" name="nom" required style="width:100%;padding:9px 13px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
    </div>
    <div style="margin-bottom:14px">
      <label style="display:block;margin-bottom:6px;font-size:13px;color:#81c784">Icône (emoji)</label>
      <input type="text" name="icone" value="💼" style="width:100%;padding:9px 13px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
    </div>
    <div style="margin-bottom:14px">
      <label style="display:block;margin-bottom:6px;font-size:13px;color:#81c784">Description</label>
      <textarea name="description" rows="2" style="width:100%;padding:9px 13px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9;resize:vertical"></textarea>
    </div>
    <div style="margin-bottom:20px;display:flex;align-items:center;gap:8px">
      <input type="checkbox" name="actif" id="actif" value="1" checked style="width:16px;height:16px">
      <label for="actif" style="color:#81c784;font-size:13px">Actif</label>
    </div>
    <button type="submit" style="padding:10px 20px;background:#4CAF50;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;width:100%">
      <i class="fas fa-plus"></i> Créer
    </button>
  </form>
</div>

<div style="background:#1a2e1a;border-radius:12px;overflow:hidden;align-self:start">
<table style="width:100%;border-collapse:collapse">
<thead>
  <tr style="background:#243d24;color:#a5d6a7;font-size:12px;text-transform:uppercase">
    <th style="padding:14px 16px;text-align:left">Métier</th>
    <th style="padding:14px 16px;text-align:left">Description</th>
    <th style="padding:14px 16px;text-align:left">Statut</th>
    <th style="padding:14px 16px;text-align:left">Actions</th>
  </tr>
</thead>
<tbody>
<?php if (!empty($metiers)): foreach($metiers as $m): ?>
<tr style="border-top:1px solid #2e4d2e">
  <td style="padding:12px 16px;font-weight:500"><?= $m->getIcone() ?> <?= htmlspecialchars($m->getNom()) ?></td>
  <td style="padding:12px 16px;font-size:13px;color:#81c784"><?= htmlspecialchars($m->getDescription() ?? '—') ?></td>
  <td style="padding:12px 16px">
    <span style="background:<?= $m->isActif()?'#1b5e20':'#4a0000' ?>;color:<?= $m->isActif()?'#a5d6a7':'#ffcdd2' ?>;padding:3px 10px;border-radius:20px;font-size:11px">
      <?= $m->isActif() ? '✅ Actif' : '❌ Inactif' ?>
    </span>
  </td>
  <td style="padding:12px 16px">
    <a href="index.php?action=metier_delete&id=<?= $m->getId() ?>" onclick="return confirm('Supprimer ?')" style="color:#ef9a9a"><i class="fas fa-trash"></i></a>
  </td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="4" style="padding:28px;text-align:center;opacity:.6">Aucun métier.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<div style="margin-top:20px"><a href="index.php?action=dechet_index" style="color:#81c784"><i class="fas fa-arrow-left"></i> Déchets</a></div>
</div>
</body></html>
