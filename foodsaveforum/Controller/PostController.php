<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/PostModel.php');
include(__DIR__ . '/../Model/CommentaireModel.php');

class PostController {

    public function listPosts() {
        $sql = "SELECT * FROM posts";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deletePost($id) {
        $sql = "DELETE FROM posts WHERE id_post = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addPost(PostModel $post) {
        $sql = "INSERT INTO posts VALUES (NULL, :titre, :contenu, :date_creation, :id_utilisateur, :categorie, :statue)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $post->getTitre(),
                'contenu' => $post->getContenu(),
                'date_creation' => $post->getDateCreation() ? $post->getDateCreation()->format('Y-m-d H:i:s') : null,
                'id_utilisateur' => $post->getIdUtilisateur(),
                'categorie' => $post->getCategorie(),
                'statue' => $post->getStatue()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updatePost(PostModel $post, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE posts SET
                    titre = :titre,
                    contenu = :contenu,
                    date_creation = :date_creation,
                    id_utilisateur = :id_utilisateur,
                    categorie = :categorie,
                    statue = :statue
                WHERE id_post = :id'
            );
            $query->execute([
                'id' => $id,
                'titre' => $post->getTitre(),
                'contenu' => $post->getContenu(),
                'date_creation' => $post->getDateCreation() ? $post->getDateCreation()->format('Y-m-d H:i:s') : null,
                'id_utilisateur' => $post->getIdUtilisateur(),
                'categorie' => $post->getCategorie(),
                'statue' => $post->getStatue()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showPost($id) {
        $sql="SELECT * FROM posts WHERE id_post = $id";
        $db= config::getConnexion();
        $query= $db->prepare($sql);

        try
        {
            $query->execute();
            $post= $query->fetch(PDO::FETCH_ASSOC);
            return $post;
        }
        catch(Exception $e)
        {
            die('Error: '. $e->getMessage());
        }
    }

    // FrontOffice: Afficher tous les posts
    public function listAll() {
        $sql = "SELECT * FROM posts WHERE statue != 'banni' ORDER BY date_creation DESC";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // FrontOffice: Afficher un post avec ses commentaires
    public function view($id) {
        $post = $this->showPost($id);
        if (!$post) {
            return null;
        }
        $sql = "SELECT * FROM commentaires WHERE id_post = :id_post AND statue != 'banni' ORDER BY date_publication DESC";
        $db = config::getConnexion();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_post', $id);
        $stmt->execute();
        $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        $post = new PostModel(null, htmlspecialchars(strip_tags($titre)), htmlspecialchars(strip_tags($contenu)), new DateTime(), $id_utilisateur, htmlspecialchars(strip_tags($categorie)), 'actif');
        $this->addPost($post);

        return ['success' => true, 'message' => 'Post créé avec succès'];
    }

    // FrontOffice: Modifier un post
    public function update($id, $titre, $contenu, $categorie, $id_utilisateur) {
        $postData = $this->showPost($id);
        
        if (!$postData) {
            return ['success' => false, 'errors' => ['Post non trouvé']];
        }

        if ($postData['id_utilisateur'] != $id_utilisateur) {
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

        $post = new PostModel($id, htmlspecialchars(strip_tags($titre)), htmlspecialchars(strip_tags($contenu)), new DateTime($postData['date_creation']), $postData['id_utilisateur'], htmlspecialchars(strip_tags($categorie)), $postData['statue']);
        $this->updatePost($post, $id);

        return ['success' => true, 'message' => 'Post modifié avec succès'];
    }

    // FrontOffice: Supprimer un post
    public function delete($id, $id_utilisateur) {
        $postData = $this->showPost($id);
        
        if (!$postData) {
            return ['success' => false, 'errors' => ['Post non trouvé']];
        }

        if ($postData['id_utilisateur'] != $id_utilisateur) {
            return ['success' => false, 'errors' => ['Vous ne pouvez supprimer que vos propres posts']];
        }

        $this->deletePost($id);

        return ['success' => true, 'message' => 'Post supprimé avec succès'];
    }

    // BackOffice: Afficher tous les posts (y compris bannis)
    public function listAllForAdmin() {
        $sql = "SELECT * FROM posts ORDER BY date_creation DESC";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // BackOffice: Bannir un post
    public function ban($id) {
        $sql = "UPDATE posts SET statue = 'banni' WHERE id_post = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
            return ['success' => true, 'message' => 'Post banni avec succès'];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Erreur lors du bannissement du post']];
        }
    }

    // BackOffice: Débannir un post
    public function unban($id) {
        $sql = "UPDATE posts SET statue = 'actif' WHERE id_post = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
            return ['success' => true, 'message' => 'Post débanni avec succès'];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Erreur lors du débannissement du post']];
        }
    }

    // Filtrer par catégorie
    public function getByCategory($category) {
        $sql = "SELECT * FROM posts WHERE categorie = :categorie AND statue != 'banni' ORDER BY date_creation DESC";
        $db = config::getConnexion();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':categorie', $category);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
