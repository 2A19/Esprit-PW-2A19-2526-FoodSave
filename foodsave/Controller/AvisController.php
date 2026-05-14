<?php
class AvisController {
    private $conn;
    private $avisModel;
    private $articleModel;

    public function __construct() {
        $this->conn = config::getConnexion();
        $this->avisModel = new Avis();
        $this->articleModel = new Article();
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
            
            include __DIR__ . '/../View/Front/blog/avis.php';
        }
    }

    public function addForm() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['article_id'])) {
            $article_id = $_GET['article_id'];
            $article = $this->articleModel->getById($article_id);
            include __DIR__ . '/../View/Front/blog/ajouter_avis.php';
        }
    }

    public function add() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $article_id = $_POST['article_id'];
            $user_id = $_SESSION['user_id'];
            $contenu = $_POST['contenu'];
            $note = $_POST['note'];
            $statut = 'en attente';
            
            if ($this->avisModel->create($article_id, $user_id, $contenu, $note, $statut)) {
                header('Location: index.php?action=detail&id=' . $article_id . '&avis=success');
                exit;
            }
        }
    }

    // ========== BACK OFFICE ==========

    public function adminAvis() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
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
        
        include __DIR__ . '/../View/Back/blog/gestion_avis.php';
    }

    public function approve() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->avisModel->updateStatut($id, 'approuvé')) {
                header('Location: admin.php?action=adminAvis&success=approved');
            }
        }
    }

    public function reject() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->avisModel->updateStatut($id, 'rejeté')) {
                header('Location: admin.php?action=adminAvis&success=rejected');
            }
        }
    }

    public function delete() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->avisModel->delete($id)) {
                header('Location: admin.php?action=adminAvis&success=deleted');
            }
        }
    }
    
    public function editForm() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $avis = $this->avisModel->getById($id);
            include __DIR__ . '/../View/Back/blog/modifier_avis.php';
        }
    }

    public function edit() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $contenu = $_POST['contenu'];
            $note = $_POST['note'];
            if ($this->avisModel->update($id, $contenu, $note)) {
                header('Location: admin.php?action=adminAvis&success=updated');
            }
        }
    }

    // ========== MODIFICATION PAR L'UTILISATEUR ==========

    public function editUserForm() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $avis = $this->avisModel->getById($id);
            if ($avis['user_id'] != $_SESSION['user_id']) {
                header('Location: index.php?action=blog');
                exit;
            }
            $article = $this->articleModel->getById($avis['article_id']);
            include __DIR__ . '/../View/Front/blog/modifier_mon_avis.php';
        }
    }

    public function editUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $contenu = $_POST['contenu'];
            $note = $_POST['note'];
            $avis = $this->avisModel->getById($id);
            if ($avis['user_id'] != $_SESSION['user_id']) {
                header('Location: index.php?action=blog');
                exit;
            }
            if ($this->avisModel->update($id, $contenu, $note)) {
                $this->avisModel->updateStatut($id, 'en attente');
                header('Location: index.php?action=showAvis&article_id=' . $avis['article_id'] . '&success=updated');
            }
        }
    }
}
?>