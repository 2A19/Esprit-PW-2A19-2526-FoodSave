<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodSave – Détail Collecte</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style-dechet.css">
</head>
<body>
<div style="max-width:900px;margin:32px auto;padding:24px">
<a href="index.php?action=collecte_index" style="color:#81c784;display:inline-flex;align-items:center;gap:8px;margin-bottom:24px;text-decoration:none">
  <i class="fas fa-arrow-left"></i> Retour aux collectes
</a>
<div style="background:#1a2e1a;border-radius:12px;padding:28px;margin-bottom:24px">
  <h1 style="margin:0 0 12px"><?= htmlspecialchars($col->getTitre()) ?></h1>
  <div style="color:#81c784;margin-bottom:8px">
    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($col->getLieu()) ?>
    &nbsp;·&nbsp;
    <i class="fas fa-calendar"></i> <?= htmlspecialchars($col->getDateCollecte()) ?>
    &nbsp;·&nbsp;
    <i class="fas fa-weight"></i> <?= $col->getQuantiteTotale() ?> <?= htmlspecialchars($col->getUnite()) ?>
  </div>
  <?php if ($col->getDescription()): ?>
  <p style="color:#c8e6c9;margin-top:12px"><?= htmlspecialchars($col->getDescription()) ?></p>
  <?php endif; ?>
</div>

<h2 style="margin-bottom:16px"><i class="fas fa-link" style="color:#4CAF50"></i> Déchets associés (jointure 3 entités)</h2>
<div style="background:#1a2e1a;border-radius:12px;overflow:hidden">
<table style="width:100%;border-collapse:collapse">
<thead>
  <tr style="background:#243d24;color:#a5d6a7;font-size:12px;text-transform:uppercase">
    <th style="padding:14px 16px;text-align:left">Aliment</th>
    <th style="padding:14px 16px;text-align:left">Quantité</th>
    <th style="padding:14px 16px;text-align:left">Raison</th>
    <th style="padding:14px 16px;text-align:left">Catégorie</th>
  </tr>
</thead>
<tbody>
<?php if (!empty($dechets)): foreach($dechets as $d): ?>
<tr style="border-top:1px solid #2e4d2e">
  <td style="padding:12px 16px;font-weight:500"><?= htmlspecialchars($d['type_aliment']) ?></td>
  <td style="padding:12px 16px"><?= $d['quantite'] ?> <?= htmlspecialchars($d['unite']) ?></td>
  <td style="padding:12px 16px"><?= htmlspecialchars($d['raison']) ?></td>
  <td style="padding:12px 16px">
    <?php if (!empty($d['categorie_icone'])): ?>
    <span style="background:#1b5e20;padding:4px 10px;border-radius:20px;font-size:12px">
      <?= $d['categorie_icone'] ?> <?= htmlspecialchars($d['categorie_nom'] ?? '—') ?>
    </span>
    <?php else: ?>—<?php endif; ?>
  </td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="4" style="padding:28px;text-align:center;color:#81c784;opacity:.6">Aucun déchet lié à cette collecte.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</body></html>
