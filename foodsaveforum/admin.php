<?php
session_start();

// Check if user is admin - in a real app, this would check a proper role/permission system
// For now, we'll allow everyone to see the admin panel
// In production, you should verify admin status

require_once __DIR__ . '/Controller/PostController.php';
require_once __DIR__ . '/Controller/CommentaireController.php';

// Check admin permission (this is simplified - add proper authentication)
if (!isset($_SESSION['is_admin'])) {
    $_SESSION['is_admin'] = true; // For demo purposes
}

if (!$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

$postController = new PostController();
$commentaireController = new CommentaireController();

$action = $_GET['action'] ?? 'dashboard';
$title = 'FoodSave - BackOffice';
$errors = [];
$success = false;
$message = '';

try {
    switch ($action) {
        case 'dashboard':
            $title = 'Dashboard Admin';
            $content = __DIR__ . '/View/back/posts/dashboard.php';
            if (!file_exists($content)) {
                // If dashboard doesn't exist, redirect to posts list
                header('Location: admin.php?action=posts');
                exit;
            }
            break;

        // Posts Admin
        case 'posts':
            $posts = $postController->listAllForAdmin();
            $title = 'Gérer les Posts';
            $content = __DIR__ . '/View/back/posts/list.php';
            break;

        case 'view-post':
            $id = $_GET['id'] ?? null;
            $post = $postController->view($id)['post'] ?? null;
            if (!$post) {
                throw new Exception('Post non trouvé');
            }
            $title = 'Détail du Post';
            $content = __DIR__ . '/View/back/posts/view.php';
            break;

        case 'ban-post':
            $id = $_GET['id'] ?? null;
            $result = $postController->ban($id);
            
            if ($result['success']) {
                $success = true;
                $message = $result['message'];
            } else {
                $errors = $result['errors'];
            }
            
            $posts = $postController->listAllForAdmin();
            $title = 'Gérer les Posts';
            $content = __DIR__ . '/View/back/posts/list.php';
            break;

        case 'unban-post':
            $id = $_GET['id'] ?? null;
            $result = $postController->unban($id);
            
            if ($result['success']) {
                $success = true;
                $message = $result['message'];
            } else {
                $errors = $result['errors'];
            }
            
            $posts = $postController->listAllForAdmin();
            $title = 'Gérer les Posts';
            $content = __DIR__ . '/View/back/posts/list.php';
            break;

        case 'delete-post':
            $id = $_GET['id'] ?? null;
            // Admin can delete posts permanently
            require_once __DIR__ . '/Model/Database.php';
            require_once __DIR__ . '/Model/PostModel.php';
            $db = (new Database())->connect();
            $postModel = new PostModel($db);
            
            if ($postModel->delete($id)) {
                $success = true;
                $message = 'Post supprimé avec succès';
            } else {
                $errors[] = 'Erreur lors de la suppression';
            }
            
            $posts = $postController->listAllForAdmin();
            $title = 'Gérer les Posts';
            $content = __DIR__ . '/View/back/posts/list.php';
            break;

        // Commentaires Admin
        case 'commentaires':
            $commentaires = $commentaireController->listAllForAdmin();
            $title = 'Gérer les Commentaires';
            $content = __DIR__ . '/View/back/commentaires/list.php';
            break;

        case 'view-commentaire':
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('Commentaire non trouvé');
            $commentaire = $commentaireController->getById($id);
            if (!$commentaire) throw new Exception('Commentaire non trouvé');
            $title = 'Détail du Commentaire';
            $content = __DIR__ . '/View/back/commentaires/view.php';
            break;

        case 'ban-commentaire':
            $id = $_GET['id'] ?? null;
            $result = $commentaireController->ban($id);
            
            if ($result['success']) {
                $success = true;
                $message = $result['message'];
            } else {
                $errors = $result['errors'];
            }
            
            $commentaires = $commentaireController->listAllForAdmin();
            $title = 'Gérer les Commentaires';
            $content = __DIR__ . '/View/back/commentaires/list.php';
            break;

        case 'unban-commentaire':
            $id = $_GET['id'] ?? null;
            $result = $commentaireController->unban($id);
            
            if ($result['success']) {
                $success = true;
                $message = $result['message'];
            } else {
                $errors = $result['errors'];
            }
            
            $commentaires = $commentaireController->listAllForAdmin();
            $title = 'Gérer les Commentaires';
            $content = __DIR__ . '/View/back/commentaires/list.php';
            break;

        case 'delete-commentaire':
            $id = $_GET['id'] ?? null;
            // Admin can delete comments permanently
            require_once __DIR__ . '/Model/Database.php';
            require_once __DIR__ . '/Model/CommentaireModel.php';
            $db = (new Database())->connect();
            $commentModel = new CommentaireModel($db);
            
            if ($commentModel->delete($id)) {
                $success = true;
                $message = 'Commentaire supprimé avec succès';
            } else {
                $errors[] = 'Erreur lors de la suppression';
            }
            
            $commentaires = $commentaireController->listAllForAdmin();
            $title = 'Gérer les Commentaires';
            $content = __DIR__ . '/View/back/commentaires/list.php';
            break;

        default:
            header('Location: admin.php?action=posts');
            exit;
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
    $posts = $postController->listAllForAdmin();
    $content = __DIR__ . '/View/back/posts/list.php';
}

// Load layout
$layout = __DIR__ . '/View/layouts/backend.php';
include $layout;
?>
