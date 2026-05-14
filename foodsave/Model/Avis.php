<?php
class Avis {
    private $conn;
    private $table = 'avis';

    public function __construct() {
        $this->conn = config::getConnexion();
    }

    // ========== MÉTHODES READ ==========

    public function getByArticleId($article_id, $limit = null) {
        $query = "SELECT * FROM " . $this->table . " WHERE article_id = :article_id AND statut = 'approuvé' ORDER BY created_at DESC";
        if($limit) {
            $query .= " LIMIT " . intval($limit);
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countByArticleId($article_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE article_id = :article_id AND statut = 'approuvé'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getAverageNote($article_id) {
        $query = "SELECT AVG(note) as moyenne FROM " . $this->table . " WHERE article_id = :article_id AND statut = 'approuvé'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['moyenne'] ?? 0, 1);
    }

    // ========== MÉTHODES CREATE ==========

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

    public function updateStatut($id, $statut) {
        $query = "UPDATE " . $this->table . " SET statut = :statut WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':statut', $statut);
        return $stmt->execute();
    }

    public function update($id, $contenu, $note) {
        $query = "UPDATE " . $this->table . " SET contenu = :contenu, note = :note WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':contenu', $contenu);
        $stmt->bindParam(':note', $note);
        return $stmt->execute();
    }

    // ========== MÉTHODES DELETE ==========

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ========== MÉTHODES STATISTIQUES ==========

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
}
?>