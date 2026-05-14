<?php
class Article {
    private $conn;
    private $table = 'articles';

    public function __construct() {
        $this->conn = config::getConnexion();
    }

    // ========== MÉTHODES EXISTANTES ==========
    
    public function getAllPublished() {
        $query = "SELECT * FROM " . $this->table . " WHERE statut = 'publié' ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($article) {
            $this->incrementViews($id);
        }
        return $article;
    }

    private function incrementViews($id) {
        $query = "UPDATE " . $this->table . " SET vue = vue + 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public function getByCategorie($categorie) {
        $query = "SELECT * FROM " . $this->table . " WHERE statut = 'publié' AND categorie = :categorie ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categorie', $categorie);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== MÉTHODES POUR LE CRUD ==========

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function countPublished() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut = 'publié'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function countDrafts() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut = 'brouillon'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function totalViews() {
        $query = "SELECT SUM(vue) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // ===== CRUD AVEC VIDÉO YOUTUBE =====
    
    public function create($titre, $categorie, $resume, $contenu, $image, $statut, $video_url = null) {
        $query = "INSERT INTO " . $this->table . " (titre, categorie, resume, contenu, image, statut, video_url) 
                  VALUES (:titre, :categorie, :resume, :contenu, :image, :statut, :video_url)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':categorie', $categorie);
        $stmt->bindParam(':resume', $resume);
        $stmt->bindParam(':contenu', $contenu);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':video_url', $video_url);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $titre, $categorie, $resume, $contenu, $image, $statut, $video_url = null) {
        // Si l'image est une chaîne vide, on la met à NULL en BDD
        $imageValue = ($image === '') ? null : $image;
        
        if ($imageValue !== null && $video_url !== null && $video_url !== '') {
            $query = "UPDATE " . $this->table . " 
                      SET titre = :titre, categorie = :categorie, resume = :resume, 
                          contenu = :contenu, image = :image, statut = :statut, video_url = :video_url
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':image', $imageValue);
            $stmt->bindParam(':video_url', $video_url);
        } 
        elseif ($imageValue !== null) {
            $query = "UPDATE " . $this->table . " 
                      SET titre = :titre, categorie = :categorie, resume = :resume, 
                          contenu = :contenu, image = :image, statut = :statut
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':image', $imageValue);
        } 
        elseif ($video_url !== null && $video_url !== '') {
            $query = "UPDATE " . $this->table . " 
                      SET titre = :titre, categorie = :categorie, resume = :resume, 
                          contenu = :contenu, statut = :statut, video_url = :video_url
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':video_url', $video_url);
        } 
        else {
            $query = "UPDATE " . $this->table . " 
                      SET titre = :titre, categorie = :categorie, resume = :resume, 
                          contenu = :contenu, statut = :statut, image = NULL
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':categorie', $categorie);
        $stmt->bindParam(':resume', $resume);
        $stmt->bindParam(':contenu', $contenu);
        $stmt->bindParam(':statut', $statut);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ========== MÉTHODES POUR LA PAGINATION ==========

    public function getArticlesPagines($page = 1, $limit = 6, $order = 'DESC') {
        $offset = ($page - 1) * $limit;
        $order = ($order == 'ASC') ? 'ASC' : 'DESC';
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE statut = 'publié' 
                  ORDER BY created_at $order 
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function countTotalArticles() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut = 'publié'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // ========== RECHERCHE ==========

    public function rechercherParTitre($search) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE statut = 'publié' 
                  AND titre LIKE :search 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%" . $search . "%";
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== STATISTIQUES ==========

    public function getStatsEvolutionMensuelle() {
        $query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as mois,
                    DATE_FORMAT(created_at, '%b %Y') as mois_format,
                    COUNT(*) as total
                  FROM " . $this->table . " 
                  WHERE statut = 'publié'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 11 MONTH)
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY mois ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $moisComplets = [];
        for($i = 11; $i >= 0; $i--) {
            $mois = date('Y-m', strtotime("-$i months"));
            $moisFormat = date('M Y', strtotime("-$i months"));
            $moisComplets[$mois] = [
                'label' => $moisFormat,
                'total' => 0
            ];
        }
        
        foreach($results as $r) {
            if(isset($moisComplets[$r['mois']])) {
                $moisComplets[$r['mois']]['total'] = $r['total'];
            }
        }
        
        $moisLabels = [];
        $moisData = [];
        foreach($moisComplets as $mois) {
            $moisLabels[] = $mois['label'];
            $moisData[] = $mois['total'];
        }
        
        $totalArticles = array_sum($moisData);
        $moyenneParMois = round($totalArticles / 12, 1);
        $meilleurMois = max($moisData);
        $meilleurMoisIndex = array_search($meilleurMois, $moisData);
        $meilleurMoisLabel = ($meilleurMoisIndex !== false) ? $moisLabels[$meilleurMoisIndex] : '-';
        $evolution = (end($moisData) - $moisData[0]);
        
        return [
            'labels' => $moisLabels,
            'data' => $moisData,
            'total' => $totalArticles,
            'moyenne' => $moyenneParMois,
            'meilleur_mois' => $meilleurMois,
            'meilleur_mois_label' => $meilleurMoisLabel,
            'evolution' => $evolution,
            'tendance' => ($evolution > 0) ? 'hausse' : (($evolution < 0) ? 'baisse' : 'stable')
        ];
    }

    // ========== MÉTHODES POUR LA NEWSLETTER ==========

    public function getSubscribers() {
        $query = "SELECT email, nom FROM newsletter_subscribers WHERE statut = 'actif'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countSubscribers() {
        $query = "SELECT COUNT(*) as total FROM newsletter_subscribers WHERE statut = 'actif'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>