<?php
/**
 * Forum Admin Panel - Requires Admin Role
 * Located at /forum/admin.php
 */
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../foodsave/index.php?action=login');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    $_SESSION['error'] = 'Acces refuse. Seuls les administrateurs peuvent acceder a cette page.';
    header('Location: ../foodsave/index.php?action=dashboard');
    exit;
}

require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/CommentaireModel.php';
require_once __DIR__ . '/../Controller/PostController.php';
require_once __DIR__ . '/../Controller/CommentaireController.php';

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
            $content = __DIR__ . '/../View/back/posts/dashboard.php';
            break;

        case 'posts':
            $selectedCategory = $_GET['category'] ?? '';
            $posts = $postController->listAllForAdmin($selectedCategory);
            $title = 'Gerer les Posts';
            $content = __DIR__ . '/../View/back/posts/list.php';
            break;

        case 'view-post':
        case 'post-details':
            $id = $_GET['id'] ?? null;
            $post = $postController->view($id)['post'] ?? null;
            if (!$post) {
                throw new Exception('Post non trouve');
            }
            $title = 'Detail du Post';
            $content = __DIR__ . '/../View/back/posts/view.php';
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

            $selectedCategory = $_GET['category'] ?? '';
            $posts = $postController->listAllForAdmin($selectedCategory);
            $title = 'Gerer les Posts';
            $content = __DIR__ . '/../View/back/posts/list.php';
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

            $selectedCategory = $_GET['category'] ?? '';
            $posts = $postController->listAllForAdmin($selectedCategory);
            $title = 'Gerer les Posts';
            $content = __DIR__ . '/../View/back/posts/list.php';
            break;

        case 'delete-post':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('Post non trouve');
            }
            $postController->deletePost($id);
            $success = true;
            $message = 'Post supprime avec succes';

            $selectedCategory = $_GET['category'] ?? '';
            $posts = $postController->listAllForAdmin($selectedCategory);
            $title = 'Gerer les Posts';
            $content = __DIR__ . '/../View/back/posts/list.php';
            break;

        case 'commentaires':
        case 'comments':
            $commentaires = $commentaireController->listAllForAdmin();
            $title = 'Gerer les Commentaires';
            $content = __DIR__ . '/../View/back/commentaires/list.php';
            break;

        case 'view-commentaire':
        case 'comment-details':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('Commentaire non trouve');
            }
            $commentaire = $commentaireController->getById($id);
            if (!$commentaire) {
                throw new Exception('Commentaire non trouve');
            }
            $title = 'Detail du Commentaire';
            $content = __DIR__ . '/../View/back/commentaires/view.php';
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
            $title = 'Gerer les Commentaires';
            $content = __DIR__ . '/../View/back/commentaires/list.php';
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
            $title = 'Gerer les Commentaires';
            $content = __DIR__ . '/../View/back/commentaires/list.php';
            break;

        case 'delete-comment':
        case 'delete-commentaire':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('Commentaire non trouve');
            }
            $commentaireController->deleteCommentaire($id);
            $success = true;
            $message = 'Commentaire supprime avec succes';

            $commentaires = $commentaireController->listAllForAdmin();
            $title = 'Gerer les Commentaires';
            $content = __DIR__ . '/../View/back/commentaires/list.php';
            break;

        default:
            header('Location: admin.php?action=posts');
            exit;
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
    $selectedCategory = $_GET['category'] ?? '';
    $posts = $postController->listAllForAdmin($selectedCategory);
    $content = __DIR__ . '/../View/back/posts/list.php';
}

$layout = __DIR__ . '/../View/layouts/backend.php';
include $layout;
?>
