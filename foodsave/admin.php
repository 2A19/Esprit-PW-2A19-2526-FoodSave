<?php
session_start();

require_once __DIR__ . '/Controller/UserController.php';
require_once __DIR__ . '/Controller/ArticleController.php';
require_once __DIR__ . '/Controller/AvisController.php';
require_once __DIR__ . '/Controller/EvenementController.php';
require_once __DIR__ . '/Controller/ParticipantController.php';
require_once __DIR__ . '/Controller/DechetController.php';
require_once __DIR__ . '/Controller/CollecteController.php';
require_once __DIR__ . '/Controller/CategoryController.php';
require_once __DIR__ . '/Controller/MetierController.php';

// Vérifier que l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php?action=login');
    exit;
}

$action = $_GET['action'] ?? 'dashboard';
$controller = new UserController();
$articleController = new ArticleController();
$avisController = new AvisController();
$evenementController  = new EvenementController();
$participantController = new ParticipantController();
$dechetController      = new DechetController();
$collecteController    = new CollecteController();
$categoryController    = new CategoryController();
$metierController      = new MetierController();

switch ($action) {
    // ========== GESTION UTILISATEURS (existants) ==========
    case 'dashboard':
        $controller->adminDashboard();
        break;
    case 'users':
        $controller->usersList();
        break;
    case 'user_details':
        $controller->userDetails();
        break;
    case 'user_history':
        $controller->userHistory();
        break;
    case 'edit_user':
        $controller->editUser();
        break;
    case 'add_user':
        $controller->addUserForm();
        break;
    case 'handleAddUser':
        $controller->handleAddUser();
        break;
    case 'handleEditUser':
        $controller->handleEditUser();
        break;
    case 'changeUserRole':
        $controller->changeUserRole();
        break;
    case 'toggleUserStatus':
        $controller->toggleUserStatus();
        break;
    case 'banUser':
        $controller->banUser();
        break;
    case 'deleteUser':
        $controller->deleteUser();
        break;

    // ========== GESTION ARTICLES (toi) ==========
    case 'adminArticles':
        $articleController->adminArticles();
        break;
    case 'addArticleForm':
        $articleController->addArticleForm();
        break;
    case 'addArticle':
        $articleController->addArticle();
        break;
    case 'editArticleForm':
        $articleController->editArticleForm();
        break;
    case 'editArticle':
        $articleController->editArticle();
        break;
    case 'deleteArticle':
        $articleController->deleteArticle();
        break;

    // ========== GESTION AVIS (toi - À AJOUTER) ==========
    case 'adminAvis':
        $avisController->adminAvis();
        break;
    case 'editAvisForm':
        $avisController->editForm();
        break;
    case 'editAvis':
        $avisController->edit();
        break;
    case 'approveAvis':
        $avisController->approve();
        break;
    case 'rejectAvis':
        $avisController->reject();
        break;
    case 'deleteAvis':
        $avisController->delete();
        break;

    // ========== NEWSLETTER (toi) ==========
    case 'adminNewsletter':
        $articleController->adminNewsletter();
        break;
    case 'sendNewsletter':
        $articleController->sendNewsletter();
        break;

    // ========== STATISTIQUES (toi - À AJOUTER) ==========
    case 'statsEvolution':
        require_once __DIR__ . '/Controller/StatistiqueController.php';
        $statController = new StatistiqueController();
        $statController->evolutionArticles();
        break;

    // ========== EVENEMENTS (Zalouni) ==========
    case 'evenements':
        $evenementController->adminList();
        break;
    case 'evenementForm':
        $evenementController->adminForm();
        break;
    case 'evenementShow':
        $evenementController->adminShow();
        break;
    case 'evenementStats':
        $evenementController->adminStats();
        break;
    case 'evenementExportPdf':
        $evenementController->exportPdf();
        break;

    // ========== PARTICIPANTS (Zalouni) ==========
    case 'participants':
        $participantController->adminList();
        break;
    case 'participantForm':
        $participantController->adminForm();
        break;

    // ========== DÉCHETS (Fares) - Back Office ==========
    case 'dechet_index':
        $dechetController->index();
        break;
    case 'dechet_stats':
        $dechetController->stats();
        break;
    case 'dechet_create':
        $dechetController->create();
        break;
    case 'dechet_store':
        $dechetController->store();
        break;
    case 'dechet_edit':
        $dechetController->edit();
        break;
    case 'dechet_update':
        $dechetController->update();
        break;
    case 'dechet_delete':
        $dechetController->delete();
        break;

    // ========== COLLECTES (Fares) - Back Office ==========
    case 'collecte_index':
        $collecteController->index();
        break;
    case 'collecte_show':
        $collecteController->show();
        break;
    case 'collecte_recap':
        $collecteController->recap();
        break;
    case 'collecte_store':
        $collecteController->store();
        break;
    case 'collecte_update':
        $collecteController->update();
        break;
    case 'collecte_delete':
        $collecteController->delete();
        break;

    // ========== CATÉGORIES (Fares) - Back Office ==========
    case 'category_index':
        $categoryController->index();
        break;
    case 'category_store':
        $categoryController->store();
        break;
    case 'category_update':
        $categoryController->update();
        break;
    case 'category_delete':
        $categoryController->delete();
        break;

    // ========== MÉTIERS (Fares) - Back Office ==========
    case 'metier_index':
        $metierController->index();
        break;
    case 'metier_store':
        $metierController->store();
        break;
    case 'metier_update':
        $metierController->update();
        break;
    case 'metier_delete':
        $metierController->delete();
        break;

    default:
        header('Location: admin.php?action=dashboard');
        exit;
}
?>