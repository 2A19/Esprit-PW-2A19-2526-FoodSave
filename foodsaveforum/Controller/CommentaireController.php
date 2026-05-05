<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/CommentaireModel.php');
include(__DIR__ . '/../Model/PostModel.php');

class CommentaireController {
    private function handleAudioUpload($audioFile, $prefix = 'comment') {
        if (empty($audioFile) || !isset($audioFile['error']) || $audioFile['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => true, 'path' => null];
        }

        if ($audioFile['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => "Erreur lors de l'upload audio"];
        }

        $allowedMimeTypes = [
            'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav',
            'audio/ogg', 'audio/webm', 'audio/mp4', 'audio/aac', 'audio/x-m4a',
            // Browser MediaRecorder often flags audio-only WebM as video/webm
            'video/webm', 'video/ogg', 'application/octet-stream',
        ];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $audioFile['tmp_name']);
        finfo_close($finfo);

        $isAccepted = in_array($mimeType, $allowedMimeTypes, true)
            || str_starts_with($mimeType, 'audio/')
            || in_array($mimeType, ['video/webm', 'video/ogg']);

        if (!$isAccepted) {
            return ['success' => false, 'error' => "Format audio non supporté ($mimeType)"];
        }

        if ($audioFile['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'error' => "Le fichier audio dépasse 10MB"];
        }

        $uploadDir = __DIR__ . '/../public/uploads/audio';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            return ['success' => false, 'error' => "Impossible de créer le dossier audio"];
        }

        // Determine extension from actual MIME or filename
        $mimeToExt = [
            'audio/mpeg' => 'mp3', 'audio/mp3' => 'mp3',
            'audio/wav' => 'wav', 'audio/x-wav' => 'wav',
            'audio/ogg' => 'ogg', 'video/ogg' => 'ogg',
            'audio/webm' => 'webm', 'video/webm' => 'webm',
            'audio/mp4' => 'm4a', 'audio/aac' => 'aac', 'audio/x-m4a' => 'm4a',
        ];
        $ext = pathinfo($audioFile['name'], PATHINFO_EXTENSION);
        $safeExt = strtolower($ext) ?: ($mimeToExt[$mimeType] ?? 'webm');
        $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $safeExt;
        $targetPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($audioFile['tmp_name'], $targetPath)) {
            return ['success' => false, 'error' => "Échec de l'enregistrement du fichier audio"];
        }

        return ['success' => true, 'path' => '/foodsaveforum/public/uploads/audio/' . $filename];
    }

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
        $sql = "INSERT INTO commentaires (contenu, audio_path, date_publication, id_post, id_utilisateur, statue) VALUES (:contenu, :audio_path, :date_publication, :id_post, :id_utilisateur, :statue)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'contenu' => $commentaire->getContenu(),
                'audio_path' => $commentaire->getAudioPath(),
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
                    audio_path = :audio_path,
                    date_publication = :date_publication,
                    id_post = :id_post,
                    id_utilisateur = :id_utilisateur,
                    statue = :statue
                WHERE id_commentaire = :id'
            );
            $query->execute([
                'id' => $id,
                'contenu' => $commentaire->getContenu(),
                'audio_path' => $commentaire->getAudioPath(),
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
    public function create($contenu, $id_post, $id_utilisateur, $audioFile = null) {
        // Validation
        $errors = [];

        $uploadResult = $this->handleAudioUpload($audioFile, 'comment');
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['error'];
        }
        $audioPath = $uploadResult['path'] ?? null;

        if (empty($contenu) && empty($audioPath)) {
            $errors[] = "Ajoutez un texte ou un message vocal";
        } elseif (strlen($contenu) < 3) {
            if (!empty($contenu)) {
                $errors[] = "Le commentaire doit contenir au moins 3 caractères";
            }
        } elseif (strlen($contenu) > 2000) {
            if (!empty($contenu)) {
                $errors[] = "Le commentaire ne doit pas dépasser 2000 caractères";
            }
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

        $cleanContenu = htmlspecialchars(strip_tags((string) $contenu));
        $commentaire = new CommentaireModel(null, $cleanContenu, $audioPath, new DateTime(), $id_post, $id_utilisateur, 'actif');
        $this->addCommentaire($commentaire);

        return ['success' => true, 'message' => 'Commentaire créé avec succès'];
    }

    public function update($id, $contenu, $id_utilisateur, $audioFile = null) {
        $commentaireData = $this->showCommentaire($id);
        
        if (!$commentaireData) {
            return ['success' => false, 'errors' => ['Commentaire non trouvé']];
        }

        if ($commentaireData['id_utilisateur'] != $id_utilisateur) {
            return ['success' => false, 'errors' => ['Vous ne pouvez modifier que vos propres commentaires']];
        }

        // Validation
        $errors = [];
        $audioPath = $commentaireData['audio_path'] ?? null;
        $uploadResult = $this->handleAudioUpload($audioFile, 'comment');
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['error'];
        } elseif (!empty($uploadResult['path'])) {
            $audioPath = $uploadResult['path'];
        }

        if (empty($contenu) && empty($audioPath)) {
            $errors[] = "Ajoutez un texte ou un message vocal";
        } elseif (strlen($contenu) < 3) {
            if (!empty($contenu)) {
                $errors[] = "Le commentaire doit contenir au moins 3 caractères";
            }
        } elseif (strlen($contenu) > 2000) {
            if (!empty($contenu)) {
                $errors[] = "Le commentaire ne doit pas dépasser 2000 caractères";
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $cleanContenu = htmlspecialchars(strip_tags((string) $contenu));
        $commentaire = new CommentaireModel($id, $cleanContenu, $audioPath, new DateTime($commentaireData['date_publication']), $commentaireData['id_post'], $commentaireData['id_utilisateur'], $commentaireData['statue']);
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

    public function getById($id) {
        return $this->showCommentaire($id);
    }
}
?>
