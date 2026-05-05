<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/PostModel.php');
include(__DIR__ . '/../Model/CommentaireModel.php');
include(__DIR__ . '/../Model/LikeModel.php');

class PostController {
    private function handleAudioUpload($audioFile, $prefix = 'post') {
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
        $sql = "INSERT INTO posts (titre, contenu, audio_path, date_creation, id_utilisateur, categorie, statue) VALUES (:titre, :contenu, :audio_path, :date_creation, :id_utilisateur, :categorie, :statue)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $post->getTitre(),
                'contenu' => $post->getContenu(),
                'audio_path' => $post->getAudioPath(),
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
                    audio_path = :audio_path,
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
                'audio_path' => $post->getAudioPath(),
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
        $sql = "SELECT 
        
                    p.id_post, p.titre, p.contenu as post_contenu, p.audio_path as post_audio_path, p.date_creation, 
                    p.id_utilisateur as post_id_utilisateur, p.categorie, p.statue as post_statue,
                    c.id_commentaire, c.contenu as commentaire_contenu, c.audio_path as commentaire_audio_path, c.date_publication,
                    c.id_utilisateur as commentaire_id_utilisateur, c.statue as commentaire_statue
                FROM posts p 
                LEFT JOIN commentaires c ON p.id_post = c.id_post 
                WHERE p.id_post = :id_post AND (c.statue != 'banni' OR c.id_commentaire IS NULL)
                ORDER BY c.date_publication DESC";
        
        $db = config::getConnexion();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_post', $id);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) {
            return null;
        }
        
        // Séparer le post des commentaires
        $post = null;
        $commentaires = [];
        
        foreach ($results as $row) {
            if ($post === null) {
                $post = [
                    'id_post' => $row['id_post'],
                    'titre' => $row['titre'],
                    'contenu' => $row['post_contenu'],
                    'audio_path' => $row['post_audio_path'],
                    'date_creation' => $row['date_creation'],
                    'id_utilisateur' => $row['post_id_utilisateur'],
                    'categorie' => $row['categorie'],
                    'statue' => $row['post_statue']
                ];
            }
            
            if (!is_null($row['id_commentaire'])) {
                $commentaires[] = [
                    'id_commentaire' => $row['id_commentaire'],
                    'contenu' => $row['commentaire_contenu'],
                    'audio_path' => $row['commentaire_audio_path'],
                    'date_publication' => $row['date_publication'],
                    'id_post' => $row['id_post'],
                    'id_utilisateur' => $row['commentaire_id_utilisateur'],
                    'statue' => $row['commentaire_statue']
                ];
            }
        }
        
