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
        
        $this->incrementViews($id);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    // ========== NOUVELLES MÉTHODES POUR LE CRUD ==========

    // Récupérer TOUS les articles (admin)
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Compter le nombre total d'articles
    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Compter les articles publiés
    public function countPublished() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut = 'publié'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Compter les brouillons
    public function countDrafts() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut = 'brouillon'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Total des vues
    public function totalViews() {
        $query = "SELECT SUM(vue) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Créer un article
    public function create($titre, $categorie, $resume, $contenu, $image, $statut) {
        $query = "INSERT INTO " . $this->table . " (titre, categorie, resume, contenu, image, statut) 
                  VALUES (:titre, :categorie, :resume, :contenu, :image, :statut)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':categorie', $categorie);
        $stmt->bindParam(':resume', $resume);
        $stmt->bindParam(':contenu', $contenu);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':statut', $statut);
        return $stmt->execute();
    }

    // Modifier un article
    public function update($id, $titre, $categorie, $resume, $contenu, $image, $statut) {
        if ($image) {
            $query = "UPDATE " . $this->table . " 
                      SET titre = :titre, categorie = :categorie, resume = :resume, 
                          contenu = :contenu, image = :image, statut = :statut 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':image', $image);
        } else {
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

    // Supprimer un article
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>