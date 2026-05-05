<?php
// app/controllers/AvisController.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../models/Avis.php';
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../../config/database.php';

class AvisController {
    private $avisModel;
    private $articleModel;
    private $conn;

    public function __construct() {
        $this->avisModel = new Avis();
        $this->articleModel = new Article();
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ========== FRONT OFFICE ==========

    public function show() {
        if (isset($_GET['article_id'])) {
            $article_id = $_GET['article_id'];
            $article = $this->articleModel->getById($article_id);
            
            if (!$article) {
                header('Location: index.php?action=blog');
                exit;
            }
            
            // CORRECTION: Table 'user' (singulier) et ajout de a.user_id
            $query = "SELECT a.*, a.user_id, CONCAT(u.prenom, ' ', u.nom) as user_name 
                      FROM avis a
                      INNER JOIN user u ON a.user_id = u.id
                      WHERE a.article_id = :article_id AND a.statut = 'approuvé'
                      ORDER BY a.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':article_id', $article_id);
            $stmt->execute();
            $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $nbAvis = $this->avisModel->countByArticleId($article_id);
            $noteMoyenne = $this->avisModel->getAverageNote($article_id);
            
            require_once __DIR__ . '/../views/front/blog/avis.php';
        } else {
            header('Location: index.php?action=blog');
            exit;
        }
    }

    public function addForm() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_GET['article_id'])) {
            $article_id = $_GET['article_id'];
            $article = $this->articleModel->getById($article_id);
            require_once __DIR__ . '/../views/front/blog/ajouter_avis.php';
        }
    }

    public function add() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $article_id = $_POST['article_id'];
            
            if (!isset($_SESSION['user_id'])) {
                header('Location: index.php?action=login&error=notlogged');
                exit;
            }
            
            $user_id = $_SESSION['user_id'];
            $contenu = $_POST['contenu'];
            $note = $_POST['note'];
            $statut = 'en attente';
            
            if ($this->avisModel->create($article_id, $user_id, $contenu, $note, $statut)) {
                $this->envoyerNotificationAdmin($article_id);
                header('Location: index.php?action=detail&id=' . $article_id . '&avis=success');
                exit;
            } else {
                header('Location: index.php?action=addAvisForm&article_id=' . $article_id . '&error=1');
            }
        }
    }

    // ========== BACK OFFICE ==========

    public function adminAvis() {
        // CORRECTION: Table 'user' (singulier) et ajout de a.user_id
        $query = "SELECT a.*, a.user_id, CONCAT(u.prenom, ' ', u.nom) as user_name, art.titre as article_titre
                  FROM avis a
                  INNER JOIN user u ON a.user_id = u.id
                  INNER JOIN articles art ON a.article_id = art.id
                  ORDER BY a.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalAvis = $this->avisModel->countTotal();
        $totalPending = $this->avisModel->countPending();
        $totalApproved = $this->avisModel->countApproved();
        $averageNote = $this->avisModel->averageNoteGlobal();
        
        require_once __DIR__ . '/../views/back/blog/gestion_avis.php';
    }

    public function approve() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->avisModel->updateStatut($id, 'approuvé')) {
                header('Location: index.php?action=adminAvis&success=approved');
            }
        }
    }

    public function reject() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->avisModel->updateStatut($id, 'rejeté')) {
                header('Location: index.php?action=adminAvis&success=rejected');
            }
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->avisModel->delete($id)) {
                header('Location: index.php?action=adminAvis&success=deleted');
            }
        }
    }
    
    public function editForm() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $avis = $this->avisModel->getById($id);
            require_once __DIR__ . '/../views/back/blog/modifier_avis.php';
        }
    }

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

    public function editUserForm() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $avis = $this->avisModel->getById($id);
            
            if (!isset($_SESSION['user_id'])) {
                header('Location: index.php?action=login');
                exit;
            }
            
            $user_id = $_SESSION['user_id'];
            if ($avis['user_id'] != $user_id) {
                header('Location: index.php?action=blog');
                exit;
            }
            $article = $this->articleModel->getById($avis['article_id']);
            require_once __DIR__ . '/../views/front/blog/modifier_mon_avis.php';
        }
    }

    public function editUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $contenu = $_POST['contenu'];
            $note = $_POST['note'];
            $avis = $this->avisModel->getById($id);
            
            if (!isset($_SESSION['user_id'])) {
                header('Location: index.php?action=login');
                exit;
            }
            
            $user_id = $_SESSION['user_id'];
            if ($avis['user_id'] != $user_id) {
                header('Location: index.php?action=blog');
                exit;
            }
            if ($this->avisModel->update($id, $contenu, $note)) {
                $this->avisModel->updateStatut($id, 'en attente');
                header('Location: index.php?action=showAvis&article_id=' . $avis['article_id'] . '&success=updated');
            } else {
                header('Location: index.php?action=editUserAvis&id=' . $id . '&error=1');
            }
        }
    }

    // ========== NOTIFICATION (SIMULÉE) ==========

    private function envoyerNotificationAdmin($article_id) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['notifications'][] = [
            'type' => 'avis',
            'message' => "📝 Un nouvel avis a été posté sur l'article #$article_id. Il est en attente de modération.",
            'article_id' => $article_id,
            'date' => date('Y-m-d H:i:s'),
            'lu' => false
        ];
    }
}
?>