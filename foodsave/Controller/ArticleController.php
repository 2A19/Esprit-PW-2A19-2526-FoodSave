<?php
require_once __DIR__ . '/../Model/Article.php';
require_once __DIR__ . '/../Model/Avis.php';
class ArticleController {
    private $conn;
    private $articleModel;
    private $avisModel;

    public function __construct() {
        $this->conn = config::getConnexion();
        $this->articleModel = new Article();
        $this->avisModel = new Avis();
    }

    // ========== FRONT OFFICE ==========
    
    public function blog() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 6;
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $order = ($order == 'ASC') ? 'ASC' : 'DESC';
        
        $articles = $this->articleModel->getArticlesPagines($page, $limit, $order);
        $totalArticles = $this->articleModel->countTotalArticles();
        $totalPages = ceil($totalArticles / $limit);
        
        include __DIR__ . '/../View/Front/blog/blog.php';
    }

    public function detail() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $article = $this->articleModel->getById($id);
            
            $nbAvis = $this->avisModel->countByArticleId($id);
            $noteMoyenne = $this->avisModel->getAverageNote($id);
            
            $query = "SELECT a.*, a.user_id, CONCAT(u.prenom, ' ', u.nom) as user_name 
                      FROM avis a
                      INNER JOIN user u ON a.user_id = u.id
                      WHERE a.article_id = :article_id AND a.statut = 'approuvé'
                      ORDER BY a.created_at DESC LIMIT 2";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':article_id', $id);
            $stmt->execute();
            $derniersAvis = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $video_id = null;
            if(!empty($article['video_url'])) {
                $video_id = $this->getYouTubeId($article['video_url']);
            }
            
            include __DIR__ . '/../View/Front/blog/article_detail.php';
        }
    }

    public function conseils() {
        $articles = $this->articleModel->getByCategorie('Conseils');
        include __DIR__ . '/../View/Front/blog/conseils.php';
    }

    public function recettes() {
        $articles = $this->articleModel->getByCategorie('Recettes');
        include __DIR__ . '/../View/Front/blog/recettes.php';
    }
    
    public function rechercher() {
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        if(empty($search)) {
            header('Location: index.php?action=blog');
            exit;
        }
        $articles = $this->articleModel->rechercherParTitre($search);
        $totalArticles = count($articles);
        $totalPages = 1;
        $page = 1;
        $order = 'DESC';
        include __DIR__ . '/../View/Front/blog/blog.php';
    }

    private function getYouTubeId($url) {
        if (empty($url)) return null;
        $pattern = '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
        preg_match($pattern, $url, $matches);
        return $matches[1] ?? null;
    }

    // ========== BACK OFFICE - ARTICLES ==========

    public function adminArticles() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        $articles = $this->articleModel->getAll();
        $totalArticles = $this->articleModel->count();
        $totalPublished = $this->articleModel->countPublished();
        $totalDrafts = $this->articleModel->countDrafts();
        $totalViews = $this->articleModel->totalViews();
        include __DIR__ . '/../View/Back/blog/gestion_articles.php';
    }

    public function addArticleForm() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        include __DIR__ . '/../View/Back/blog/ajouter_article.php';
    }

    public function addArticle() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titre'];
            $categorie = $_POST['categorie'];
            $resume = $_POST['resume'];
            $contenu = $_POST['contenu'];
            $statut = $_POST['statut'];
            $video_url = $_POST['video_url'] ?? null;
            
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $image = time() . '_' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
            }
            
            $articleId = $this->articleModel->create($titre, $categorie, $resume, $contenu, $image, $statut, $video_url);
            
            if ($articleId) {
                header('Location: admin.php?action=adminArticles&success=created');
            }
        }
    }

    public function editArticleForm() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $article = $this->articleModel->getById($id);
            include __DIR__ . '/../View/Back/blog/modifier_article.php';
        }
    }

    public function editArticle() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $titre = $_POST['titre'];
            $categorie = $_POST['categorie'];
            $resume = $_POST['resume'];
            $contenu = $_POST['contenu'];
            $statut = $_POST['statut'];
            $video_url = $_POST['video_url'] ?? null;
            
            $oldArticle = $this->articleModel->getById($id);
            $oldImage = $oldArticle['image'] ?? '';
            
            $supprimerImage = isset($_POST['supprimer_image']) && $_POST['supprimer_image'] == '1';
            $hasNewImage = isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK;
            
            $image = $oldImage;
            
            if ($supprimerImage && !$hasNewImage) {
                if (!empty($oldImage) && file_exists(__DIR__ . '/../assets/uploads/' . $oldImage)) {
                    unlink(__DIR__ . '/../assets/uploads/' . $oldImage);
                }
                $image = '';
            }
            
            if ($hasNewImage) {
                $uploadDir = __DIR__ . '/../assets/uploads/';
                if (!empty($oldImage) && file_exists($uploadDir . $oldImage)) {
                    unlink($uploadDir . $oldImage);
                }
                $image = time() . '_' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
            }
            
            if ($this->articleModel->update($id, $titre, $categorie, $resume, $contenu, $image, $statut, $video_url)) {
                header('Location: admin.php?action=adminArticles&success=updated');
            }
        }
    }

    public function deleteArticle() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $article = $this->articleModel->getById($id);
            if (!empty($article['image']) && file_exists(__DIR__ . '/../assets/uploads/' . $article['image'])) {
                unlink(__DIR__ . '/../assets/uploads/' . $article['image']);
            }
            if ($this->articleModel->delete($id)) {
                header('Location: admin.php?action=adminArticles&success=deleted');
            }
        }
    }

    // ========== NEWSLETTER ==========

    public function newsletterSubscribe() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $nom = trim($_POST['nom'] ?? '');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email invalide']);
                return;
            }
            
            try {
                $check = $this->conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = :email");
                $check->bindParam(':email', $email);
                $check->execute();
                
                if($check->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Cet email est déjà inscrit']);
                    return;
                }
                
                $insert = $this->conn->prepare("INSERT INTO newsletter_subscribers (email, nom) VALUES (:email, :nom)");
                $insert->bindParam(':email', $email);
                $insert->bindParam(':nom', $nom);
                
                if($insert->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Inscription réussie !']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'insertion']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
            }
            return;
        }
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    }
    
    public function adminNewsletter() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        $subscribersCount = $this->articleModel->countSubscribers();
        $articles = $this->articleModel->getAllPublished();
        include __DIR__ . '/../View/Back/blog/admin_newsletter.php';
    }

    public function sendNewsletter() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sujet = trim($_POST['sujet'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $article_id = $_POST['article_id'] ?? null;
            
            if (empty($sujet) || empty($message)) {
                $_SESSION['newsletter_error'] = "Veuillez remplir tous les champs";
                header('Location: index.php?action=adminNewsletter');
                exit;
            }
            
            $subscribers = $this->articleModel->getSubscribers();
            if (empty($subscribers)) {
                $_SESSION['newsletter_error'] = "Aucun abonné à la newsletter";
                header('Location: index.php?action=adminNewsletter');
                exit;
            }
            
            $sent = 0;
            foreach($subscribers as $sub) {
                if($this->sendMail($sub['email'], $sub['nom'], $sujet, $message, $article_id)) {
                    $sent++;
                }
            }
            $_SESSION['newsletter_success'] = "Newsletter envoyée à $sent abonné(s)";
            header('Location: index.php?action=adminNewsletter');
            exit;
        }
    }

    private function sendMail($to, $nom, $sujet, $message, $article_id = null) {
        $sujet = "📧 FoodSave - " . $sujet;
        $article_url = '';
        if($article_id) {
            $article_url = "http://" . $_SERVER['HTTP_HOST'] . "/foodsaveforum/foodsave/index.php?action=detail&id=" . $article_id;
        }
        
        $contenu = "<html><head><style>
            body{font-family:Arial,sans-serif;background:#0d1f14;color:#e8f5e9;padding:20px}
            .container{max-width:600px;margin:0 auto;background:rgba(255,255,255,0.05);border-radius:20px;padding:30px}
            .btn{display:inline-block;padding:10px 20px;background:#16a34a;color:#fff;border-radius:50px;text-decoration:none}
        </style></head><body>
            <div class='container'><h2>🍽️ FoodSave</h2>
            <p>Bonjour " . htmlspecialchars($nom ?? 'cher abonné') . ",</p>
            <div>" . nl2br(htmlspecialchars($message)) . "</div>";
        if($article_url) {
            $contenu .= "<p><a href='" . $article_url . "' class='btn'>Lire l'article</a></p>";
        }
        $contenu .= "<hr><p>FoodSave - Mangez mieux, gaspillez moins</p></div></body></html>";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: FoodSave <newsletter@foodsave.com>\r\n";
        return mail($to, $sujet, $contenu, $headers);
    }

    // ========== CHATBOT ==========

    public function chatbot() {
        // Nettoyer tout ce qui aurait pu être envoyé
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Définir les headers en premier
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        
        try {
            // Vérifier la méthode
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                $response = ['success' => false, 'response' => 'Méthode non autorisée'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            // Récupérer la question
            $question = isset($_POST['question']) ? trim($_POST['question']) : '';
            
            if (empty($question)) {
                http_response_code(400);
                $response = ['success' => false, 'response' => 'Veuillez poser une question.'];
                echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            
            // Obtenir la réponse
            $reply = $this->askAI($question);
            
            // Répondre
            http_response_code(200);
            $response = ['success' => true, 'response' => $reply];
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
            
        } catch (Throwable $e) {
            http_response_code(500);
            $response = ['success' => false, 'response' => 'Erreur: ' . $e->getMessage()];
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    private function askAI($question) {
        // Fallback immédiat si curl n'existe pas
        if (!function_exists('curl_init')) {
            return $this->getOfflineResponse($question);
        }
        
        try {
            $apiKey = 'sU2zIj76Ii7G6u8tjquMtIfwBJDQ1R4P';
            
            // Initialiser curl avec vérification d'erreur
            $ch = @curl_init('https://api.mistral.ai/v1/chat/completions');
            if ($ch === false) {
                return $this->getOfflineResponse($question);
            }
            
            // Préparer les données
            $data = [
                'model' => 'mistral-tiny',
                'messages' => [
                    ['role' => 'system', 'content' => 'Tu es un assistant expert en lutte contre le gaspillage alimentaire. Tu réponds en français.'],
                    ['role' => 'user', 'content' => $question]
                ],
                'temperature' => 0.7,
                'max_tokens' => 300
            ];
            
            $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
            
            // Configuration curl
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            
            // Exécuter
            $result = @curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            // Traiter les erreurs curl
            if ($error || $result === false) {
                return $this->getOfflineResponse($question);
            }
            
            // Succès
            if ($httpCode === 200) {
                $decoded = json_decode($result, true);
                if (isset($decoded['choices'][0]['message']['content'])) {
                    return $decoded['choices'][0]['message']['content'];
                }
            }
            
            // Autres codes = fallback
            return $this->getOfflineResponse($question);
            
        } catch (Throwable $e) {
            return $this->getOfflineResponse($question);
        }
    }

    private function getOfflineResponse($question) {
        $q = strtolower($question);
        
        // Base de réponses
        $map = [
            'conservation|stocker|frais|réfrigérer|conserver|durée|combien|longtemps' => 
                "📦 Conservation des aliments:\n- Légumes frais: 1-2 semaines\n- Viande: 2-3 jours\n- Fruits: 5-7 jours\n- Restes: 3-4 jours max\n\nTip: Fermez bien les contenants!",
            
            'reste|résidu|surplus|recette|utiliser|pain|légume' => 
                "♻️ Réduire les restes:\n- Pain sec → croutons\n- Légumes fanés → soupe\n- Fruits abîmés → compote\n- Viande cuite → sandwich\n- Fromage dur → râper\n\nQuelle recette?",
            
            'gaspillage|perdu|jeter|déchet' => 
                "🌍 Lutter contre le gaspillage:\n- Planifiez vos courses\n- Vérifiez le frigo\n- Congelez avant expiration\n- Achetez juste\n\nVous cherchez des idées?",
            
            'congel|surgel|freezer|glaçon' => 
                "❄️ Congélation:\n- Durée: viande 3-4 mois, légumes 8-12, fruits 10-12\n- Étiquetez tout\n- Décongelez au frigo\n- Ne recongelez pas!\n\nQuel aliment?",
            
            'plan|liste|course|achat|budget|manger' => 
                "🛒 Planifier les courses:\n- Listez vos repas\n- Vérifiez le frigo\n- Achetez frais\n- Dates d'expiration\n\nMeal planning?",
        ];
        
        // Chercher correspondance
        foreach ($map as $keywords => $response) {
            foreach (explode('|', $keywords) as $keyword) {
                if (strpos($q, trim($keyword)) !== false) {
                    return $response;
                }
            }
        }
        
        // Défaut
        return "👋 Assistant FoodSave IA\n\nJe peux vous aider sur:\n- Conservation des aliments\n- Recettes pour restes\n- Réduire le gaspillage\n- Congélation\n- Planification repas\n\nVotre question?";
    }

    // ========== FRONT OFFICE - AVIS ==========

    public function showAvis() {
        if (isset($_GET['article_id'])) {
            $article_id = $_GET['article_id'];
            $article = $this->articleModel->getById($article_id);
            $avis = $this->avisModel->getByArticleId($article_id);
            $nbAvis = $this->avisModel->countByArticleId($article_id);
            $noteMoyenne = $this->avisModel->getAverageNote($article_id);
            include __DIR__ . '/../View/Front/blog/avis.php';
        }
    }

    public function addAvisForm() {
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

    public function addAvis() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_id'])) {
            $article_id = $_POST['article_id'];
            $note = (int)$_POST['note'] ?? 5;
            $contenu = trim($_POST['contenu'] ?? '');
            $user_id = $_SESSION['user_id'];

            if (empty($contenu) || $note < 1 || $note > 5) {
                $_SESSION['error'] = "Tous les champs sont obligatoires et la note doit être entre 1 et 5";
                header('Location: index.php?action=addAvisForm&article_id=' . $article_id);
                exit;
            }

            $this->avisModel->create($article_id, $user_id, $contenu, $note);
            $_SESSION['success'] = "Votre avis a été soumis pour modération";
            header('Location: index.php?action=detail&id=' . $article_id);
            exit;
        }
    }

    public function editUserAvis() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $avis_id = $_GET['id'];
            $avis = $this->avisModel->getById($avis_id);
            
            if ($avis['user_id'] != $_SESSION['user_id']) {
                $_SESSION['error'] = "Vous ne pouvez modifier que vos propres avis";
                header('Location: index.php?action=blog');
                exit;
            }

            $article = $this->articleModel->getById($avis['article_id']);
            include __DIR__ . '/../View/Front/blog/modifier_mon_avis.php';
        }
    }

    public function editAvis() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $avis_id = $_POST['id'];
            $avis = $this->avisModel->getById($avis_id);

            if ($avis['user_id'] != $_SESSION['user_id']) {
                $_SESSION['error'] = "Vous ne pouvez modifier que vos propres avis";
                header('Location: index.php?action=blog');
                exit;
            }

            $note = (int)$_POST['note'] ?? 5;
            $contenu = trim($_POST['contenu'] ?? '');

            if (empty($contenu) || $note < 1 || $note > 5) {
                $_SESSION['error'] = "Tous les champs sont obligatoires et la note doit être entre 1 et 5";
                header('Location: index.php?action=editUserAvis&id=' . $avis_id);
                exit;
            }

            $this->avisModel->update($avis_id, $contenu, $note);
            $_SESSION['success'] = "Votre avis a été modifié";
            header('Location: index.php?action=detail&id=' . $avis['article_id']);
            exit;
        }
    }

    public function deleteAvis() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $avis_id = $_GET['id'];
            $avis = $this->avisModel->getById($avis_id);

            if ($avis['user_id'] != $_SESSION['user_id']) {
                $_SESSION['error'] = "Vous ne pouvez supprimer que vos propres avis";
                header('Location: index.php?action=blog');
                exit;
            }

            $article_id = $avis['article_id'];
            $this->avisModel->delete($avis_id);
            $_SESSION['success'] = "Votre avis a été supprimé";
            header('Location: index.php?action=detail&id=' . $article_id);
            exit;
        }
    }

    // ========== BACK OFFICE - AVIS ==========

    public function adminAvis() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        $avis = $this->avisModel->getAll();
        $totalAvis = $this->avisModel->count();
        $totalPending = $this->avisModel->countByStatus('pending');
        $totalApproved = $this->avisModel->countByStatus('approuvé');
        $averageNote = $this->avisModel->getAverageNoteAll();
        include __DIR__ . '/../View/Back/blog/gestion_avis.php';
    }

    public function approveAvis() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $avis_id = $_GET['id'];
            $this->avisModel->updateStatus($avis_id, 'approuvé');
            header('Location: admin.php?action=adminAvis&success=approved');
            exit;
        }
    }

    public function deleteAvisAdmin() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $avis_id = $_GET['id'];
            $this->avisModel->delete($avis_id);
            header('Location: admin.php?action=adminAvis&success=deleted');
            exit;
        }
    }

    public function editAvisAdmin() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if (isset($_GET['id'])) {
            $avis_id = $_GET['id'];
            $avis = $this->avisModel->getById($avis_id);
            include __DIR__ . '/../View/Back/blog/modifier_avis.php';
        }
    }

    public function handleEditAvisAdmin() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $avis_id = $_POST['id'];
            $note = (int)$_POST['note'] ?? 5;
            $contenu = trim($_POST['contenu'] ?? '');
            $statut = $_POST['statut'] ?? 'pending';

            if (empty($contenu) || $note < 1 || $note > 5) {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
                header('Location: admin.php?action=editAvisForm&id=' . $avis_id);
                exit;
            }

            $this->avisModel->updateFull($avis_id, $contenu, $note, $statut);
            $_SESSION['success'] = "L'avis a été modifié";
            header('Location: admin.php?action=adminAvis');
            exit;
        }
    }
}
?>