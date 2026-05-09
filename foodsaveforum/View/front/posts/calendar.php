<?php
require_once dirname(dirname(dirname(__DIR__))) . '/translations.php';

// Définir la langue par défaut
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr';
}
$lang = $_SESSION['lang'];
$t = getTranslations($lang);

$calendarPosts = $calendarPosts ?? [];
$postsByDate = [];

foreach ($calendarPosts as $post) {
    $dateKey = date('Y-m-d', strtotime($post['date_creation']));
    if (!isset($postsByDate[$dateKey])) {
        $postsByDate[$dateKey] = [];
    }
    $postsByDate[$dateKey][] = $post;
}

$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
$year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

if ($month < 1 || $month > 12) {
    $month = (int) date('n');
}
if ($year < 2020 || $year > 2035) {
    $year = (int) date('Y');
}

$firstDay = new DateTime(sprintf('%04d-%02d-01', $year, $month));
$daysInMonth = (int) $firstDay->format('t');
$firstWeekday = (int) $firstDay->format('N'); // 1 (Mon) to 7 (Sun)

$prevMonthDate = (clone $firstDay)->modify('-1 month');
$nextMonthDate = (clone $firstDay)->modify('+1 month');
?>


<section class="posts-calendar">
    <div class="calendar-header">
        <h2><?php echo $t['calendar_title']; ?></h2>
        <div class="calendar-nav">
            <a class="btn-small btn-secondary" href="index.php?action=calendar&month=<?php echo (int) $prevMonthDate->format('n'); ?>&year=<?php echo (int) $prevMonthDate->format('Y'); ?>&lang=<?php echo $lang; ?>">←</a>
            <span class="calendar-month-label"><?php echo $t['months'][$month] . ' ' . $year; ?></span>
            <a class="btn-small btn-secondary" href="index.php?action=calendar&month=<?php echo (int) $nextMonthDate->format('n'); ?>&year=<?php echo (int) $nextMonthDate->format('Y'); ?>&lang=<?php echo $lang; ?>">→</a>
        </div>
    </div>

    <div class="calendar-grid">
        <?php foreach ($t['weekdays'] as $weekday): ?>
            <div class="calendar-weekday"><?php echo $weekday; ?></div>
        <?php endforeach; ?>

        <?php for ($blank = 1; $blank < $firstWeekday; $blank++): ?>
            <div class="calendar-day empty"></div>
        <?php endfor; ?>

        <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
            <?php
            $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dayPosts = $postsByDate[$dateKey] ?? [];
            ?>
            <div class="calendar-day">
                <div class="day-number"><?php echo $day; ?></div>
                <?php if (!empty($dayPosts)): ?>
                    <div class="day-post-count"><?php echo count($dayPosts); ?> <?php echo $t['posts']; ?></div>
                    <div class="day-post-list">
                        <?php foreach (array_slice($dayPosts, 0, 2) as $post): ?>
                            <a href="index.php?action=view&id=<?php echo $post['id_post']; ?>" class="day-post-item">
                                <?php echo htmlspecialchars(mb_strimwidth($post['titre'], 0, 28, '...')); ?>
                            </a>
                        <?php endforeach; ?>
                        <?php if (count($dayPosts) > 2): ?>
                            <span class="day-post-more">+<?php echo count($dayPosts) - 2; ?> <?php echo $t['more']; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</section>
