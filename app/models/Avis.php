<?php
// app/models/Avis.php

require_once __DIR__ . '/../../config/database.php';

class Avis {
    private $conn;
    private $table = 'avis';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ========== MÉTHODES READ ==========

    // Récupérer tous les avis d'un article (avec limite optionnelle)
    public function getByArticleId($article_id, $limit = null) {
        $query = "SELECT a.*, CONCAT(u.prenom, ' ', u.nom) as user_name 
                  FROM " . $this->table . " a
                  JOIN user u ON a.user_id = u.id
                  WHERE a.article_id = :article_id AND a.statut = 'approuvé'
                  ORDER BY a.created_at DESC";
        
        if($limit) {
            $query .= " LIMIT " . intval($limit);
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer tous les avis (admin)
    public function getAll() {
        $query = "SELECT a.*, CONCAT(u.prenom, ' ', u.nom) as user_name, art.titre as article_titre
                  FROM " . $this->table . " a
                  JOIN user u ON a.user_id = u.id
                  JOIN articles art ON a.article_id = art.id
                  ORDER BY a.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un avis par son ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Compter les avis par article
    public function countByArticleId($article_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE article_id = :article_id AND statut = 'approuvé'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Calculer la note moyenne d'un article
    public function getAverageNote($article_id) {
        $query = "SELECT AVG(note) as moyenne FROM " . $this->table . " WHERE article_id = :article_id AND statut = 'approuvé'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['moyenne'] ?? 0, 1);
    }

    // ========== MÉTHODES CREATE ==========

    // Ajouter un avis
    public function create($article_id, $user_id, $contenu, $note, $statut) {
        $query = "INSERT INTO " . $this->table . " (article_id, user_id, contenu, note, statut) 
                  VALUES (:article_id, :user_id, :contenu, :note, :statut)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':contenu', $contenu);
        $stmt->bindParam(':note', $note);
        $stmt->bindParam(':statut', $statut);
        return $stmt->execute();
    }

    // ========== MÉTHODES UPDATE ==========

    // Modifier le statut d'un avis (admin)
    public function updateStatut($id, $statut) {
        $query = "UPDATE " . $this->table . " SET statut = :statut WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':statut', $statut);
        return $stmt->execute();
    }

    // ========== MÉTHODES DELETE ==========

    // Supprimer un avis
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ========== MÉTHODES STATISTIQUES (Admin) ==========

    public function countTotal() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countPending() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut = 'en attente'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countApproved() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut = 'approuvé'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function averageNoteGlobal() {
        $query = "SELECT AVG(note) as moyenne FROM " . $this->table . " WHERE statut = 'approuvé'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['moyenne'] ?? 0, 1);
    }
    // Modifier un avis (contenu et note)
public function update($id, $contenu, $note) {
    $query = "UPDATE " . $this->table . " SET contenu = :contenu, note = :note WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':contenu', $contenu);
    $stmt->bindParam(':note', $note);
    return $stmt->execute();
}
}
?>