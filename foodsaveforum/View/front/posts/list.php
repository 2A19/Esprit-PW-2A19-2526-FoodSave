<?php
require_once dirname(dirname(dirname(__DIR__))) . '/translations.php';
$lang = $_SESSION['lang'] ?? 'fr';
$t = getTranslations($lang);

$displayPosts = $paginatedPosts ?? $posts;
$currentPage = isset($currentPage) ? (int) $currentPage : 1;
$totalPages = isset($totalPages) ? (int) $totalPages : 1;
$selectedCategory = $selectedCategory ?? ($_GET['category'] ?? '');

$membersCount = count(array_unique(array_map(function ($post) {
    return $post['id_utilisateur'];
}, $posts)));
$topicsCount = count($posts);
$messagesCount = max($topicsCount, $topicsCount * 2);

// Categories with translations
$categories = ['Recettes', 'Astuces', 'Questions', 'Conseils', 'Autre'];
$categoryDescriptions = [
    'fr' => [
        'Recettes' => "Recettes anti-gaspi et cuisine durable.",
        'Astuces' => "Astuces pratiques pour mieux conserver.",
        'Questions' => "Questions et entraide entre membres.",
        'Conseils' => "Bonnes pratiques de consommation.",
        'Autre' => "Sujets divers autour de l'anti-gaspillage."
    ],
    'en' => [
        'Recettes' => "Anti-waste recipes and sustainable cooking.",
        'Astuces' => "Practical tips to better preserve food.",
        'Questions' => "Questions and mutual help between members.",
        'Conseils' => "Good consumption practices.",
        'Autre' => "Various topics around anti-waste."
    ]
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
            <h1><?php echo $lang === 'en' ? 'Share your best ideas against waste' : 'Partagez vos meilleures idées contre le gaspillage'; ?></h1>
            <p><?php echo $lang === 'en' ? 'Post recipes, tips and questions to inspire the FoodSave community.' : 'Publiez des recettes, astuces et questions pour inspirer la communauté FoodSave.'; ?></p>
        </div>
        <a href="index.php?action=create" class="btn btn-primary hero-cta"><?php echo $lang === 'en' ? '+ Create a Post' : '+ Créer un Post'; ?></a>
    </div>
</section>

<section class="forum-kpi-row">
    <article class="kpi-card">
        <div class="kpi-icon">👥</div>
        <div>
            <div class="kpi-number"><?php echo $membersCount; ?></div>
            <div class="kpi-label"><?php echo $lang === 'en' ? 'Members' : 'Membres'; ?></div>
        </div>
    </article>
    <article class="kpi-card">
        <div class="kpi-icon">💬</div>
        <div>
            <div class="kpi-number"><?php echo $topicsCount; ?></div>
            <div class="kpi-label"><?php echo $lang === 'en' ? 'Topics' : 'Sujets'; ?></div>
        </div>
    </article>
    <article class="kpi-card">
        <div class="kpi-icon">📌</div>
        <div>
            <div class="kpi-number"><?php echo $messagesCount; ?></div>
            <div class="kpi-label"><?php echo $lang === 'en' ? 'Messages' : 'Messages'; ?></div>
        </div>
    </article>
</section>

<section class="forum-panels">
    <article class="forum-panel">
        <h3><?php echo $lang === 'en' ? 'Categories' : 'Catégories'; ?></h3>
        <div class="category-list">
            <?php foreach ($categories as $cat): ?>
                <div class="category-row">
                    <div class="category-left">
                        <span class="cat-round-icon"><?php echo $categoryIcons[$cat]; ?></span>
                        <div>
                            <strong><?php echo $cat; ?></strong>
                            <p><?php echo $categoryDescriptions[$lang][$cat]; ?></p>
                        </div>
                    </div>
                    <span class="category-total"><?php echo $categoryCountMap[$cat] ?? 0; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
    <article class="forum-panel">
        <h3><?php echo $lang === 'en' ? 'Recent topics' : 'Sujets récents'; ?></h3>
        <div class="recent-subjects">
            <?php foreach (array_slice($posts, 0, 6) as $recentPost): ?>
                <a href="index.php?action=view&id=<?php echo $recentPost['id_post']; ?>" class="recent-subject-item">
                    <div class="recent-subject-title"><?php echo htmlspecialchars($recentPost['titre']); ?></div>
                    <div class="recent-subject-meta"><?php echo $lang === 'en' ? 'by ' : 'par '; ?><?php echo htmlspecialchars($recentPost['auteur_nom'] ?: ($recentPost['auteur_email'] ?? 'Utilisateur')); ?></div>
                </a>
            <?php endforeach; ?>
            <a href="#all-subjects" class="see-all-link"><?php echo $lang === 'en' ? 'See all topics' : 'Voir tous les sujets'; ?></a>
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
                <option value=""><?php echo $lang === 'en' ? '-- All categories --' : '-- Toutes les catégories --'; ?></option>
                <option value="Recettes" <?php echo $selectedCategory === 'Recettes' ? 'selected' : ''; ?>>🍳 Recettes</option>
                <option value="Astuces" <?php echo $selectedCategory === 'Astuces' ? 'selected' : ''; ?>>💡 Astuces</option>
                <option value="Questions" <?php echo $selectedCategory === 'Questions' ? 'selected' : ''; ?>>❓ Questions</option>
                <option value="Conseils" <?php echo $selectedCategory === 'Conseils' ? 'selected' : ''; ?>>📋 Conseils</option>
                <option value="Autre" <?php echo $selectedCategory === 'Autre' ? 'selected' : ''; ?>>🔖 Autre</option>
            </select>
            <button type="submit" class="btn btn-secondary"><?php echo $lang === 'en' ? 'Filter' : 'Filtrer'; ?></button>
        </form>
    </div>

    <?php if (empty($displayPosts)): ?>
        <div class="empty-state">
            <p><?php echo $lang === 'en' ? 'No posts found. Be the first to create a post! 🚀' : 'Aucun post trouvé. Soyez le premier à créer un post! 🚀'; ?></p>
        </div>
    <?php else: ?>
        <div class="posts-list" id="posts-list">
            <?php foreach ($displayPosts as $post): ?>
                <div class="post-card entity-card">
                    <div class="post-header">
                        <h3>
                            <a href="index.php?action=view&id=<?php echo $post['id_post']; ?>">
                                <?php echo htmlspecialchars($post['titre']); ?>
                            </a>
                        </h3>
                        <span class="category-badge category-<?php echo strtolower($post['categorie']); ?>">
                            <?php echo htmlspecialchars($post['categorie']); ?>
                        </span>
                    </div>

                    <div class="post-meta">
                        <span class="author">👤 <?php echo htmlspecialchars($post['auteur_nom'] ?: ($post['auteur_email'] ?? 'Utilisateur')); ?></span>
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
                                    title="<?php echo $lang === 'en' ? 'Like' : 'J\'aime'; ?>">
                                👍 <span class="reaction-count"><?php echo $post['likes_stats']['likes']; ?></span>
                            </button>
                            <button class="btn-reaction btn-dislike <?php echo ($post['user_reaction'] ?? null) === 'dislike' ? 'active' : ''; ?>" 
                                    data-post-id="<?php echo $post['id_post']; ?>" 
                                    data-type="dislike"
                                    title="<?php echo $lang === 'en' ? 'Dislike' : 'Je n\'aime pas'; ?>">
                                👎 <span class="reaction-count"><?php echo $post['likes_stats']['dislikes']; ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="post-actions">
                        <a href="index.php?action=view&id=<?php echo $post['id_post']; ?>" class="btn btn-info">
                            💬 <?php echo $lang === 'en' ? 'View discussion' : 'Voir la discussion'; ?>
                        </a>
                        <a href="index.php?action=edit&id=<?php echo $post['id_post']; ?>" class="btn btn-warning">
                            ✏️ <?php echo $lang === 'en' ? 'Edit' : 'Modifier'; ?>
                        </a>
                        <a href="index.php?action=delete&id=<?php echo $post['id_post']; ?>" class="btn btn-danger" onclick="return confirm('<?php echo $lang === 'en' ? 'Are you sure?' : 'Êtes-vous sûr ?'; ?>');">
                            🗑️ <?php echo $lang === 'en' ? 'Delete' : 'Supprimer'; ?>
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
