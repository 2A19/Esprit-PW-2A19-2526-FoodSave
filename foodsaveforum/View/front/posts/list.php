<?php
$displayPosts = $paginatedPosts ?? $posts;
$currentPage = isset($currentPage) ? (int) $currentPage : 1;
$totalPages = isset($totalPages) ? (int) $totalPages : 1;
$selectedCategory = $selectedCategory ?? ($_GET['category'] ?? '');

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
                <option value="Recettes" <?php echo $selectedCategory === 'Recettes' ? 'selected' : ''; ?>>🍳 Recettes</option>
                <option value="Astuces" <?php echo $selectedCategory === 'Astuces' ? 'selected' : ''; ?>>💡 Astuces</option>
                <option value="Questions" <?php echo $selectedCategory === 'Questions' ? 'selected' : ''; ?>>❓ Questions</option>
                <option value="Conseils" <?php echo $selectedCategory === 'Conseils' ? 'selected' : ''; ?>>📋 Conseils</option>
                <option value="Autre" <?php echo $selectedCategory === 'Autre' ? 'selected' : ''; ?>>🔖 Autre</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
        </form>
    </div>

    <?php if (empty($displayPosts)): ?>
        <div class="empty-state">
            <p>Aucun post trouvé. Soyez le premier à créer un post! 🚀</p>
        </div>
    <?php else: ?>
        <div class="posts-list" id="posts-list">
            <?php foreach ($displayPosts as $post): ?>
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

                    <div class="post-reactions">
                        <div class="reactions-group">
                            <button class="btn-reaction btn-like <?php echo ($post['user_reaction'] ?? null) === 'like' ? 'active' : ''; ?>" 
                                    data-post-id="<?php echo $post['id_post']; ?>" 
                                    data-type="like"
                                    title="J'aime">
                                👍 <span class="reaction-count"><?php echo $post['likes_stats']['likes']; ?></span>
                            </button>
                            <button class="btn-reaction btn-dislike <?php echo ($post['user_reaction'] ?? null) === 'dislike' ? 'active' : ''; ?>" 
                                    data-post-id="<?php echo $post['id_post']; ?>" 
                                    data-type="dislike"
                                    title="Je n'aime pas">
                                👎 <span class="reaction-count"><?php echo $post['likes_stats']['dislikes']; ?></span>
                            </button>
                        </div>
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
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php
                $categoryParam = $selectedCategory !== '' ? '&category=' . urlencode($selectedCategory) : '';
                $previousPage = max(1, $currentPage - 1);
                $nextPage = min($totalPages, $currentPage + 1);
                ?>
                <a class="page-link <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>" href="index.php?action=posts<?php echo $categoryParam; ?>&page=<?php echo $previousPage; ?>">← Précédent</a>
                <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                    <a class="page-link <?php echo $page === $currentPage ? 'active' : ''; ?>" href="index.php?action=posts<?php echo $categoryParam; ?>&page=<?php echo $page; ?>">
                        <?php echo $page; ?>
                    </a>
                <?php endfor; ?>
                <a class="page-link <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>" href="index.php?action=posts<?php echo $categoryParam; ?>&page=<?php echo $nextPage; ?>">Suivant →</a>
            </div>
        <?php endif; ?>
        <div class="empty-state" id="search-empty-state" style="display: none; margin-top: 14px;">
            <p>Aucun post ne correspond à votre recherche.</p>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('.search-input');
    const postsList = document.getElementById('posts-list');

    if (!searchInput || !postsList) {
        return;
    }

    const postCards = Array.from(postsList.querySelectorAll('.post-card'));
    const emptyState = document.getElementById('search-empty-state');

    const runFilter = function () {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        postCards.forEach(function (card) {
            const titleElement = card.querySelector('.post-header h3 a');
            const title = titleElement ? titleElement.textContent.toLowerCase() : '';
            const matches = query === '' || title.includes(query);

            card.style.display = matches ? '' : 'none';
            if (matches) {
                visibleCount++;
            }
        });

        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    };

    searchInput.addEventListener('input', runFilter);
});
</script>
