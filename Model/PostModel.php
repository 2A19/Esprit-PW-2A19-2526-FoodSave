<?php
class PostModel {
    private $conn;
    private $table = 'posts';

    public $id_post;
    public $titre;
    public $contenu;
    public $date_creation;
    public $id_utilisateur;
    public $categorie;
    public $statue;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Récupérer tous les posts
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " WHERE statue != 'bannL' ORDER BY date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un post par ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_post = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer un nouveau post
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (titre, contenu, date_creation, id_utilisateur, categorie, statue) 
                  VALUES 
                  (:titre, :contenu, NOW(), :id_utilisateur, :categorie, 'actif')";

        $stmt = $this->conn->prepare($query);

        // Nettoyer et valider les données
        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $this->categorie = htmlspecialchars(strip_tags($this->categorie));

        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':contenu', $this->contenu);
        $stmt->bindParam(':id_utilisateur', $this->id_utilisateur);
        $stmt->bindParam(':categorie', $this->categorie);

        return $stmt->execute();
    }

    // Modifier un post
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET titre = :titre, contenu = :contenu, categorie = :categorie 
                  WHERE id_post = :id";

        $stmt = $this->conn->prepare($query);

        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $this->categorie = htmlspecialchars(strip_tags($this->categorie));

        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':contenu', $this->contenu);
        $stmt->bindParam(':categorie', $this->categorie);
        $stmt->bindParam(':id', $this->id_post);

        return $stmt->execute();
    }

    // Supprimer un post
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id_post = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Bannir un post (BackOffice Admin)
    public function ban($id) {
        $query = "UPDATE " . $this->table . " SET statue = 'banni' WHERE id_post = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Débannir un post
    public function unban($id) {
        $query = "UPDATE " . $this->table . " SET statue = 'actif' WHERE id_post = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Récupérer tous les posts (y compris bannis) pour l'admin
    public function getAllForAdmin() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Filtrer par catégorie
    public function getByCategory($category) {
        $query = "SELECT * FROM " . $this->table . " WHERE categorie = :categorie AND statue != 'banni' ORDER BY date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categorie', $category);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
