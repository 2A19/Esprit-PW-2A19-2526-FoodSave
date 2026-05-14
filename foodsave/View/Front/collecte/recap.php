<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodSave – Récapitulatif Collectes × Catégories</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style-dechet.css">
</head>
<body>
<div style="max-width:1100px;margin:32px auto;padding:24px">
<div style="display:flex;align-items:center;gap:16px;margin-bottom:28px">
  <a href="index.php?action=collecte_index" style="color:#81c784;text-decoration:none"><i class="fas fa-arrow-left"></i></a>
  <h1><i class="fas fa-chart-bar" style="color:#FFA726"></i> Récapitulatif Collectes × Catégories de Déchets</h1>
</div>
<p style="color:#81c784;margin-bottom:24px">Jointure 3 entités : collectes → collecte_dechets → dechets → categories</p>

<div style="background:#1a2e1a;border-radius:12px;overflow:hidden">
<table style="width:100%;border-collapse:collapse">
<thead>
  <tr style="background:#243d24;color:#a5d6a7;font-size:12px;text-transform:uppercase">
    <th style="padding:14px 16px;text-align:left">Collecte</th>
    <th style="padding:14px 16px;text-align:left">Date</th>
    <th style="padding:14px 16px;text-align:left">Lieu</th>
    <th style="padding:14px 16px;text-align:left">Déchet</th>
    <th style="padding:14px 16px;text-align:left">Quantité</th>
    <th style="padding:14px 16px;text-align:left">Catégorie</th>
  </tr>
</thead>
<tbody>
<?php if (!empty($recap)): foreach($recap as $r): ?>
<tr style="border-top:1px solid #2e4d2e">
  <td style="padding:12px 16px;font-weight:500"><?= htmlspecialchars($r['collecte_titre'] ?? '—') ?></td>
  <td style="padding:12px 16px;font-size:13px"><?= htmlspecialchars($r['date_collecte'] ?? '—') ?></td>
  <td style="padding:12px 16px;font-size:13px;color:#81c784"><?= htmlspecialchars($r['lieu'] ?? '—') ?></td>
  <td style="padding:12px 16px"><?= htmlspecialchars($r['type_aliment'] ?? '—') ?></td>
  <td style="padding:12px 16px"><?= htmlspecialchars((string)($r['dechet_quantite'] ?? '')) ?> <?= htmlspecialchars($r['dechet_unite'] ?? '') ?></td>
  <td style="padding:12px 16px">
    <span style="background:#1b5e20;padding:3px 10px;border-radius:20px;font-size:12px">
      <?= htmlspecialchars($r['categorie_icone'] ?? '') ?> <?= htmlspecialchars($r['categorie_nom'] ?? '—') ?>
    </span>
  </td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="padding:32px;text-align:center;opacity:.6">Aucune donnée.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</body></html>
