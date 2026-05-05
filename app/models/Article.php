<?php
// app/models/Article.php

require_once __DIR__ . '/../../config/database.php';

class Article {
    private $conn;
    private $table = 'articles';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
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
        
        // N'incrémenter que si l'article existe
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
        return $this->conn->lastInsertId(); // ← Retourne l'ID inséré
    }
    return false;
}

    public function update($id, $titre, $categorie, $resume, $contenu, $image, $statut, $video_url = null) {
        if ($image && $video_url !== null) {
            // Image ET vidéo
            $query = "UPDATE " . $this->table . " 
                      SET titre = :titre, categorie = :categorie, resume = :resume, 
                          contenu = :contenu, image = :image, statut = :statut, video_url = :video_url
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':video_url', $video_url);
        } elseif ($image) {
            // Image seule
            $query = "UPDATE " . $this->table . " 
                      SET titre = :titre, categorie = :categorie, resume = :resume, 
                          contenu = :contenu, image = :image, statut = :statut
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':image', $image);
        } elseif ($video_url !== null) {
            // Vidéo seule
            $query = "UPDATE " . $this->table . " 
                      SET titre = :titre, categorie = :categorie, resume = :resume, 
                          contenu = :contenu, statut = :statut, video_url = :video_url
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':video_url', $video_url);
        } else {
            // Ni image ni vidéo
            $query = "UPDATE " . $this->table . " 
                      SET titre = :titre, categorie = :categorie, resume = :resume, 
                          contenu = :contenu, statut = :statut
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

    // ========== MÉTHODES AVEC JOINTURE AVIS ==========

    public function getArticleWithAvis($id) {
        $query = "SELECT a.*, 
                  (SELECT COUNT(*) FROM avis WHERE article_id = a.id AND statut = 'approuvé') as nb_avis,
                  (SELECT AVG(note) FROM avis WHERE article_id = a.id AND statut = 'approuvé') as note_moyenne
                  FROM articles a
                  WHERE a.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTopRated($limit = 3) {
        $query = "SELECT a.*, AVG(av.note) as note_moyenne, COUNT(av.id) as nb_avis
                  FROM articles a
                  LEFT JOIN avis av ON a.id = av.article_id AND av.statut = 'approuvé'
                  WHERE a.statut = 'publié'
                  GROUP BY a.id
                  ORDER BY note_moyenne DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== MÉTHODES POUR LA PAGINATION ET LE TRI ==========

    /**
     * Récupérer les articles avec pagination et tri
     * @param int $page Numéro de la page
     * @param int $limit Nombre d'articles par page
     * @param string $order Ordre de tri ('DESC' = plus récents, 'ASC' = plus anciens)
     * @return array Les articles de la page
     */
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
    
    // ========== STATISTIQUE : ÉVOLUTION MENSUELLE DES ARTICLES ==========

    /**
     * Récupère le nombre d'articles publiés par mois (12 derniers mois)
     * Retourne les données formatées pour Chart.js
     */
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
        
        // Créer un tableau avec tous les 12 derniers mois (même ceux sans article)
        $moisLabels = [];
        $moisData = [];
        $moisComplets = [];
        
        // Générer les 12 derniers mois
        for($i = 11; $i >= 0; $i--) {
            $mois = date('Y-m', strtotime("-$i months"));
            $moisFormat = date('M Y', strtotime("-$i months"));
            $moisComplets[$mois] = [
                'label' => $moisFormat,
                'total' => 0
            ];
        }
        
        // Remplir avec les données réelles
        foreach($results as $r) {
            if(isset($moisComplets[$r['mois']])) {
                $moisComplets[$r['mois']]['total'] = $r['total'];
            }
        }
        
        // Construire les tableaux pour le graphique
        foreach($moisComplets as $mois) {
            $moisLabels[] = $mois['label'];
            $moisData[] = $mois['total'];
        }
        
        // Calculer les stats supplémentaires
        $totalArticles = array_sum($moisData);
        $moyenneParMois = round($totalArticles / 12, 1);
        $meilleurMois = max($moisData);
        $meilleurMoisIndex = array_search($meilleurMois, $moisData);
        $meilleurMoisLabel = ($meilleurMoisIndex !== false) ? $moisLabels[$meilleurMoisIndex] : '-';
        $dernierMois = end($moisData);
        $evolution = ($dernierMois - $moisData[0]);
        
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

    public function countTotalArticles() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut = 'publié'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // ========== ARTICLES SIMILAIRES (RECOMMANDATION) ==========
    
    /**
     * Récupère les articles similaires à un article donné
     * @param int $article_id L'ID de l'article courant
     * @param int $limit Nombre d'articles similaires à retourner
     * @return array Liste des articles similaires
     */
    public function getSimilarArticles($article_id, $limit = 3) {
        // Récupérer l'article courant pour connaître sa catégorie
        $currentArticle = $this->getById($article_id);
        
        if (!$currentArticle) {
            return [];
        }
        
        $categorie = $currentArticle['categorie'];
        
        // Requête : articles de la même catégorie, exclus l'article courant
        $query = "SELECT id, titre, resume, image, vue, categorie, created_at
                  FROM " . $this->table . " 
                  WHERE statut = 'publié' 
                  AND id != :article_id 
                  AND categorie = :categorie
                  ORDER BY vue DESC, created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->bindParam(':categorie', $categorie);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si pas assez d'articles dans la même catégorie, compléter avec les plus vus
        if (count($results) < $limit) {
            $existingIds = [$article_id];
            foreach ($results as $r) {
                $existingIds[] = $r['id'];
            }
            $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
            
            $query2 = "SELECT id, titre, resume, image, vue, categorie, created_at
                       FROM " . $this->table . " 
                       WHERE statut = 'publié' 
                       AND id NOT IN ($placeholders)
                       ORDER BY vue DESC, created_at DESC
                       LIMIT :limit";
            
            $stmt2 = $this->conn->prepare($query2);
            foreach ($existingIds as $index => $id) {
                $stmt2->bindValue($index + 1, $id);
            }
            $stmt2->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt2->execute();
            $additional = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
            $results = array_merge($results, $additional);
            $results = array_slice($results, 0, $limit);
        }
        
        return $results;
    }

    // ========== MÉTHODES POUR LA NEWSLETTER ==========

    /**
     * Crée la table des abonnés si elle n'existe pas
     */
    public function createNewsletterTable() {
        $query = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(150) NOT NULL UNIQUE,
            nom VARCHAR(100),
            date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
            statut ENUM('actif', 'desactive') DEFAULT 'actif'
        )";
        $this->conn->exec($query);
    }

    /**
     * Récupère tous les abonnés actifs à la newsletter
     */
    public function getSubscribers() {
        $query = "SELECT email, nom FROM newsletter_subscribers WHERE statut = 'actif'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   /**
 * Ajoute un nouvel abonné
 */
public function addSubscriber($email, $nom = null) {
    // Créer la table si elle n'existe pas
    $this->createNewsletterTable();
    
    // Vérifier si l'email existe déjà
    $check = "SELECT id FROM newsletter_subscribers WHERE email = :email";
    $stmt = $this->conn->prepare($check);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        error_log("Newsletter - Email déjà existant: " . $email);
        return false; // Déjà inscrit
    }
    
    $query = "INSERT INTO newsletter_subscribers (email, nom) VALUES (:email, :nom)";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':nom', $nom);
    
    if($stmt->execute()) {
        error_log("Newsletter - Insertion réussie pour: " . $email);
        return true;
    } else {
        error_log("Newsletter - Erreur SQL: " . print_r($stmt->errorInfo(), true));
        return false;
    }
}

    /**
     * Compte le nombre d'abonnés actifs
     */
    public function countSubscribers() {
        $this->createNewsletterTable();
        $query = "SELECT COUNT(*) as total FROM newsletter_subscribers WHERE statut = 'actif'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>