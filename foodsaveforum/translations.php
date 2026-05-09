<?php
/**
 * Système centralisé de traductions
 * Utilisation: $t = getTranslations($lang)['key']
 */

function getTranslations($lang = 'fr') {
    $translations = [
        'fr' => [
            // Navigation
            'home' => 'Accueil',
            'categories' => 'Catégories',
            'recent' => 'Sujets récents',
            'calendar' => 'Calendrier',
            'create_post' => 'Créer un post',
            'administration' => 'Administration',
            'login' => 'Connexion',
            'signup' => 'S\'inscrire',
            'search' => 'Rechercher...',
            
            // Calendrier
            'calendar_title' => 'Calendrier des posts',
            'posts' => 'post(s)',
            'more' => 'autres',
            'months' => [
                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
            ],
            'weekdays' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            
            // Posts
            'all_posts' => 'Tous les posts',
            'no_posts' => 'Aucun post trouvé',
            'author' => 'Auteur',
            'date' => 'Date',
            'comments' => 'Commentaires',
            'like' => 'J\'aime',
            'likes' => 'J\'aime',
            'add_comment' => 'Ajouter un commentaire',
            'edit' => 'Éditer',
            'delete' => 'Supprimer',
            'save' => 'Enregistrer',
            'cancel' => 'Annuler',
            'back' => 'Retour',
            
            // Création/édition de post
            'create_new_post' => 'Créer un nouveau post',
            'edit_post' => 'Éditer le post',
            'title' => 'Titre',
            'content' => 'Contenu',
            'category' => 'Catégorie',
            'publish' => 'Publier',
            'draft' => 'Brouillon',
            
            // Messages
            'loading' => 'Chargement...',
            'error' => 'Erreur',
            'success' => 'Succès',
            'warning' => 'Attention',
            'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer ?',
            
            // Footer
            'footer' => '© 2026 FoodSave - Plateforme Anti-Gaspillage. Tous droits réservés.',
        ],
        'en' => [
            // Navigation
            'home' => 'Home',
            'categories' => 'Categories',
            'recent' => 'Recent Topics',
            'calendar' => 'Calendar',
            'create_post' => 'Create Post',
            'administration' => 'Administration',
            'login' => 'Login',
            'signup' => 'Sign Up',
            'search' => 'Search...',
            
            // Calendrier
            'calendar_title' => 'Posts Calendar',
            'posts' => 'post(s)',
            'more' => 'more',
            'months' => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ],
            'weekdays' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            
            // Posts
            'all_posts' => 'All Posts',
            'no_posts' => 'No posts found',
            'author' => 'Author',
            'date' => 'Date',
            'comments' => 'Comments',
            'like' => 'Like',
            'likes' => 'Likes',
            'add_comment' => 'Add a comment',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'back' => 'Back',
            
            // Création/édition de post
            'create_new_post' => 'Create New Post',
            'edit_post' => 'Edit Post',
            'title' => 'Title',
            'content' => 'Content',
            'category' => 'Category',
            'publish' => 'Publish',
            'draft' => 'Draft',
            
            // Messages
            'loading' => 'Loading...',
            'error' => 'Error',
            'success' => 'Success',
            'warning' => 'Warning',
            'confirm_delete' => 'Are you sure you want to delete?',
            
            // Footer
            'footer' => '© 2026 FoodSave - Anti-Waste Platform. All rights reserved.',
        ]
    ];
    
    return $translations[$lang] ?? $translations['fr'];
}

function t($key, $lang = 'fr', $default = '') {
    $translations = getTranslations($lang);
    return $translations[$key] ?? $default;
}
?>
