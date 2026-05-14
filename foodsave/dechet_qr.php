<?php
/**
 * FoodSave — Page de scan QR : dechet.php
 * URL : /dechet.php?id=5
 * Affiche la fiche complète d'un déchet sur mobile.
 */
declare(strict_types=1);
require_once __DIR__ . '/Model/Dechet.php';

$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$d     = null;
$data  = [];

if ($id <= 0) {
    $error = 'Identifiant manquant ou invalide.';
} else {
    try {
        $m = new Dechet();
        $d = $m->findById($id);
        if (!$d) {
            $error = "Aucun déchet trouvé avec l'identifiant #$id.";
        } else {
            $data = $d->toArray();
            // Récupérer categorie_nom si disponible via findById JOIN
            // On réinterroge directement pour avoir categorie_nom
            $pdo = (new ReflectionProperty(Dechet::class, 'pdo'))->getValue($d);
        }
    } catch (Throwable $e) {
        $error = 'Erreur serveur : ' . htmlspecialchars($e->getMessage());
    }
}

// Formatage date
function fmtDate(string $date): string {
    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : $date;
}
function e(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

// Couleur raison
$raisonColors = [
    'périmé'        => '#e74c3c',
    'périmée'       => '#e74c3c',
    'date dépassée' => '#e74c3c',
    'excès'         => '#f39c12',
    'surplus'       => '#f39c12',
    'qualité'       => '#9b59b6',
    'cuisson'       => '#3498db',
    'préparation'   => '#3498db',
];
$raisonColor = '#40916c';
if ($d) {
    $r = strtolower($data['raison']);
    foreach ($raisonColors as $k => $c) {
        if (str_contains($r, $k)) { $raisonColor = $c; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FoodSave — <?= $d ? e($data['type_aliment']) . ' #' . $data['id'] : 'Déchet introuvable' ?></title>
  <meta name="theme-color" content="#2d6a4f">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --green-dark:  #1b4332;
      --green:       #2d6a4f;
      --green-mid:   #40916c;
      --green-light: #74c69d;
      --green-pale:  #d8f3dc;
      --white:       #ffffff;
      --gray-50:     #f8fafb;
      --gray-100:    #f0f4f2;
      --gray-300:    #c8d8d0;
      --gray-500:    #7a9488;
      --gray-700:    #3d5a50;
      --text:        #1a2e26;
      --radius:      16px;
      --shadow:      0 4px 24px rgba(27,67,50,.13);
      --shadow-lg:   0 8px 40px rgba(27,67,50,.18);
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: var(--gray-100);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ── Header ── */
    .header {
      background: linear-gradient(135deg, var(--green-dark), var(--green));
      color: var(--white);
      padding: 20px 20px 32px;
      position: relative;
      overflow: hidden;
    }
    .header::after {
      content: '';
      position: absolute;
      bottom: -20px; left: 0; right: 0;
      height: 40px;
      background: var(--gray-100);
      border-radius: 50% 50% 0 0 / 100% 100% 0 0;
    }
    .header-top {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 6px;
    }
    .logo { font-size: 1.3rem; font-weight: 800; letter-spacing: -.02em; }
    .logo span { opacity: .7; font-weight: 400; font-size: .85rem; margin-left: 6px; }
    .badge-scan {
      margin-left: auto;
      background: rgba(255,255,255,.2);
      border: 1px solid rgba(255,255,255,.35);
      color: #fff;
      font-size: .72rem;
      padding: 3px 10px;
      border-radius: 20px;
      font-weight: 600;
      letter-spacing: .04em;
    }
    .header-sub {
      font-size: .82rem;
      opacity: .75;
      margin-top: 2px;
    }

    /* ── Main card ── */
    .main { flex: 1; padding: 28px 16px 32px; max-width: 520px; margin: 0 auto; width: 100%; }

    .card {
      background: var(--white);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow: hidden;
    }

    /* ── Card top ── */
    .card-top {
      padding: 22px 22px 18px;
      border-bottom: 1px solid var(--gray-100);
    }
    .card-id {
      font-size: .75rem;
      color: var(--gray-500);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: 6px;
    }
    .card-name {
      font-size: 1.55rem;
      font-weight: 800;
      color: var(--text);
      line-height: 1.2;
    }
    .card-status {
      margin-top: 10px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: var(--green-pale);
      color: var(--green-dark);
      font-size: .78rem;
      font-weight: 700;
      padding: 4px 12px;
      border-radius: 20px;
    }

    /* ── Fields grid ── */
    .fields {
      padding: 18px 22px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }
    .field {
      background: var(--gray-50);
      border-radius: 12px;
      padding: 14px 16px;
      border: 1px solid var(--gray-100);
    }
    .field.full { grid-column: 1 / -1; }
    .field-label {
      font-size: .7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .07em;
      color: var(--gray-500);
      margin-bottom: 5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .field-value {
      font-size: 1rem;
      font-weight: 600;
      color: var(--text);
      line-height: 1.4;
    }
    .field-value.qty {
      font-size: 1.25rem;
      font-weight: 800;
      color: var(--green);
    }
    .raison-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      color: #fff;
      font-size: .85rem;
      font-weight: 600;
    }
    .cat-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: var(--green-pale);
      color: var(--green-dark);
      padding: 4px 12px;
      border-radius: 20px;
      font-size: .82rem;
      font-weight: 600;
    }

    /* ── Footer card ── */
    .card-footer {
      padding: 16px 22px;
      background: var(--gray-50);
      border-top: 1px solid var(--gray-100);
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .btn {
      flex: 1;
      padding: 12px 16px;
      border-radius: 10px;
      border: none;
      font-size: .9rem;
      font-weight: 700;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: opacity .15s, transform .1s;
    }
    .btn:active { transform: scale(.97); opacity: .85; }
    .btn-primary { background: var(--green); color: #fff; }
    .btn-outline { background: var(--white); color: var(--green); border: 2px solid var(--green-light); }

    /* ── Error ── */
    .error-card {
      background: #fff5f5;
      border: 1px solid #fed7d7;
      border-radius: var(--radius);
      padding: 32px 24px;
      text-align: center;
    }
    .error-icon { font-size: 3rem; margin-bottom: 12px; }
    .error-title { font-size: 1.1rem; font-weight: 700; color: #c53030; margin-bottom: 8px; }
    .error-msg { color: #742a2a; font-size: .9rem; }

    /* ── Timestamp ── */
    .timestamp {
      text-align: center;
      margin-top: 20px;
      font-size: .75rem;
      color: var(--gray-500);
    }

    /* ── Footer ── */
    .footer {
      text-align: center;
      padding: 16px;
      font-size: .75rem;
      color: var(--gray-500);
    }
    .footer a { color: var(--green-mid); text-decoration: none; font-weight: 600; }
  </style>
</head>
<body>

<!-- Header -->
<div class="header">
  <div class="header-top">
    <div class="logo">🌿 FoodSave <span>Zéro gaspillage</span></div>
    <span class="badge-scan">📱 SCAN QR</span>
  </div>
  <div class="header-sub">Fiche déchet alimentaire</div>
</div>

<!-- Main -->
<div class="main">

<?php if ($error): ?>
  <div class="error-card">
    <div class="error-icon">❌</div>
    <div class="error-title">Déchet introuvable</div>
    <div class="error-msg"><?= e($error) ?></div>
  </div>

<?php else:
  $catNom = ''; // récupéré via JOIN dans findById
  // On re-fetch avec PDO direct pour avoir categorie_nom
  try {
    $pdo2 = \Database::getConnection();
    $st = $pdo2->prepare(
      "SELECT d.*, c.nom AS categorie_nom, c.couleur AS categorie_couleur
       FROM dechets d
       LEFT JOIN categories c ON c.id = d.categorie_id
       WHERE d.id = :id LIMIT 1"
    );
    $st->execute([':id' => $id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    $catNom    = $row['categorie_nom']    ?? '';
    $catColor  = $row['categorie_couleur'] ?? '#40916c';
    $createdAt = $row['created_at'] ?? '';
  } catch(Throwable $e2) {
    $catNom = ''; $catColor = '#40916c'; $createdAt = '';
  }
?>
  <div class="card">

    <!-- Top -->
    <div class="card-top">
      <div class="card-id">Déchet alimentaire · #<?= $data['id'] ?></div>
      <div class="card-name"><?= e($data['type_aliment']) ?></div>
      <div class="card-status">✅ Enregistré dans FoodSave</div>
    </div>

    <!-- Fields -->
    <div class="fields">

      <div class="field">
        <div class="field-label">⚖️ Quantité</div>
        <div class="field-value qty">
          <?= number_format((float)$data['quantite'], 3, '.', '') ?>
          <small style="font-size:.7em;font-weight:600;color:var(--gray-500);">
            <?= e($data['unite']) ?>
          </small>
        </div>
      </div>

      <div class="field">
        <div class="field-label">📅 Date</div>
        <div class="field-value"><?= fmtDate($data['date_dechet']) ?></div>
      </div>

      <div class="field full">
        <div class="field-label">🔎 Raison du gaspillage</div>
        <div class="field-value">
          <span class="raison-badge" style="background:<?= e($raisonColor) ?>">
            <?= e($data['raison']) ?>
          </span>
        </div>
      </div>

      <?php if ($catNom): ?>
      <div class="field full">
        <div class="field-label">🏷️ Catégorie</div>
        <div class="field-value">
          <span class="cat-badge" style="background:<?= e($catColor) ?>22;color:<?= e($catColor) ?>">
            <?= e($catNom) ?>
          </span>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($data['notes'])): ?>
      <div class="field full">
        <div class="field-label">📝 Notes</div>
        <div class="field-value" style="font-weight:400;font-size:.92rem;color:#4a6057;">
          <?= e($data['notes']) ?>
        </div>
      </div>
      <?php endif; ?>

    </div><!-- /fields -->

    <!-- Footer buttons -->
    <div class="card-footer">
      <a class="btn btn-primary" href="index.php?action=dechet_index">🏠 Ouvrir FoodSave</a>
      <a class="btn btn-outline" href="index.php?action=dechet_index" onclick="history.back();return false;">← Retour</a>
    </div>

  </div><!-- /card -->

  <?php if ($createdAt): ?>
  <div class="timestamp">
    Créé le <?= fmtDate($createdAt) ?>
  </div>
  <?php endif; ?>

<?php endif; ?>

</div><!-- /main -->

<div class="footer">
  <a href="index.php?action=dechet_index">🌿 FoodSave</a> · Zéro gaspillage alimentaire
</div>

</body>
</html>
