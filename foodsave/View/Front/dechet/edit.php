<?php if (session_status() === PHP_SESSION_NONE) session_start();
$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);

require_once __DIR__ . '/../../../Model/Category.php';
$catModel   = new Category();
$categories = $catModel->findAll();
// $dechet is set by controller
$d = $dechet;
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodSave – Modifier Déchet</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style-dechet.css">
</head>
<body>
<div style="max-width:640px;margin:40px auto;padding:24px">
<h1 style="margin-bottom:24px"><i class="fas fa-edit" style="color:#FFA726"></i> Modifier Déchet #<?= $d->getId() ?></h1>
<form method="POST" action="index.php?action=dechet_update" novalidate style="background:#1a2e1a;padding:28px;border-radius:12px">
  <input type="hidden" name="id" value="<?= $d->getId() ?>">
  <div style="margin-bottom:16px">
    <label style="display:block;margin-bottom:6px;color:#a5d6a7">Type d'aliment *</label>
    <input type="text" name="type_aliment" value="<?= htmlspecialchars($old['type_aliment'] ?? $d->getTypeAliment()) ?>"
      style="width:100%;padding:10px 14px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
    <div>
      <label style="display:block;margin-bottom:6px;color:#a5d6a7">Quantité *</label>
      <input type="number" name="quantite" step="0.001" min="0.001" value="<?= htmlspecialchars($old['quantite'] ?? $d->getQuantite()) ?>"
        style="width:100%;padding:10px 14px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
    </div>
    <div>
      <label style="display:block;margin-bottom:6px;color:#a5d6a7">Unité *</label>
      <select name="unite" style="width:100%;padding:10px 14px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
        <?php foreach(['kg','g','L','ml','unité','portion'] as $u): ?>
        <option value="<?= $u ?>" <?= ($old['unite']??$d->getUnite())===$u?'selected':'' ?>><?= $u ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div style="margin-bottom:16px">
    <label style="display:block;margin-bottom:6px;color:#a5d6a7">Date *</label>
    <input type="date" name="date_dechet" value="<?= htmlspecialchars($old['date_dechet'] ?? $d->getDateDechet()) ?>"
      style="width:100%;padding:10px 14px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
  </div>
  <div style="margin-bottom:16px">
    <label style="display:block;margin-bottom:6px;color:#a5d6a7">Raison *</label>
    <input type="text" name="raison" value="<?= htmlspecialchars($old['raison'] ?? $d->getRaison()) ?>"
      style="width:100%;padding:10px 14px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
  </div>
  <div style="margin-bottom:16px">
    <label style="display:block;margin-bottom:6px;color:#a5d6a7">Catégorie</label>
    <select name="categorie_id" style="width:100%;padding:10px 14px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9">
      <option value="">— Aucune —</option>
      <?php foreach($categories as $cat): ?>
      <option value="<?= $cat->getId() ?>" <?= ($old['categorie_id']??$d->getCategorieId())==$cat->getId()?'selected':'' ?>>
        <?= $cat->getIcone() ?> <?= htmlspecialchars($cat->getNom()) ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div style="margin-bottom:24px">
    <label style="display:block;margin-bottom:6px;color:#a5d6a7">Notes</label>
    <textarea name="notes" rows="3" style="width:100%;padding:10px 14px;background:#0d1f14;border:1px solid #2e4d2e;border-radius:8px;color:#e8f5e9;resize:vertical"><?= htmlspecialchars($old['notes'] ?? $d->getNotes()) ?></textarea>
  </div>
  <div style="display:flex;gap:12px">
    <button type="submit" style="padding:12px 28px;background:#FFA726;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:15px;font-weight:600">
      <i class="fas fa-save"></i> Mettre à jour
    </button>
    <a href="index.php?action=dechet_index" style="padding:12px 24px;background:#243d24;color:#a5d6a7;border-radius:8px;text-decoration:none">Annuler</a>
  </div>
</form>
</div>
</body></html>
