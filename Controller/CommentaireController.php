<?php
require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/CommentaireModel.php';
require_once __DIR__ . '/../Model/PostModel.php';

class CommentaireController {
    private $db;
    private $commentaireModel;
    private $postModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->commentaireModel = new CommentaireModel($this->db);
        $this->postModel = new PostModel($this->db);
    }

    // FrontOffice: Créer un commentaire
    public function create($contenu, $id_post, $id_utilisateur) {
        // Vérifier que le post existe
        $post = $this->postModel->getById($id_post);
        if (!$post) {
            return ['success' => false, 'errors' => ['Post non trouvé']];
        }

        // Validation
        $errors = [];

        if (empty($contenu)) {
            $errors[] = "Le contenu est requis";
        } elseif (strlen($contenu) < 3) {
            $errors[] = "Le commentaire doit contenir au moins 3 caractères";
        } elseif (strlen($contenu) > 2000) {
            $errors[] = "Le commentaire ne doit pas dépasser 2000 caractères";
        }

        if (empty($id_utilisateur)) {
            $errors[] = "Vous devez être connecté";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->commentaireModel->contenu = $contenu;
        $this->commentaireModel->id_post = $id_post;
        $this->commentaireModel->id_utilisateur = $id_utilisateur;

        if ($this->commentaireModel->create()) {
            return ['success' => true, 'message' => 'Commentaire créé avec succès'];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors de la création du commentaire']];
        }
    }

    // FrontOffice: Modifier un commentaire
    public function update($id, $contenu, $id_utilisateur) {
        $commentaire = $this->commentaireModel->getById($id);
        
        if (!$commentaire) {
            return ['success' => false, 'errors' => ['Commentaire non trouvé']];
        }

        if ($commentaire['id_utilisateur'] != $id_utilisateur) {
            return ['success' => false, 'errors' => ['Vous ne pouvez modifier que vos propres commentaires']];
        }

        // Validation
        $errors = [];

        if (empty($contenu)) {
            $errors[] = "Le contenu est requis";
        } elseif (strlen($contenu) < 3) {
            $errors[] = "Le commentaire doit contenir au moins 3 caractères";
        } elseif (strlen($contenu) > 2000) {
            $errors[] = "Le commentaire ne doit pas dépasser 2000 caractères";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->commentaireModel->id_commentaire = $id;
        $this->commentaireModel->contenu = $contenu;

        if ($this->commentaireModel->update()) {
            return ['success' => true, 'message' => 'Commentaire modifié avec succès'];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors de la modification du commentaire']];
        }
    }

    // Récupérer un commentaire par ID (FrontOffice)
    public function getById($id) {
        return $this->commentaireModel->getById($id);
    }

    // FrontOffice: Supprimer un commentaire
    public function delete($id, $id_utilisateur) {
        $commentaire = $this->commentaireModel->getById($id);
        
        if (!$commentaire) {
            return ['success' => false, 'errors' => ['Commentaire non trouvé']];
        }

        if ($commentaire['id_utilisateur'] != $id_utilisateur) {
            return ['success' => false, 'errors' => ['Vous ne pouvez supprimer que vos propres commentaires']];
        }

        if ($this->commentaireModel->delete($id)) {
            return ['success' => true, 'message' => 'Commentaire supprimé avec succès'];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors de la suppression du commentaire']];
        }
    }

    // BackOffice: Afficher tous les commentaires
    public function listAllForAdmin() {
        return $this->commentaireModel->getAllForAdmin();
    }

    // BackOffice: Bannir un commentaire
    public function ban($id) {
        if ($this->commentaireModel->ban($id)) {
            return ['success' => true, 'message' => 'Commentaire banni avec succès'];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors du bannissement du commentaire']];
        }
    }

    // BackOffice: Débannir un commentaire
    public function unban($id) {
        if ($this->commentaireModel->unban($id)) {
            return ['success' => true, 'message' => 'Commentaire débanni avec succès'];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors du débannissement du commentaire']];
        }
    }
}
?>
