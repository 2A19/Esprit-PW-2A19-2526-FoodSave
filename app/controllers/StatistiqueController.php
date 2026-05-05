<?php
// app/controllers/StatistiqueController.php

require_once __DIR__ . '/../models/Article.php';

class StatistiqueController {
    private $articleModel;

    public function __construct() {
        $this->articleModel = new Article();
    }

    /**
     * Affiche la page des statistiques d'évolution des articles
     */
    public function evolutionArticles() {
        // Récupérer les données statistiques
        $stats = $this->articleModel->getStatsEvolutionMensuelle();
        
        // Passer les données à la vue
        require_once __DIR__ . '/../views/back/blog/stats_evolution.php';
    }
}
?>