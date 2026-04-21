<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/CommentaireModel.php');
include(__DIR__ . '/../Model/PostModel.php');

class CommentaireController {

    public function listCommentaires() {
        $sql = "SELECT * FROM commentaires";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteCommentaire($id) {
        $sql = "DELETE FROM commentaires WHERE id_commentaire = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addCommentaire(CommentaireModel $commentaire) {
        $sql = "INSERT INTO commentaires VALUES (NULL, :contenu, :date_publication, :id_post, :id_utilisateur, :statue)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'contenu' => $commentaire->getContenu(),
                'date_publication' => $commentaire->getDatePublication() ? $commentaire->getDatePublication()->format('Y-m-d H:i:s') : null,
                'id_post' => $commentaire->getIdPost(),
                'id_utilisateur' => $commentaire->getIdUtilisateur(),
                'statue' => $commentaire->getStatue()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateCommentaire(CommentaireModel $commentaire, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE commentaires SET
                    contenu = :contenu,
                    date_publication = :date_publication,
                    id_post = :id_post,
                    id_utilisateur = :id_utilisateur,
                    statue = :statue
                WHERE id_commentaire = :id'
            );
            $query->execute([
                'id' => $id,
                'contenu' => $commentaire->getContenu(),
                'date_publication' => $commentaire->getDatePublication() ? $commentaire->getDatePublication()->format('Y-m-d H:i:s') : null,
                'id_post' => $commentaire->getIdPost(),
                'id_utilisateur' => $commentaire->getIdUtilisateur(),
                'statue' => $commentaire->getStatue()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showCommentaire($id) {
        $sql="SELECT * FROM commentaires WHERE id_commentaire = $id";
        $db= config::getConnexion();
        $query= $db->prepare($sql);

        try
        {
            $query->execute();
            $commentaire= $query->fetch();
            return $commentaire;
        }
        catch(Exception $e)
        {
            die('Error: '. $e->getMessage());
        }
    }

    // Additional methods to keep functionality
    public function create($contenu, $id_post, $id_utilisateur) {
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

        // Check if post exists
        $sql = "SELECT id_post FROM posts WHERE id_post = :id_post";
        $db = config::getConnexion();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_post', $id_post);
        $stmt->execute();
        if (!$stmt->fetch()) {
            return ['success' => false, 'errors' => ['Post non trouvé']];
        }

        $commentaire = new CommentaireModel(null, htmlspecialchars(strip_tags($contenu)), new DateTime(), $id_post, $id_utilisateur, 'actif');
        $this->addCommentaire($commentaire);

        return ['success' => true, 'message' => 'Commentaire créé avec succès'];
    }

    public function update($id, $contenu, $id_utilisateur) {
        $commentaireData = $this->showCommentaire($id);
        
        if (!$commentaireData) {
            return ['success' => false, 'errors' => ['Commentaire non trouvé']];
        }

        if ($commentaireData['id_utilisateur'] != $id_utilisateur) {
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

        $commentaire = new CommentaireModel($id, htmlspecialchars(strip_tags($contenu)), new DateTime($commentaireData['date_publication']), $commentaireData['id_post'], $commentaireData['id_utilisateur'], $commentaireData['statue']);
        $this->updateCommentaire($commentaire, $id);

        return ['success' => true, 'message' => 'Commentaire modifié avec succès'];
    }

    public function delete($id, $id_utilisateur) {
        $commentaireData = $this->showCommentaire($id);
        
        if (!$commentaireData) {
            return ['success' => false, 'errors' => ['Commentaire non trouvé']];
        }

        if ($commentaireData['id_utilisateur'] != $id_utilisateur) {
            return ['success' => false, 'errors' => ['Vous ne pouvez supprimer que vos propres commentaires']];
        }

        $this->deleteCommentaire($id);

        return ['success' => true, 'message' => 'Commentaire supprimé avec succès'];
    }

    // BackOffice methods
    public function listAllForAdmin() {
        $sql = "SELECT * FROM commentaires ORDER BY date_publication DESC";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function ban($id) {
        $sql = "UPDATE commentaires SET statue = 'banni' WHERE id_commentaire = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
            return ['success' => true, 'message' => 'Commentaire banni avec succès'];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Erreur lors du bannissement du commentaire']];
        }
    }

    public function unban($id) {
        $sql = "UPDATE commentaires SET statue = 'actif' WHERE id_commentaire = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
            return ['success' => true, 'message' => 'Commentaire débanni avec succès'];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Erreur lors du débannissement du commentaire']];
        }
    }
}
?>
