<?php
if(!isset($avis) || !isset($article)) {
    header('Location: index.php?action=blog');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Modifier mon avis</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/FoodSave/public/assets/css/style.css">
    
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
        }
        button {
            background: linear-gradient(135deg, #ff6b35, #e65100);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
        }
        button:hover { transform: translateY(-2px); }
        .btn-back {
            display: inline-block;
            margin-bottom: 1rem;
            color: #4caf50;
            text-decoration: none;
        }
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 10px;
        }
        .rating input { display: none; }
        .rating label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
        }
        .rating input:checked ~ label,
        .rating label:hover,
        .rating label:hover ~ label {
            color: #ffc107;
        }
        .info-note {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 1rem;
            color: #666;
        }
        .error-global {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: none;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .required-star { color: red; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container nav-container">
        <div class="nav-logo">
            <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="Logo" style="height: 45px;">
            <span style="font-weight: 700; font-size: 1.4rem;">
                <span style="color: #ff6b35;">Food</span><span style="color: #4caf50;">Save</span>
            </span>
        </div>
        <div class="nav-menu">
            <a href="index.php?action=blog" class="nav-link">Blog</a>
            <a href="index.php?action=conseils" class="nav-link">Conseils</a>
            <a href="index.php?action=recettes" class="nav-link">Recettes</a>
        </div>
    </div>
</nav>

<div class="form-container">
    <a href="index.php?action=showAvis&article_id=<?php echo $article['id']; ?>" class="btn-back">
        ← Retour aux avis
    </a>
    
    <h1>✏️ Modifier mon avis</h1>
    
    <div id="erreurGlobal" class="error-global">
        ⚠️ Veuillez corriger les erreurs ci-dessous.
    </div>
    
    <?php if(isset($_GET['error'])): ?>
        <div class="error-message" style="background:#f8d7da; padding:10px; border-radius:10px; margin-bottom:1rem;">
            ❌ Une erreur est survenue. Veuillez réessayer.
        </div>
    <?php endif; ?>
    
    <div class="info-note">
        <strong>Article :</strong> <?php echo htmlspecialchars($article['titre']); ?>
    </div>
    
    <form id="editAvisForm" action="index.php?action=editUserAvis" method="POST">
        <input type="hidden" name="id" value="<?php echo $avis['id']; ?>">
        
        <div class="form-group">
            <label>Ma note <span class="required-star">*</span></label>
            <div class="rating" id="ratingContainer">
                <input type="radio" name="note" id="star5" value="5" <?php echo ($avis['note'] == 5) ? 'checked' : ''; ?>><label for="star5">★</label>
                <input type="radio" name="note" id="star4" value="4" <?php echo ($avis['note'] == 4) ? 'checked' : ''; ?>><label for="star4">★</label>
                <input type="radio" name="note" id="star3" value="3" <?php echo ($avis['note'] == 3) ? 'checked' : ''; ?>><label for="star3">★</label>
                <input type="radio" name="note" id="star2" value="2" <?php echo ($avis['note'] == 2) ? 'checked' : ''; ?>><label for="star2">★</label>
                <input type="radio" name="note" id="star1" value="1" <?php echo ($avis['note'] == 1) ? 'checked' : ''; ?>><label for="star1">★</label>
            </div>
        </div>
        
        <div class="form-group">
            <label>Mon avis <span class="required-star">*</span></label>
            <textarea id="contenu" name="contenu" rows="5" placeholder="Partagez votre expérience..."><?php echo htmlspecialchars($avis['contenu']); ?></textarea>
            <small style="color: #666;">Minimum 5 caractères, maximum 500</small>
        </div>
        
        <button type="submit">💾 Enregistrer les modifications</button>
    </form>
</div>

<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <span>🍽️ FoodSave</span>
            <p>Stop au gaspillage alimentaire</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2025 FoodSave - Tous droits réservés</p>
    </div>
</footer>

<!-- Inclusion des fichiers JavaScript -->
<script src="/FoodSave/public/js/validationAvis.js"></script>
<script src="/FoodSave/public/js/modifier_mon_avis.js"></script>

</body>
</html>