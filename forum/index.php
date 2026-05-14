<?php
/**
 * Forum Frontend - Requires Authentication
 * Located at /forum/index.php
 * User must be logged in via FoodSave authentication
 */
session_start();

// ===== AUTHENTICATION CHECK =====
// User must be logged in via FoodSave
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../foodsave/index.php?action=login');
    exit;
}

// Manage language preference
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr';
}

require_once __DIR__ . '/../Model/Database.php';
require_once __DIR__ . '/../Model/CommentaireModel.php';
require_once __DIR__ . '/../Controller/PostController.php';
require_once __DIR__ . '/../Controller/CommentaireController.php';

$postController = new PostController();
$commentaireController = new CommentaireController();
$perPage = 6;

$action = $_GET['action'] ?? 'list';
$actionAliases = [
    'create-post' => 'create',
    'store-post' => 'store',
    'posts-calendar' => 'calendar',
    'view-post' => 'view',
    'edit-post' => 'edit',
    'update-post' => 'update',
    'delete-post' => 'delete',
    'like-post' => 'toggle-like',
];
$action = $actionAliases[$action] ?? $action;
$title = 'FoodSave Forum';
$errors = [];
$success = false;
$message = '';
$data = [];

try {
    $loadFrontPostsPage = function ($category = '', $page = 1) use ($postController, $perPage) {
        $page = max(1, (int) $page);
        $category = trim((string) $category);

        if ($category !== '') {
            $posts = $postController->getByCategory($category);
        } else {
            $posts = $postController->listAll();
        }

        $posts = $postController->enrichPostsWithLikes($posts, $_SESSION['user_id']);

        $totalPosts = count($posts);
        $totalPages = max(1, (int) ceil($totalPosts / $perPage));
        $currentPage = min($page, $totalPages);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedPosts = array_slice($posts, $offset, $perPage);

        return [
            'posts' => $posts,
            'paginatedPosts' => $paginatedPosts,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'selectedCategory' => $category
        ];
    };

    switch ($action) {
        // Posts FrontOffice
        case 'list':
        case 'posts':
            $category = $_GET['category'] ?? '';
            $page = $_GET['page'] ?? 1;
            $postPageData = $loadFrontPostsPage($category, $page);
            $posts = $postPageData['posts'];
            $paginatedPosts = $postPageData['paginatedPosts'];
            $currentPage = $postPageData['currentPage'];
            $totalPages = $postPageData['totalPages'];
            $selectedCategory = $postPageData['selectedCategory'];
            $title = 'Forum FoodSave';
            $content = __DIR__ . '/../View/front/posts/list.php';
            break;

        case 'create':
            $title = 'Créer un Post';
            $content = __DIR__ . '/../View/front/posts/create.php';
            break;

        case 'calendar':
            $calendarPosts = $postController->listCalendarPosts();
            $title = 'Calendrier des Posts';
            $content = __DIR__ . '/../View/front/posts/calendar.php';
            break;

        case 'store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $postController->create(
                    $_POST['titre'] ?? '',
                    $_POST['contenu'] ?? '',
                    $_POST['categorie'] ?? '',
                    $_SESSION['user_id'],
                    $_FILES['audio_message'] ?? null
                );
                
                if ($result['success']) {
                    $success = true;
                    $message = $result['message'];
                    header('refresh:2;url=index.php?action=list');
                } else {
                    $errors = $result['errors'];
                }
            }
            $postPageData = $loadFrontPostsPage('', 1);
            $posts = $postPageData['posts'];
            $paginatedPosts = $postPageData['paginatedPosts'];
            $currentPage = $postPageData['currentPage'];
            $totalPages = $postPageData['totalPages'];
            $selectedCategory = $postPageData['selectedCategory'];
            $content = __DIR__ . '/../View/front/posts/list.php';
            break;

        case 'view':
            $id = $_GET['id'] ?? null;
            $data = $postController->view($id);
            if (!$data) {
                throw new Exception('Post non trouvé');
            }
            // Ajouter les stats de likes au post
            $data['post']['likes_stats'] = $postController->getLikeStats($id);
            $data['post']['user_reaction'] = $postController->getUserLikeOnPost($id, $_SESSION['user_id']);
            $title = 'Voir le Post';
            $content = __DIR__ . '/../View/front/posts/view.php';
            break;

        case 'edit':
            $id = $_GET['id'] ?? null;
            $post = $postController->view($id)['post'] ?? null;
            if (!$post) {
                throw new Exception('Post non trouvé');
            }
            if ($post['id_utilisateur'] != $_SESSION['user_id']) {
                throw new Exception('Vous ne pouvez modifier que vos propres posts');
            }
            $title = 'Modifier le Post';
            $content = __DIR__ . '/../View/front/posts/edit.php';
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $postController->update(
                    $_POST['id_post'] ?? '',
                    $_POST['titre'] ?? '',
                    $_POST['contenu'] ?? '',
                    $_POST['categorie'] ?? '',
                    $_SESSION['user_id'],
                    $_FILES['audio_message'] ?? null
                );
                
                if ($result['success']) {
                    $success = true;
                    $message = $result['message'];
                    $id = $_POST['id_post'];
                    header('refresh:2;url=index.php?action=view&id=' . $id);
                } else {
                    $errors = $result['errors'];
                }
            }
            $id = $_POST['id_post'] ?? $_GET['id'];
            $post = $postController->view($id)['post'] ?? null;
            $content = __DIR__ . '/../View/front/posts/edit.php';
            break;

        case 'delete':
            $id = $_GET['id'] ?? null;
            $result = $postController->delete($id, $_SESSION['user_id']);
            
            if ($result['success']) {
                $success = true;
                $message = $result['message'];
                header('refresh:2;url=index.php?action=list');
            } else {
                $errors = $result['errors'];
            }
            $category = $_GET['category'] ?? '';
            $page = $_GET['page'] ?? 1;
            $postPageData = $loadFrontPostsPage($category, $page);
            $posts = $postPageData['posts'];
            $paginatedPosts = $postPageData['paginatedPosts'];
            $currentPage = $postPageData['currentPage'];
            $totalPages = $postPageData['totalPages'];
            $selectedCategory = $postPageData['selectedCategory'];
            $content = __DIR__ . '/../View/front/posts/list.php';
            break;

        // Commentaires FrontOffice
        case 'store-comment':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $commentaireController->create(
                    $_POST['contenu'] ?? '',
                    $_POST['id_post'] ?? '',
                    $_SESSION['user_id'],
                    $_FILES['audio_message'] ?? null
                );
                
                if ($result['success']) {
                    $success = true;
                    $message = $result['message'];
                    $id_post = $_POST['id_post'];
                    header('refresh:2;url=index.php?action=view&id=' . $id_post);
                } else {
                    $errors = $result['errors'];
                }
            }
            $id_post = $_POST['id_post'] ?? $_GET['id_post'];
            $data = $postController->view($id_post);
            $title = 'Voir le Post';
            $content = __DIR__ . '/../View/front/posts/view.php';
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
            $content = __DIR__ . '/../View/front/commentaires/edit.php';
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
                    $_SESSION['user_id'],
                    $_FILES['audio_message'] ?? null
                );
                
                if ($result['success']) {
                    $success = true;
                    $message = $result['message'];
                    $id_post = $_POST['id_post'];
                    header('refresh:2;url=index.php?action=view&id=' . $id_post);
                } else {
                    $errors = $result['errors'];
                    $commentaire['contenu'] = $_POST['contenu'] ?? $commentaire['contenu'];
                }
            }
            $title = 'Modifier le Commentaire';
            $content = __DIR__ . '/../View/front/commentaires/edit.php';
            break;

        case 'delete-comment':
            $id = $_GET['id'] ?? null;
            // Get the post ID before deletion
            $commentaire = $commentaireController->showCommentaire($id);
            $id_post = $commentaire['id_post'] ?? null;

            $result = $commentaireController->delete($id, $_SESSION['user_id']);
            
            if ($result['success']) {
                $success = true;
                $message = $result['message'];
                if ($id_post) {
                    header('refresh:2;url=index.php?action=view&id=' . $id_post);
                }
            } else {
                $errors = $result['errors'];
            }
            $postPageData = $loadFrontPostsPage('', 1);
            $posts = $postPageData['posts'];
            $paginatedPosts = $postPageData['paginatedPosts'];
            $currentPage = $postPageData['currentPage'];
            $totalPages = $postPageData['totalPages'];
            $selectedCategory = $postPageData['selectedCategory'];
            $content = __DIR__ . '/../View/front/posts/list.php';
            break;

        // Likes et Dislikes
        case 'toggle-like':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id_post = $_POST['id_post'] ?? null;
                $type = $_POST['type'] ?? 'like'; // 'like' ou 'dislike'
                
                if (!$id_post) {
                    $errors[] = 'Post non trouvé';
                } else {
                    $result = $postController->toggleLike($id_post, $_SESSION['user_id'], $type);
                    
                    if ($result['success']) {
                        // Retourner les stats de likes en JSON pour AJAX
                        $stats = $postController->getLikeStats($id_post);
                        $userReaction = $postController->getUserLikeOnPost($id_post, $_SESSION['user_id']);
                        
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'message' => $result['message'],
                            'action' => $result['action'],
                            'stats' => $stats,
                            'user_reaction' => $userReaction
                        ]);
                        exit;
                    } else {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'errors' => $result['errors']
                        ]);
                        exit;
                    }
                }
            }
            $posts = $postController->listAll();
            $content = __DIR__ . '/../View/front/posts/list.php';
            break;

        default:
            header('Location: index.php?action=list');
            exit;
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
    $postPageData = $loadFrontPostsPage('', 1);
    $posts = $postPageData['posts'];
    $paginatedPosts = $postPageData['paginatedPosts'];
    $currentPage = $postPageData['currentPage'];
    $totalPages = $postPageData['totalPages'];
    $selectedCategory = $postPageData['selectedCategory'];
    $content = __DIR__ . '/../View/front/posts/list.php';
}

// Load layout
$layout = __DIR__ . '/../View/layouts/frontend.php';
include $layout;
?>
