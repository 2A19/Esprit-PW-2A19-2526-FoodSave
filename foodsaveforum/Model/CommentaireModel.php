<?php
class CommentaireModel {
    private $conn;
    private $table = 'commentaires';

    public $id_commentaire;
    public $contenu;
    public $date_publication;
    public $id_post;
    public $id_utilisateur;
    public $statue;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Récupérer les commentaires d'un post
    public function getByPost($id_post) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_post = :id_post AND statue != 'banni' ORDER BY date_publication DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_post', $id_post);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un commentaire par ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_commentaire = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer un nouveau commentaire
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (contenu, date_publication, id_post, id_utilisateur, statue) 
                  VALUES 
                  (:contenu, NOW(), :id_post, :id_utilisateur, 'actif')";

        $stmt = $this->conn->prepare($query);

        $this->contenu = htmlspecialchars(strip_tags($this->contenu));

        $stmt->bindParam(':contenu', $this->contenu);
        $stmt->bindParam(':id_post', $this->id_post);
        $stmt->bindParam(':id_utilisateur', $this->id_utilisateur);

        return $stmt->execute();
    }

    // Modifier un commentaire
    public function update() {
        $query = "UPDATE " . $this->table . " SET contenu = :contenu WHERE id_commentaire = :id";
        $stmt = $this->conn->prepare($query);

        $this->contenu = htmlspecialchars(strip_tags($this->contenu));

        $stmt->bindParam(':contenu', $this->contenu);
        $stmt->bindParam(':id', $this->id_commentaire);

        return $stmt->execute();
    }

    // Supprimer un commentaire
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id_commentaire = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Bannir un commentaire
    public function ban($id) {
        $query = "UPDATE " . $this->table . " SET statue = 'banni' WHERE id_commentaire = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Débannir un commentaire
    public function unban($id) {
        $query = "UPDATE " . $this->table . " SET statue = 'actif' WHERE id_commentaire = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Récupérer tous les commentaires (y compris bannis) pour l'admin
    public function getAllForAdmin() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY date_publication DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
