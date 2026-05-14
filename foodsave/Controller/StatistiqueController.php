<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../Model/Article.php';

class StatistiqueController {
    private $articleModel;

    public function __construct() {
        $this->articleModel = new Article();
    }

    public function evolutionArticles() {
        $stats = $this->articleModel->getStatsEvolutionMensuelle();
        require_once __DIR__ . '/../View/Back/blog/stats_evolution.php';
    }
}
?>