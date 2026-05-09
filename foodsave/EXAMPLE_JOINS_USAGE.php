<?php
/**
 * EXEMPLE: Utilisation des Jointures dans une Page Réelle
 * 
 * Cet exemple montre comment utiliser les contrôleurs avec jointures
 * pour afficher des données complexes liées
 */

// ============================================
// EXEMPLE 1: Dashboard Utilisateur
// ============================================

session_start();
include('config/config.php');
include('Controller/UserController.php');
include('Controller/ListeController.php');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: index.php?action=login');
    exit;
}

// Récupérer les données utilisateur avec jointures
$userController = new UserController();
$listeController = new ListeController();

// Statistiques utilisateur (JOIN avec listes et articles)
$stats = $userController->getUserStatistics($user_id);

// Listes actives de l'utilisateur (JOIN avec user et articles)
$listes = $listeController->getListesByUser($user_id);

// Aliments les plus utilisés globalement
$top_aliments = $userController->getTopAliments(5);

?>

<!DOCTYPE html>
<html>
<body>

<h1>Dashboard - Bienvenue <?php echo htmlspecialchars($stats['prenom']); ?></h1>

<!-- STATISTIQUES UTILISATEUR (Issue de JOIN) -->
<section>
    <h2>Vos Statistiques</h2>
    <p>Total listes: <?php echo $stats['total_listes']; ?></p>
    <p>Articles à acheter: <?php echo $stats['articles_a_acheter']; ?></p>
    <p>Articles achetés: <?php echo $stats['articles_achetes']; ?></p>
    <p>Listes de courses: <?php echo $stats['listes_courses']; ?></p>
    <p>Listes de stock: <?php echo $stats['listes_stock']; ?></p>
</section>

<!-- VOS LISTES (Issue de JOIN listes + articles count) -->
<section>
    <h2>Vos Listes</h2>
    <ul>
    <?php foreach ($listes as $liste): ?>
        <li>
            <strong><?php echo htmlspecialchars($liste['titre']); ?></strong>
            (Type: <?php echo $liste['type']; ?>, 
             Articles: <?php echo $liste['nombre_articles']; ?>)
            <a href="index.php?action=viewListe&id=<?php echo $liste['id']; ?>">Voir</a>
        </li>
    <?php endforeach; ?>
    </ul>
</section>

<!-- ALIMENTS POPULAIRES (JOIN aliments + articles + listes) -->
<section>
    <h2>Aliments les Plus Populaires</h2>
    <ul>
    <?php foreach ($top_aliments as $aliment): ?>
        <li>
            <?php echo htmlspecialchars($aliment['nom']); ?> 
            (Catégorie: <?php echo $aliment['categorie']; ?>)
            - Utilisé <?php echo $aliment['nombre_utilisations']; ?> fois 
            par <?php echo $aliment['nombre_utilisateurs']; ?> utilisateurs
        </li>
    <?php endforeach; ?>
    </ul>
</section>

</body>
</html>

<?php

// ============================================
// EXEMPLE 2: Vue Détaillée d'une Liste
// ============================================

