<?php
// Vérifier que l'article existe
if(!isset($article)) {
    header('Location: index.php?action=blog');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - Donner mon avis</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/FoodSave/public/assets/css/style.css">    
    
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .form-card-avis {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid #e8e0c8;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e8e0c8;
            border-radius: 14px;
            font-family: "Poppins", sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .rating input {
            display: none;
        }
        
        .rating label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .rating input:checked ~ label,
        .rating label:hover,
        .rating label:hover ~ label {
            color: #ffc107;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .article-info {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(255, 107, 53, 0.05));
            padding: 1rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .btn-back {
            display: inline-block;
            margin-bottom: 1rem;
            color: #4caf50;
            text-decoration: none;
        }
        
        .btn-back:hover {
            text-decoration: underline;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        /* Style pour l'erreur globale */
        .error-global {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: none;
        }
        
        /* Style pour le champ en erreur */
        .error-border {
            border-color: #dc3545 !important;
            background-color: #fff8f8 !important;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="container nav-container">
            <div class="nav-logo">
                <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="Logo" style="height: 50px; width: auto;">
                <span style="font-weight: 700; font-size: 1.4rem;">
                    <span style="color: #ff6b35;">Food</span><span style="color: #4caf50;">Save</span>
                </span>
            </div>
            <div class="nav-menu">
                <a href="index.php?action=blog" class="nav-link">Accueil</a>
                <a href="index.php?action=blog" class="nav-link">Blog</a>
                <a href="index.php?action=conseils" class="nav-link">Conseils</a>
                <a href="index.php?action=recettes" class="nav-link">Recettes</a>
            </div>
            <div class="user-actions">
                <button class="login-btn login-outline">Connexion</button>
                <button class="login-btn login-primary">Inscription</button>
            </div>
        </div>
    </nav>

    <div class="form-container">
        <a href="index.php?action=showAvis&article_id=<?php echo $article['id']; ?>" class="btn-back">
            ← Retour aux avis
        </a>
        
        <div class="form-card-avis">
            <h1 style="color: #333; margin-bottom: 1rem;">
                <i class="fas fa-star" style="color: #ffc107;"></i> Donner mon avis
            </h1>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="error-message">
                    ❌ Une erreur est survenue. Veuillez réessayer.
                </div>
            <?php endif; ?>
            
            <!-- Message d'erreur global pour la validation JS -->
            <div id="erreurGlobal" class="error-global">
                ⚠️ Veuillez corriger les erreurs ci-dessous.
            </div>
            
            <div class="article-info">
                <strong>Article :</strong> <?php echo htmlspecialchars($article['titre']); ?>
            </div>
            
            <!-- Formulaire avec ID pour la validation JS -->
            <form id="avisForm" action="index.php?action=addAvis" method="POST">
                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                
                <div class="form-group">
                    <label>Votre note <span style="color: red;">*</span></label>
                    <div class="rating" id="ratingContainer">
                        <input type="radio" name="note" id="star5" value="5"><label for="star5">★</label>
                        <input type="radio" name="note" id="star4" value="4"><label for="star4">★</label>
                        <input type="radio" name="note" id="star3" value="3"><label for="star3">★</label>
                        <input type="radio" name="note" id="star2" value="2"><label for="star2">★</label>
                        <input type="radio" name="note" id="star1" value="1"><label for="star1">★</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Votre avis <span style="color: red;">*</span></label>
                    <textarea id="contenu" name="contenu" rows="5" placeholder="Partagez votre expérience..."></textarea>
                    <small style="color: #666;">Minimum 5 caractères, maximum 500</small>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Publier mon avis
                </button>
            </form>
        </div>
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
    <script src="/FoodSave/public/js/ajouter_avis.js"></script>

</body>
</html>