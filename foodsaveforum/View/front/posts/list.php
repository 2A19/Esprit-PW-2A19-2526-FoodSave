<?php
$membersCount = count(array_unique(array_map(function ($post) {
    return $post['id_utilisateur'];
}, $posts)));
$topicsCount = count($posts);
$messagesCount = max($topicsCount, $topicsCount * 2);

$categories = ['Recettes', 'Astuces', 'Questions', 'Conseils', 'Autre'];
$categoryDescriptions = [
    'Recettes' => "Recettes anti-gaspi et cuisine durable.",
    'Astuces' => "Astuces pratiques pour mieux conserver.",
    'Questions' => "Questions et entraide entre membres.",
    'Conseils' => "Bonnes pratiques de consommation.",
    'Autre' => "Sujets divers autour de l'anti-gaspillage."
];
$categoryIcons = [
    'Recettes' => '🍳',
    'Astuces' => '💡',
    'Questions' => '❓',
    'Conseils' => '📋',
    'Autre' => '🔖'
];

$categoryCountMap = [];
foreach ($posts as $post) {
    $cat = $post['categorie'];
    if (!isset($categoryCountMap[$cat])) {
        $categoryCountMap[$cat] = 0;
    }
    $categoryCountMap[$cat]++;
}
?>

<section class="hero-banner">
    <div class="hero-inner">
        <div>
            <h1>Partagez vos meilleures idées contre le gaspillage</h1>
            <p>Publiez des recettes, astuces et questions pour inspirer la communauté FoodSave.</p>
        </div>
        <a href="index.php?action=create-post" class="btn btn-primary hero-cta">+ Créer un Post</a>
    </div>
</section>

<section class="forum-kpi-row">
    <article class="kpi-card">
        <div class="kpi-icon">👥</div>
        <div>
            <div class="kpi-number"><?php echo $membersCount; ?></div>
            <div class="kpi-label">Membres</div>
        </div>
    </article>
    <article class="kpi-card">
        <div class="kpi-icon">💬</div>
        <div>
            <div class="kpi-number"><?php echo $topicsCount; ?></div>
            <div class="kpi-label">Sujets</div>
        </div>
    </article>
    <article class="kpi-card">
        <div class="kpi-icon">📌</div>
        <div>
            <div class="kpi-number"><?php echo $messagesCount; ?></div>
            <div class="kpi-label">Messages</div>
        </div>
    </article>
</section>

<section class="forum-panels">
    <article class="forum-panel">
        <h3>Catégories</h3>
        <div class="category-list">
            <?php foreach ($categories as $cat): ?>
                <div class="category-row">
                    <div class="category-left">
                        <span class="cat-round-icon"><?php echo $categoryIcons[$cat]; ?></span>
                        <div>
                            <strong><?php echo $cat; ?></strong>
                            <p><?php echo $categoryDescriptions[$cat]; ?></p>
                        </div>
                    </div>
                    <span class="category-total"><?php echo $categoryCountMap[$cat] ?? 0; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
    <article class="forum-panel">
        <h3>Sujets récents</h3>
        <div class="recent-subjects">
            <?php foreach (array_slice($posts, 0, 6) as $recentPost): ?>
                <a href="index.php?action=view-post&id=<?php echo $recentPost['id_post']; ?>" class="recent-subject-item">
                    <div class="recent-subject-title"><?php echo htmlspecialchars($recentPost['titre']); ?></div>
                    <div class="recent-subject-meta">par Utilisateur #<?php echo $recentPost['id_utilisateur']; ?></div>
                </a>
            <?php endforeach; ?>
            <a href="#all-subjects" class="see-all-link">Voir tous les sujets</a>
        </div>
    </article>
</section>

<div class="posts-container">
    <div class="posts-header">
        <h2 id="all-subjects">Forum FoodSave 🌱</h2>
    </div>

    <div class="filters">
        <form method="GET" class="filter-form">
            <input type="hidden" name="action" value="posts">
            <select name="category" id="category">
                <option value="">-- Toutes les catégories --</option>
                <option value="Recettes">🍳 Recettes</option>
                <option value="Astuces">💡 Astuces</option>
                <option value="Questions">❓ Questions</option>
                <option value="Conseils">📋 Conseils</option>
                <option value="Autre">🔖 Autre</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
        </form>
    </div>

    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <p>Aucun post trouvé. Soyez le premier à créer un post! 🚀</p>
        </div>
    <?php else: ?>
        <div class="posts-list">
            <?php foreach ($posts as $post): ?>
                <div class="post-card entity-card">
                    <div class="post-header">
                        <h3>
                            <a href="index.php?action=view-post&id=<?php echo $post['id_post']; ?>">
                                <?php echo htmlspecialchars($post['titre']); ?>
                            </a>
                        </h3>
                        <span class="category-badge category-<?php echo strtolower($post['categorie']); ?>">
                            <?php echo htmlspecialchars($post['categorie']); ?>
                        </span>
                    </div>

                    <div class="post-meta">
                        <span class="author">👤 Utilisateur #<?php echo $post['id_utilisateur']; ?></span>
                        <span class="date">📅 <?php echo date('d/m/Y H:i', strtotime($post['date_creation'])); ?></span>
                    </div>

                    <div class="post-content">
                        <p><?php echo htmlspecialchars(substr($post['contenu'], 0, 200)) . '...'; ?></p>
                    </div>

                    <div class="post-actions">
                        <a href="index.php?action=view-post&id=<?php echo $post['id_post']; ?>" class="btn btn-info">
                            💬 Voir la discussion
                        </a>
                        <a href="index.php?action=edit-post&id=<?php echo $post['id_post']; ?>" class="btn btn-warning">
                            ✏️ Modifier
                        </a>
                        <a href="index.php?action=delete-post&id=<?php echo $post['id_post']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr ?');">
                            🗑️ Supprimer
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
