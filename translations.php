<?php
/**
 * FoodSave Forum — Traductions PHP
 * Utilisé par : View/front/posts/list.php et View/front/posts/calendar.php
 */

function getTranslations(string $lang = 'fr'): array {
    $translations = [
        'fr' => [
            // Calendrier
            'calendar_title' => 'Calendrier des posts',
            'months' => [
                1  => 'Janvier',
                2  => 'Février',
                3  => 'Mars',
                4  => 'Avril',
                5  => 'Mai',
                6  => 'Juin',
                7  => 'Juillet',
                8  => 'Août',
                9  => 'Septembre',
                10 => 'Octobre',
                11 => 'Novembre',
                12 => 'Décembre',
            ],
            'weekdays' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            'posts'    => 'post(s)',
            'more'     => 'de plus',

            // Liste posts
            'all_posts'       => 'Tous les posts',
            'new_post'        => 'Créer un post',
            'search'          => 'Rechercher...',
            'no_posts'        => 'Aucun post disponible.',
            'read_more'       => 'Lire la suite',
            'by'              => 'Par',
            'on'              => 'le',
            'comments'        => 'commentaire(s)',
            'likes'           => 'j\'aime',
            'category'        => 'Catégorie',
            'all_categories'  => 'Toutes les catégories',
            'prev'            => '← Précédent',
            'next'            => 'Suivant →',
        ],

        'en' => [
            // Calendar
            'calendar_title' => 'Posts Calendar',
            'months' => [
                1  => 'January',
                2  => 'February',
                3  => 'March',
                4  => 'April',
                5  => 'May',
                6  => 'June',
                7  => 'July',
                8  => 'August',
                9  => 'September',
                10 => 'October',
                11 => 'November',
                12 => 'December',
            ],
            'weekdays' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'posts'    => 'post(s)',
            'more'     => 'more',

            // Posts list
            'all_posts'       => 'All posts',
            'new_post'        => 'Create a post',
            'search'          => 'Search...',
            'no_posts'        => 'No posts available.',
            'read_more'       => 'Read more',
            'by'              => 'By',
            'on'              => 'on',
            'comments'        => 'comment(s)',
            'likes'           => 'likes',
            'category'        => 'Category',
            'all_categories'  => 'All categories',
            'prev'            => '← Previous',
            'next'            => 'Next →',
        ],
    ];

    return $translations[$lang] ?? $translations['fr'];
}
