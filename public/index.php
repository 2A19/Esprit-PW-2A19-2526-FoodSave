<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Charger le traducteur
require_once 'C:/xampp/htdocs/FoodSave/app/core/Translator.php';
$translator = Translator::getInstance();
$lang = $translator->getCurrentLang();

require_once 'C:/xampp/htdocs/FoodSave/app/controllers/ArticleController.php';
require_once 'C:/xampp/htdocs/FoodSave/app/controllers/AvisController.php';
require_once 'C:/xampp/htdocs/FoodSave/app/models/Article.php';
require_once 'C:/xampp/htdocs/FoodSave/app/models/Avis.php';
require_once 'C:/xampp/htdocs/FoodSave/config/database.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'blog';

$articleController = new ArticleController();
$avisController = new AvisController();

switch($action) {
    // ========== FRONT OFFICE - ARTICLES ==========
    case 'blog':
        $articleController->blog();
        break;
    case 'detail':
        $articleController->detail();
        break;
    case 'conseils':
        $articleController->conseils();
        break;
    case 'recettes':
        $articleController->recettes();
        break;
    case 'rechercher':
        $articleController->rechercher();
        break;
    
    // ========== BACK OFFICE - ARTICLES (CRUD) ==========
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
    
    // ========== FRONT OFFICE - AVIS ==========
    case 'showAvis':
        $avisController->show();
        break;
    case 'addAvisForm':
        $avisController->addForm();
        break;
    case 'addAvis':
        $avisController->add();
        break;
    
    // ========== BACK OFFICE - AVIS ==========
    case 'adminAvis':
        $avisController->adminAvis();
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
    case 'editAvisForm':
        $avisController->editForm();
        break;
    case 'editAvis':
        $avisController->edit();
        break;
    
    // ========== FRONT OFFICE - MODIFICATION AVIS PAR USER ==========
    case 'editUserAvis':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $avisController->editUser();
        } else {
            $avisController->editUserForm();
        }
        break;
    
    // ========== NOTIFICATIONS ==========
    case 'clearNotification':
        if (isset($_GET['key']) && isset($_SESSION['notifications'][$_GET['key']])) {
            unset($_SESSION['notifications'][$_GET['key']]);
        }
        header('Location: index.php?action=adminAvis');
        break;
    
    case 'clearAllNotifications':
        if (isset($_SESSION['notifications'])) {
            $_SESSION['notifications'] = [];
        }
        header('Location: index.php?action=adminAvis');
        break;
    
    case 'markNotificationsRead':
        if (isset($_SESSION['notifications'])) {
            foreach ($_SESSION['notifications'] as $key => $notif) {
                $_SESSION['notifications'][$key]['lu'] = true;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        break;
    
    case 'checkNotifications':
        require_once 'C:/xampp/htdocs/FoodSave/public/check_notifications.php';
        break;
    
    // ========== STATISTIQUES ==========
    case 'statsEvolution':
        require_once 'C:/xampp/htdocs/FoodSave/app/controllers/StatistiqueController.php';
        $statController = new StatistiqueController();
        $statController->evolutionArticles();
        break;
    
    // ========== NEWSLETTER ==========
    case 'newsletterSubscribe':
        $articleController->newsletterSubscribe();
        break;
    
    case 'adminNewsletter':
        $articleController->adminNewsletter();
        break;
    
    case 'sendNewsletter':
        $articleController->sendNewsletter();
        break;
        // ========== CHATBOT IA ==========
case 'chatbot':
    $articleController->chatbot();
    break;
    
    // ========== PAGE PAR DÉFAUT ==========
    default:
        $articleController->blog();
        break;
}
?>