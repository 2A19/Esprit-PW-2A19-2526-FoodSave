<?php
session_start();

// Simulated current user - in a real app, this would come from authentication
$_SESSION['user_id'] = $_SESSION['user_id'] ?? 1;
$_SESSION['username'] = $_SESSION['username'] ?? 'User #' . $_SESSION['user_id'];
$_SESSION['is_admin'] = $_SESSION['is_admin'] ?? true; // For demo purposes

require_once __DIR__ . '/Controller/PostController.php';
require_once __DIR__ . '/Controller/CommentaireController.php';

$postController = new PostController();
$commentaireController = new CommentaireController();

$action = $_GET['action'] ?? 'posts';
$title = 'FoodSave Forum';
$errors = [];
$success = false;
$message = '';
$data = [];

try {
    switch ($action) {
        // Posts FrontOffice
        case 'posts':
            $category = $_GET['category'] ?? '';
            if ($category) {
                $posts = $postController->getByCategory($category);
            } else {
                $posts = $postController->listAll();
            }
            $title = 'Forum FoodSave';
            $content = __DIR__ . '/View/front/posts/list.php';
            break;

        case 'create-post':
            $title = 'Créer un Post';
            $content = __DIR__ . '/View/front/posts/create.php';
            break;

        case 'store-post':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $postController->create(
                    $_POST['titre'] ?? '',
                    $_POST['contenu'] ?? '',
                    $_POST['categorie'] ?? '',
                    $_SESSION['user_id']
                );
                
                if ($result['success']) {
                    $success = true;
                    $message = $result['message'];
                    header('refresh:2;url=index.php?action=posts');
                } else {
                    $errors = $result['errors'];
                }
            }
            $posts = $postController->listAll();
            $content = __DIR__ . '/View/front/posts/list.php';
            break;

        case 'view-post':
            $id = $_GET['id'] ?? null;
            $data = $postController->view($id);
            if (!$data) {
                throw new Exception('Post non trouvé');
            }
            $title = 'Voir le Post';
            $content = __DIR__ . '/View/front/posts/view.php';
            break;

        case 'edit-post':
            $id = $_GET['id'] ?? null;
            $post = $postController->view($id)['post'] ?? null;
            if (!$post) {
                throw new Exception('Post non trouvé');
            }
            if ($post['id_utilisateur'] != $_SESSION['user_id']) {
                throw new Exception('Vous ne pouvez modifier que vos propres posts');
            }
            $title = 'Modifier le Post';
            $content = __DIR__ . '/View/front/posts/edit.php';
            break;

        case 'update-post':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $postController->update(
                    $_POST['id_post'] ?? '',
                    $_POST['titre'] ?? '',
                    $_POST['contenu'] ?? '',
                    $_POST['categorie'] ?? '',
                    $_SESSION['user_id']
                );
                
                if ($result['success']) {
                    $success = true;
                    $message = $result['message'];
                    $id = $_POST['id_post'];
                    header('refresh:2;url=index.php?action=view-post&id=' . $id);
                } else {
                    $errors = $result['errors'];
                }
            }
            $id = $_POST['id_post'] ?? $_GET['id'];
            $post = $postController->view($id)['post'] ?? null;
            $content = __DIR__ . '/View/front/posts/edit.php';
            break;

        case 'delete-post':
            $id = $_GET['id'] ?? null;
            $result = $postController->delete($id, $_SESSION['user_id']);
            
            if ($result['success']) {
                $success = true;
                $message = $result['message'];
                header('refresh:2;url=index.php?action=posts');
            } else {
                $errors = $result['errors'];
            }
            $posts = $postController->listAll();
            $content = __DIR__ . '/View/front/posts/list.php';
            break;

        // Commentaires FrontOffice
        case 'store-comment':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $commentaireController->create(
                    $_POST['contenu'] ?? '',
                    $_POST['id_post'] ?? '',
                    $_SESSION['user_id']
                );
                
                if ($result['success']) {
                    $success = true;
                    $message = $result['message'];
                    $id_post = $_POST['id_post'];
                    header('refresh:2;url=index.php?action=view-post&id=' . $id_post);
                } else {
                    $errors = $result['errors'];
                }
            }
            $id_post = $_POST['id_post'] ?? $_GET['id_post'];
            $data = $postController->view($id_post);
            $title = 'Voir le Post';
            $content = __DIR__ . '/View/front/posts/view.php';
            break;

        case 'edit-comment':
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('Commentaire non trouvé');
            $commentaire = $commentaireController->getById($id);
            if (!$commentaire) throw new Exception('Commentaire non trouvé');
            if ($commentaire['id_utilisateur'] != $_SESSION['user_id']) {
                throw new Exception('Vous ne pouvez modifier que vos propres commentaires');
            }
            $title = 'Modifier le Commentaire';
            $content = __DIR__ . '/View/front/commentaires/edit.php';
            break;

        case 'update-comment':
            $id = $_POST['id_commentaire'] ?? $_GET['id'];
            if (!$id) throw new Exception('Commentaire non trouvé');
            $commentaire = $commentaireController->getById($id);
            if (!$commentaire) throw new Exception('Commentaire non trouvé');

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $commentaireController->update(
                    $_POST['id_commentaire'] ?? '',
                    $_POST['contenu'] ?? '',
                    $_SESSION['user_id']
                );
                
                if ($result['success']) {
                    $success = true;
                    $message = $result['message'];
                    $id_post = $_POST['id_post'];
                    header('refresh:2;url=index.php?action=view-post&id=' . $id_post);
                } else {
                    $errors = $result['errors'];
                    $commentaire['contenu'] = $_POST['contenu'] ?? $commentaire['contenu'];
                }
            }
            $title = 'Modifier le Commentaire';
            $content = __DIR__ . '/View/front/commentaires/edit.php';
            break;

        case 'delete-comment':
            $id = $_GET['id'] ?? null;
            // Get the post ID before deletion
            $commentaireTable = new CommentaireModel(
                (new Database())->connect()
            );
            $commentaire = $commentaireTable->getById($id);
            $id_post = $commentaire['id_post'] ?? null;

            $result = $commentaireController->delete($id, $_SESSION['user_id']);
            
            if ($result['success']) {
                $success = true;
                $message = $result['message'];
                if ($id_post) {
                    header('refresh:2;url=index.php?action=view-post&id=' . $id_post);
                }
            } else {
                $errors = $result['errors'];
            }
            $posts = $postController->listAll();
            $content = __DIR__ . '/View/front/posts/list.php';
            break;

        default:
            header('Location: index.php?action=posts');
            exit;
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
    $posts = $postController->listAll();
    $content = __DIR__ . '/View/front/posts/list.php';
}

// Load layout
$layout = __DIR__ . '/View/layouts/frontend.php';
include $layout;
?>
