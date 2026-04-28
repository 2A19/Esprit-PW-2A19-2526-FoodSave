<?php
// app/controllers/AvisController.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../models/Avis.php';
require_once __DIR__ . '/../models/Article.php';

class AvisController {
    private $avisModel;
    private $articleModel;

    public function __construct() {
        $this->avisModel = new Avis();
        $this->articleModel = new Article();
    }

    // ========== FRONT OFFICE ==========

    // Afficher les avis d'un article
    // Afficher les avis d'un article
public function show() {
    if (isset($_GET['article_id'])) {
        $article_id = $_GET['article_id'];
        $article = $this->articleModel->getById($article_id);
        
        // Vérifier si l'article existe
        if (!$article) {
            header('Location: index.php?action=blog');
            exit;
        }
        
        $avis = $this->avisModel->getByArticleId($article_id);
        $nbAvis = $this->avisModel->countByArticleId($article_id);
        $noteMoyenne = $this->avisModel->getAverageNote($article_id);
        
        require_once __DIR__ . '/../views/front/blog/avis.php';
    } else {
        header('Location: index.php?action=blog');
        exit;
    }
}

    // Formulaire d'ajout d'avis
    public function addForm() {
        if (isset($_GET['article_id'])) {
            $article_id = $_GET['article_id'];
            $article = $this->articleModel->getById($article_id);
            require_once __DIR__ . '/../views/front/blog/ajouter_avis.php';
        }
    }

    // Traiter l'ajout d'un avis
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $article_id = $_POST['article_id'];
            $user_id = $_SESSION['user_id'] ?? 1; // Temporaire : user_id = 1
            $contenu = $_POST['contenu'];
            $note = $_POST['note'];
            $statut = 'en attente'; // Par défaut, en attente de modération
            
            if ($this->avisModel->create($article_id, $user_id, $contenu, $note, $statut)) {
                header('Location: index.php?action=detail&id=' . $article_id . '&avis=success');
            } else {
                header('Location: index.php?action=addAvisForm&article_id=' . $article_id . '&error=1');
            }
        }
    }

    // ========== BACK OFFICE ==========

    // Admin : liste des avis
    public function adminAvis() {
        $avis = $this->avisModel->getAll();
        $totalAvis = $this->avisModel->countTotal();
        $totalPending = $this->avisModel->countPending();
        $totalApproved = $this->avisModel->countApproved();
        $averageNote = $this->avisModel->averageNoteGlobal();
        
        require_once __DIR__ . '/../views/back/blog/gestion_avis.php';
    }

    // Admin : approuver un avis
    public function approve() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->avisModel->updateStatut($id, 'approuvé')) {
                header('Location: index.php?action=adminAvis&success=approved');
            }
        }
    }

    // Admin : rejeter un avis
    public function reject() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->avisModel->updateStatut($id, 'rejeté')) {
                header('Location: index.php?action=adminAvis&success=rejected');
            }
        }
    }

    // Admin : supprimer un avis
    public function delete() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->avisModel->delete($id)) {
                header('Location: index.php?action=adminAvis&success=deleted');
            }
        }
    }
    // Admin : formulaire de modification d'un avis
public function editForm() {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $avis = $this->avisModel->getById($id);
        require_once __DIR__ . '/../views/back/blog/modifier_avis.php';
    }
}

// Admin : traiter la modification d'un avis
public function edit() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $contenu = $_POST['contenu'];
        $note = $_POST['note'];
        
        if ($this->avisModel->update($id, $contenu, $note)) {
            header('Location: index.php?action=adminAvis&success=updated');
        } else {
            header('Location: index.php?action=editAvisForm&id=' . $id . '&error=1');
        }
    }
}
// ========== FRONT OFFICE - MODIFICATION PAR L'UTILISATEUR ==========

// Formulaire de modification d'avis (pour l'utilisateur)
public function editUserForm() {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $avis = $this->avisModel->getById($id);
        
        // Vérifier que l'avis appartient à l'utilisateur connecté
        $user_id = $_SESSION['user_id'] ?? 1;
        if ($avis['user_id'] != $user_id) {
            header('Location: index.php?action=blog');
            exit;
        }
        
        $article = $this->articleModel->getById($avis['article_id']);
        require_once __DIR__ . '/../views/front/blog/modifier_mon_avis.php';
    }
}

// Traiter la modification d'un avis (par l'utilisateur)
public function editUser() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $contenu = $_POST['contenu'];
        $note = $_POST['note'];
        
        // Vérifier que l'avis appartient à l'utilisateur
        $avis = $this->avisModel->getById($id);
        $user_id = $_SESSION['user_id'] ?? 1;
        if ($avis['user_id'] != $user_id) {
            header('Location: index.php?action=blog');
            exit;
        }
        
        // Mettre à jour l'avis
        if ($this->avisModel->update($id, $contenu, $note)) {
            // Remettre en attente pour modération
            $this->avisModel->updateStatut($id, 'en attente');
            header('Location: index.php?action=showAvis&article_id=' . $avis['article_id'] . '&success=updated');
        } else {
            header('Location: index.php?action=editUserAvis&id=' . $id . '&error=1');
        }
    }
}
}
?>