        return ['post' => $post, 'commentaires' => $commentaires];
    }

    // FrontOffice: Créer un post
    public function create($titre, $contenu, $categorie, $id_utilisateur, $audioFile = null) {
        // Validation
        $errors = [];

        $uploadResult = $this->handleAudioUpload($audioFile, 'post');
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['error'];
        }
        $audioPath = $uploadResult['path'] ?? null;

        if (empty($titre)) {
            $errors[] = "Le titre est requis";
        } elseif (strlen($titre) > 255) {
            $errors[] = "Le titre ne doit pas dépasser 255 caractères";
        }

        if (empty($contenu) && empty($audioPath)) {
            $errors[] = "Ajoutez un texte ou un message vocal";
        } elseif (strlen($contenu) < 10) {
            if (!empty($contenu)) {
                $errors[] = "Le contenu doit contenir au moins 10 caractères";
            }
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

        $cleanTitre = htmlspecialchars(strip_tags((string) $titre));
        $cleanContenu = htmlspecialchars(strip_tags((string) $contenu));
        $cleanCategorie = htmlspecialchars(strip_tags((string) $categorie));
        $post = new PostModel(null, $cleanTitre, $cleanContenu, $audioPath, new DateTime(), $id_utilisateur, $cleanCategorie, 'actif');
        $this->addPost($post);

        return ['success' => true, 'message' => 'Post créé avec succès'];
    }

    // FrontOffice: Modifier un post
    public function update($id, $titre, $contenu, $categorie, $id_utilisateur, $audioFile = null) {
        $postData = $this->showPost($id);
        
        if (!$postData) {
            return ['success' => false, 'errors' => ['Post non trouvé']];
        }

        if ($postData['id_utilisateur'] != $id_utilisateur) {
            return ['success' => false, 'errors' => ['Vous ne pouvez modifier que vos propres posts']];
        }

        // Validation
        $errors = [];
        $audioPath = $postData['audio_path'] ?? null;
        $uploadResult = $this->handleAudioUpload($audioFile, 'post');
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['error'];
        } elseif (!empty($uploadResult['path'])) {
            $audioPath = $uploadResult['path'];
        }

        if (empty($titre)) {
            $errors[] = "Le titre est requis";
        } elseif (strlen($titre) > 255) {
            $errors[] = "Le titre ne doit pas dépasser 255 caractères";
        }

        if (empty($contenu) && empty($audioPath)) {
            $errors[] = "Ajoutez un texte ou un message vocal";
        } elseif (strlen($contenu) < 10) {
            if (!empty($contenu)) {
                $errors[] = "Le contenu doit contenir au moins 10 caractères";
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $cleanTitre = htmlspecialchars(strip_tags((string) $titre));
        $cleanContenu = htmlspecialchars(strip_tags((string) $contenu));
        $cleanCategorie = htmlspecialchars(strip_tags((string) $categorie));
        $post = new PostModel($id, $cleanTitre, $cleanContenu, $audioPath, new DateTime($postData['date_creation']), $postData['id_utilisateur'], $cleanCategorie, $postData['statue']);
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
    public function listAllForAdmin($category = null) {
        $db = config::getConnexion();
        try {
            if (!empty($category)) {
                $sql = "SELECT * FROM posts WHERE categorie = :categorie ORDER BY date_creation DESC";
                $stmt = $db->prepare($sql);
                $stmt->execute([':categorie' => $category]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $sql = "SELECT * FROM posts ORDER BY date_creation DESC";
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

    // FrontOffice: Données pour le calendrier des posts
    public function listCalendarPosts() {
        $sql = "SELECT id_post, titre, categorie, date_creation
                FROM posts
                WHERE statue != 'banni'
                ORDER BY date_creation DESC";
        $db = config::getConnexion();
        try {
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // ========================================
    // Gestion des Likes et Dislikes
    // ========================================

    /**
     * Ajouter ou modifier un like/dislike
     */
    public function toggleLike($id_post, $id_utilisateur, $type_reaction) {
        if (!in_array($type_reaction, ['like', 'dislike'])) {
            return ['success' => false, 'errors' => ['Type de réaction invalide']];
        }

        $db = config::getConnexion();

        try {
            // Vérifier si l'utilisateur a déjà une réaction sur ce post
            $sqlCheck = "SELECT * FROM post_likes WHERE id_post = :id_post AND id_utilisateur = :id_utilisateur";
            $stmtCheck = $db->prepare($sqlCheck);
            $stmtCheck->execute([
                ':id_post' => $id_post,
                ':id_utilisateur' => $id_utilisateur
            ]);
            $existingLike = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existingLike) {
                // Si la réaction est la même, la supprimer
                if ($existingLike['type_reaction'] === $type_reaction) {
                    $sqlDelete = "DELETE FROM post_likes WHERE id_post = :id_post AND id_utilisateur = :id_utilisateur";
                    $stmtDelete = $db->prepare($sqlDelete);
                    $stmtDelete->execute([
                        ':id_post' => $id_post,
                        ':id_utilisateur' => $id_utilisateur
                    ]);
                    return ['success' => true, 'message' => 'Réaction supprimée', 'action' => 'removed'];
                } else {
                    // Sinon, la modifier
                    $sqlUpdate = "UPDATE post_likes SET type_reaction = :type_reaction WHERE id_post = :id_post AND id_utilisateur = :id_utilisateur";
                    $stmtUpdate = $db->prepare($sqlUpdate);
                    $stmtUpdate->execute([
                        ':type_reaction' => $type_reaction,
                        ':id_post' => $id_post,
                        ':id_utilisateur' => $id_utilisateur
                    ]);
                    return ['success' => true, 'message' => 'Réaction modifiée', 'action' => 'updated'];
                }
            } else {
                // Ajouter une nouvelle réaction
                $sqlInsert = "INSERT INTO post_likes (id_post, id_utilisateur, type_reaction) VALUES (:id_post, :id_utilisateur, :type_reaction)";
                $stmtInsert = $db->prepare($sqlInsert);
                $stmtInsert->execute([
                    ':id_post' => $id_post,
                    ':id_utilisateur' => $id_utilisateur,
                    ':type_reaction' => $type_reaction
                ]);
                return ['success' => true, 'message' => 'Réaction ajoutée', 'action' => 'added'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Erreur lors de la gestion de la réaction: ' . $e->getMessage()]];
        }
    }

    /**
     * Obtenir les statistiques de likes/dislikes pour un post
     */
    public function getLikeStats($id_post) {
        $db = config::getConnexion();

        try {
            $sql = "SELECT type_reaction, COUNT(*) as count FROM post_likes WHERE id_post = :id_post GROUP BY type_reaction";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id_post' => $id_post]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stats = [
                'likes' => 0,
                'dislikes' => 0
            ];

            foreach ($results as $row) {
                if ($row['type_reaction'] === 'like') {
                    $stats['likes'] = $row['count'];
                } elseif ($row['type_reaction'] === 'dislike') {
                    $stats['dislikes'] = $row['count'];
                }
            }

            return $stats;
        } catch (Exception $e) {
            return ['likes' => 0, 'dislikes' => 0];
        }
    }

    /**
     * Obtenir la réaction de l'utilisateur sur un post
     */
    public function getUserLikeOnPost($id_post, $id_utilisateur) {
        $db = config::getConnexion();

        try {
            $sql = "SELECT type_reaction FROM post_likes WHERE id_post = :id_post AND id_utilisateur = :id_utilisateur";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_post' => $id_post,
                ':id_utilisateur' => $id_utilisateur
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['type_reaction'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Ajouter les stats de likes aux posts
     */
    public function enrichPostsWithLikes($posts, $id_utilisateur = null) {
        foreach ($posts as &$post) {
            $post['likes_stats'] = $this->getLikeStats($post['id_post']);
            if ($id_utilisateur) {
                $post['user_reaction'] = $this->getUserLikeOnPost($post['id_post'], $id_utilisateur);
            }
        }
        return $posts;
    }
}
?>