$liste_id = $_GET['liste_id'] ?? null;
if ($liste_id) {
    $liste_detail = $listeController->getListeDetailsWithArticles($liste_id);
    
    // Affichage structuré
    echo "<h1>" . htmlspecialchars($liste_detail['titre']) . "</h1>";
    echo "<p>Type: " . $liste_detail['type'] . "</p>";
    echo "<p>Créée par: " . htmlspecialchars($liste_detail['user_prenom'] . ' ' . $liste_detail['user_nom']) . "</p>";
    
    // Articles (JOIN result)
    echo "<h2>Articles</h2>";
    echo "<table>";
    echo "<tr><th>Aliment</th><th>Catégorie</th><th>Quantité</th><th>Statut</th></tr>";
    
    foreach ($liste_detail['articles'] as $article) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($article['aliment_nom']) . "</td>";
        echo "<td>" . htmlspecialchars($article['categorie_nom']) . "</td>";
        echo "<td>" . $article['quantite'] . " " . $article['unite'] . "</td>";
        echo "<td>" . $article['article_statut'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// ============================================
// EXEMPLE 3: Admin Dashboard - Statistiques Globales
// ============================================

if ($_SESSION['user_role'] === 'admin') {
    $all_users = $userController->listUsers(); // With stats
    $system_stats = $userController->getSystemStatistics(); // All joins
    
    ?>
    
    <h1>Admin Dashboard</h1>
    
    <section>
        <h2>Statistiques Globales</h2>
        <p>Utilisateurs totaux: <?php echo $system_stats['total_utilisateurs']; ?></p>
        <p>Utilisateurs actifs: <?php echo $system_stats['utilisateurs_actifs']; ?></p>
        <p>Admins: <?php echo $system_stats['admins']; ?></p>
        <p>Listes totales: <?php echo $system_stats['total_listes']; ?></p>
        <p>Aliments totaux: <?php echo $system_stats['total_aliments']; ?></p>
        <p>Recettes totales: <?php echo $system_stats['total_recettes']; ?></p>
    </section>
    
    <section>
        <h2>Utilisateurs avec Activité (JOIN)</h2>
        <table>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Listes</th>
                <th>Articles</th>
                <th>Statut</th>
            </tr>
            <?php foreach ($all_users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo $user['nombre_listes']; ?></td>
                <td><?php echo $user['nombre_articles']; ?></td>
                <td><?php echo $user['statut']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>
    
    <?php
}

// ============================================
// EXEMPLE 4: Vue d'une Recette avec Ingrédients
// ============================================

include('Controller/RecetteController.php');

$recette_id = $_GET['recette_id'] ?? null;
if ($recette_id) {
    $recetteController = new RecetteController();
    $recette = $recetteController->getRecetteWithIngredients($recette_id);
    
    if ($recette) {
        ?>
        
        <div class="recette-detail">
            <h1><?php echo htmlspecialchars($recette['titre']); ?></h1>
            <p><?php echo nl2br(htmlspecialchars($recette['description'])); ?></p>
            
            <div class="recette-infos">
                <p><strong>Préparation:</strong> <?php echo $recette['temps_preparation']; ?> min</p>
                <p><strong>Cuisson:</strong> <?php echo $recette['temps_cuisson']; ?> min</p>
                <p><strong>Portions:</strong> <?php echo $recette['portions']; ?></p>
                <p><strong>Difficulté:</strong> <?php echo $recette['difficulte']; ?></p>
            </div>
            
            <h2>Ingrédients (JOIN avec aliments)</h2>
            <table>
                <tr>
                    <th>Ingrédient</th>
                    <th>Catégorie</th>
                    <th>Quantité</th>
                    <th>Calories/100g</th>
                </tr>
                <?php foreach ($recette['ingredients'] as $ingredient): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ingredient['aliment_nom']); ?></td>
                    <td><?php echo htmlspecialchars($ingredient['categorie_nom']); ?></td>
                    <td><?php echo $ingredient['quantite'] . ' ' . $ingredient['unite']; ?></td>
                    <td><?php echo $ingredient['calories_100g'] ?? 'N/A'; ?> kcal</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <?php
    }
}

// ============================================
// EXEMPLE 5: Requête Personnalisée
// ============================================

/**
 * Exemple: Obtenir toutes les listes d'un utilisateur 
 * avec le nombre d'articles par catégorie
 */
function getListesWithCategoryBreakdown($user_id) {
    $sql = "SELECT 
                l.id,
                l.titre,
                l.type,
                c.nom as categorie,
                COUNT(al.id) as nombre_articles
            FROM listes l
            INNER JOIN user u ON l.user_id = u.id
            LEFT JOIN articles_liste al ON l.id = al.liste_id
            LEFT JOIN aliments a ON al.aliment_id = a.id
            LEFT JOIN categories c ON a.categorie_id = c.id
            WHERE l.user_id = :user_id AND l.statut = 'active'
            GROUP BY l.id, c.id
            ORDER BY l.date_modification DESC";
    
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $req->execute();
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

// Utilisation
$breakdown = getListesWithCategoryBreakdown($user_id);

?>

<!-- LISTES AVEC RÉPARTITION PAR CATÉGORIE -->
<h2>Répartition des Articles par Catégorie</h2>
<ul>
<?php foreach ($breakdown as $item): ?>
    <li>
        <?php echo htmlspecialchars($item['titre']); ?> - 
        <?php echo htmlspecialchars($item['categorie'] ?? 'Sans catégorie'); ?>
        (<?php echo $item['nombre_articles']; ?> articles)
    </li>
<?php endforeach; ?>
</ul>

<?php

// ============================================
// BONNES PRATIQUES
// ============================================

/*
 * ✅ BONNES PRATIQUES APPLIQUÉES:
 * 
 * 1. **Prepared Statements**: Toutes les requêtes utilisent PDO::prepare()
 *    - Protège contre les injections SQL
 *    - Améliore la performance avec les requêtes répétées
 * 
 * 2. **Jointures Appropriées**: 
 *    - LEFT JOIN pour les données optionnelles (articles, recettes)
 *    - INNER JOIN pour les relations obligatoires
 * 
 * 3. **GROUP BY avec COUNT DISTINCT**:
 *    - Évite les doublons dans les comptages
 *    - Utilisé pour les statistiques agrégées
 * 
 * 4. **Indexes sur les colonnes FK**:
 *    - Améliore la performance des jointures
 *    - Défini dans la structure de la BDD
 * 
 * 5. **Logique métier dans les Contrôleurs**:
 *    - Les requêtes complexes restent en une seule requête SQL
 *    - Évite les N+1 queries
 * 
 * 6. **Cachage des Statistiques** (À implémenter):
 *    - Mettre en cache les statistiques globales
 *    - Invalider le cache lors de mises à jour
 */

?>
