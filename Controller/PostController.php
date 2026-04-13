<?php
require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/PostModel.php';
require_once __DIR__ . '/../Model/CommentaireModel.php';

class PostController {
    private $db;
    private $postModel;
    private $commentaireModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->postModel = new PostModel($this->db);
        $this->commentaireModel = new CommentaireModel($this->db);
    }

    // FrontOffice: Afficher tous les posts
    public function listAll() {
        $posts = $this->postModel->getAll();
        return $posts;
    }

    // FrontOffice: Afficher un post avec ses commentaires
    public function view($id) {
        $post = $this->postModel->getById($id);
        if (!$post) {
            return null;
        }
        $commentaires = $this->commentaireModel->getByPost($id);
        return ['post' => $post, 'commentaires' => $commentaires];
    }

    // FrontOffice: Créer un post
    public function create($titre, $contenu, $categorie, $id_utilisateur) {
        // Validation
        $errors = [];

        if (empty($titre)) {
            $errors[] = "Le titre est requis";
        } elseif (strlen($titre) > 255) {
            $errors[] = "Le titre ne doit pas dépasser 255 caractères";
        }

        if (empty($contenu)) {
            $errors[] = "Le contenu est requis";
        } elseif (strlen($contenu) < 10) {
            $errors[] = "Le contenu doit contenir au moins 10 caractères";
        }

        if (empty($categorie)) {
            $errors[] = "La catégorie est requise";
        }

        if (empty($id_utilisateur)) {
            $errors[] = "Vous devez être connecté";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->postModel->titre = $titre;
        $this->postModel->contenu = $contenu;
        $this->postModel->categorie = $categorie;
        $this->postModel->id_utilisateur = $id_utilisateur;

        if ($this->postModel->create()) {
            return ['success' => true, 'message' => 'Post créé avec succès'];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors de la création du post']];
        }
    }

    // FrontOffice: Modifier un post
    public function update($id, $titre, $contenu, $categorie, $id_utilisateur) {
        $post = $this->postModel->getById($id);
        
        if (!$post) {
            return ['success' => false, 'errors' => ['Post non trouvé']];
        }

        if ($post['id_utilisateur'] != $id_utilisateur) {
            return ['success' => false, 'errors' => ['Vous ne pouvez modifier que vos propres posts']];
        }

        // Validation
        $errors = [];

        if (empty($titre)) {
            $errors[] = "Le titre est requis";
        } elseif (strlen($titre) > 255) {
            $errors[] = "Le titre ne doit pas dépasser 255 caractères";
        }

        if (empty($contenu)) {
            $errors[] = "Le contenu est requis";
        } elseif (strlen($contenu) < 10) {
            $errors[] = "Le contenu doit contenir au moins 10 caractères";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->postModel->id_post = $id;
        $this->postModel->titre = $titre;
        $this->postModel->contenu = $contenu;
        $this->postModel->categorie = $categorie;

        if ($this->postModel->update()) {
            return ['success' => true, 'message' => 'Post modifié avec succès'];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors de la modification du post']];
        }
    }

    // FrontOffice: Supprimer un post
    public function delete($id, $id_utilisateur) {
        $post = $this->postModel->getById($id);
        
        if (!$post) {
            return ['success' => false, 'errors' => ['Post non trouvé']];
        }

        if ($post['id_utilisateur'] != $id_utilisateur) {
            return ['success' => false, 'errors' => ['Vous ne pouvez supprimer que vos propres posts']];
        }

        if ($this->postModel->delete($id)) {
            return ['success' => true, 'message' => 'Post supprimé avec succès'];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors de la suppression du post']];
        }
    }

    // BackOffice: Afficher tous les posts (y compris bannis)
    public function listAllForAdmin() {
        return $this->postModel->getAllForAdmin();
    }

    // BackOffice: Bannir un post
    public function ban($id) {
        if ($this->postModel->ban($id)) {
            return ['success' => true, 'message' => 'Post banni avec succès'];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors du bannissement du post']];
        }
    }

    // BackOffice: Débannir un post
    public function unban($id) {
        if ($this->postModel->unban($id)) {
            return ['success' => true, 'message' => 'Post débanni avec succès'];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors du débannissement du post']];
        }
    }

    // Filtrer par catégorie
    public function getByCategory($category) {
        return $this->postModel->getByCategory($category);
    }
}
?>
