<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'C:/xampp/htdocs/FoodSave/app/controllers/ArticleController.php';
require_once 'C:/xampp/htdocs/FoodSave/app/models/Article.php';
require_once 'C:/xampp/htdocs/FoodSave/config/database.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'blog';


$controller = new ArticleController();

switch($action) {
    // Front Office
    case 'blog':
        $controller->blog();
        break;
    case 'detail':
        $controller->detail();
        break;
    case 'conseils':
        $controller->conseils();
        break;
    case 'recettes':
        $controller->recettes();
        break;
    
    // Back Office (CRUD)
    case 'adminArticles':
        $controller->adminArticles();
        break;
    case 'addArticleForm':
        $controller->addArticleForm();
        break;
    case 'addArticle':
        $controller->addArticle();
        break;
    case 'editArticleForm':
        $controller->editArticleForm();
        break;
    case 'editArticle':
        $controller->editArticle();
        break;
    case 'deleteArticle':
        $controller->deleteArticle();
        break;
    
    default:
        $controller->blog();
        break;
}
?>