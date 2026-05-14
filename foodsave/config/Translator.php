<?php
/**
 * Classe Translator - Gestion centralisée des traductions
 * Utilisation: $translator = Translator::getInstance();
 */
class Translator {
    private static $instance = null;
    private $currentLang = 'fr';
    private $translations = [
        'fr' => [
            // Navigation
            'nav_home' => 'Accueil',
            'nav_blog' => 'Blog',
            'nav_tips' => 'Conseils',
            'nav_recipes' => 'Recettes',
            'nav_login' => 'Connexion',
            'nav_register' => 'Inscription',
            
            // Blog
            'blog_title' => 'Blog FoodSave',
            'blog_subtitle' => 'Astuces et recettes anti-gaspillage',
            'reviews' => 'Avis',
            'write_review' => 'Écrire un avis',
            'newest' => 'Derniers articles',
            'oldest' => 'Plus anciens',
            'read_more' => 'Lire la suite',
            'view_all' => 'Voir tous les articles',
            'prev' => 'Précédent',
            'next' => 'Suivant',
            'page' => 'Page',
            'articles' => 'articles',
            'no_articles' => 'Aucun article trouvé',
        ],
        'en' => [
            // Navigation
            'nav_home' => 'Home',
            'nav_blog' => 'Blog',
            'nav_tips' => 'Tips',
            'nav_recipes' => 'Recipes',
            'nav_login' => 'Login',
            'nav_register' => 'Register',
            
            // Blog
            'blog_title' => 'FoodSave Blog',
            'blog_subtitle' => 'Tips and recipes to reduce food waste',
            'reviews' => 'Reviews',
            'write_review' => 'Write a review',
            'newest' => 'Latest articles',
            'oldest' => 'Oldest articles',
            'read_more' => 'Read more',
            'view_all' => 'View all articles',
            'prev' => 'Previous',
            'next' => 'Next',
            'page' => 'Page',
            'articles' => 'articles',
            'no_articles' => 'No articles found',
        ]
    ];

    private function __construct() {
        // Récupérer la langue de la session ou des paramètres
        if (isset($_GET['lang'])) {
            $this->currentLang = in_array($_GET['lang'], ['fr', 'en']) ? $_GET['lang'] : 'fr';
            $_SESSION['lang'] = $this->currentLang;
        } elseif (isset($_SESSION['lang'])) {
            $this->currentLang = $_SESSION['lang'];
        }
    }

    /**
     * Obtenir l'instance unique du Translator (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtenir la langue actuelle
     */
    public function getCurrentLang() {
        return $this->currentLang;
    }

    /**
     * Définir la langue actuelle
     */
    public function setLang($lang) {
        if (in_array($lang, ['fr', 'en'])) {
            $this->currentLang = $lang;
            $_SESSION['lang'] = $lang;
        }
    }

    /**
     * Traduire une clé
     */
    public function translate($key) {
        return $this->translations[$this->currentLang][$key] ?? $key;
    }

    /**
     * Alias pour translate
     */
    public function __invoke($key) {
        return $this->translate($key);
    }
}
?>
