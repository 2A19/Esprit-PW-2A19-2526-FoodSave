<?php
// app/controllers/ArticleController.php

require_once __DIR__ . '/../models/Article.php';

class ArticleController {
    private $articleModel;

    public function __construct() {
        $this->articleModel = new Article();
    }

    // ========== MÉTHODES EXISTANTES ==========
    
    public function blog() {
        $articles = $this->articleModel->getAllPublished();
        require_once __DIR__ . '/../views/front/blog/blog.php';
    }

    public function detail() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $article = $this->articleModel->getById($id);
            require_once __DIR__ . '/../views/front/blog/article_detail.php';
        }
    }

    public function conseils() {
        $articles = $this->articleModel->getByCategorie('Conseils');
        require_once __DIR__ . '/../views/front/blog/conseils.php';
    }

    public function recettes() {
        $articles = $this->articleModel->getByCategorie('Recettes');
        require_once __DIR__ . '/../views/front/blog/recettes.php';
    }

    // ========== NOUVELLES MÉTHODES CRUD ==========

    // Admin : afficher la liste des articles
    public function adminArticles() {
        // LIGNE DE TEST - AJOUTÉE
        echo "TEST : La méthode adminArticles est appelée ! Le fichier va être chargé...<br>";
        
        $articles = $this->articleModel->getAll();
        $totalArticles = $this->articleModel->count();
        $totalPublished = $this->articleModel->countPublished();
        $totalDrafts = $this->articleModel->countDrafts();
        $totalViews = $this->articleModel->totalViews();
        
        require_once __DIR__ . '/../views/back/blog/gestion_articles.php';
    }

    // Admin : formulaire d'ajout
    public function addArticleForm() {
        require_once __DIR__ . '/../views/back/blog/ajouter_article.php';
    }

    // Admin : ajouter un article
    public function addArticle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titre'];
            $categorie = $_POST['categorie'];
            $resume = $_POST['resume'];
            $contenu = $_POST['contenu'];
            $statut = $_POST['statut'];
            
            // Gestion de l'upload d'image
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $image = time() . '_' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
            }
            
            if ($this->articleModel->create($titre, $categorie, $resume, $contenu, $image, $statut)) {
                header('Location: index.php?action=adminArticles&success=created');
            } else {
                $error = "Erreur lors de la création";
            }
        }
    }

    // Admin : formulaire de modification
    public function editArticleForm() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $article = $this->articleModel->getById($id);
            require_once __DIR__ . '/../views/back/blog/modifier_article.php';
        }
    }

    // Admin : modifier un article
    public function editArticle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $titre = $_POST['titre'];
            $categorie = $_POST['categorie'];
            $resume = $_POST['resume'];
            $contenu = $_POST['contenu'];
            $statut = $_POST['statut'];
            
            // Gestion de l'upload d'image
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                $image = time() . '_' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
            }
            
            if ($this->articleModel->update($id, $titre, $categorie, $resume, $contenu, $image, $statut)) {
                header('Location: index.php?action=adminArticles&success=updated');
            }
        }
    }

    // Admin : supprimer un article
    public function deleteArticle() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->articleModel->delete($id)) {
                header('Location: index.php?action=adminArticles&success=deleted');
            }
        }
    }
}
?>