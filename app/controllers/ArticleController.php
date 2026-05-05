<?php
// app/controllers/ArticleController.php

require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../../config/database.php';

class ArticleController {
    private $articleModel;
    private $conn;

    public function __construct() {
        $this->articleModel = new Article();
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ========== MÉTHODES EXISTANTES ==========
    
    public function blog() {
        // Récupérer la page courante (par défaut page 1)
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 6; // 6 articles par page
        
        // Récupérer l'ordre de tri (par défaut DESC = plus récents d'abord)
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        // Sécuriser : seulement DESC ou ASC
        $order = ($order == 'ASC') ? 'ASC' : 'DESC';
        
        // Récupérer les articles avec pagination et tri
        $articles = $this->articleModel->getArticlesPagines($page, $limit, $order);
        $totalArticles = $this->articleModel->countTotalArticles();
        $totalPages = ceil($totalArticles / $limit);
        
        require_once __DIR__ . '/../views/front/blog/blog.php';
    }

    public function detail() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $article = $this->articleModel->getById($id);
            
            // Récupération des données des avis
            require_once __DIR__ . '/../models/Avis.php';
            $avisModel = new Avis();
            
            $nbAvis = $avisModel->countByArticleId($id);
            $noteMoyenne = $avisModel->getAverageNote($id);
            
            $query = "SELECT a.*, a.user_id, CONCAT(u.prenom, ' ', u.nom) as user_name 
                      FROM avis a
                      INNER JOIN user u ON a.user_id = u.id
                      WHERE a.article_id = :article_id AND a.statut = 'approuvé'
                      ORDER BY a.created_at DESC LIMIT 2";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':article_id', $id);
            $stmt->execute();
            $derniersAvis = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // ===== VIDÉO YOUTUBE : Récupérer l'ID de la vidéo =====
            $video_id = null;
            if(!empty($article['video_url'])) {
                $video_id = $this->getYouTubeId($article['video_url']);
            }
            
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
        
        require_once __DIR__ . '/../views/front/blog/blog.php';
    }

    // ========== MÉTHODE POUR EXTRAIRE L'ID YOUTUBE ==========
    
    /**
     * Extrait l'ID d'une vidéo YouTube depuis son URL
     * @param string $url L'URL de la vidéo YouTube
     * @return string|null L'ID de la vidéo ou null
     */
    private function getYouTubeId($url) {
        if (empty($url)) return null;
        
        $pattern = '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
        preg_match($pattern, $url, $matches);
        
        return $matches[1] ?? null;
    }

    // ========== NOUVELLES MÉTHODES CRUD ==========

    // Admin : afficher la liste des articles
    public function adminArticles() {
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

    // Admin : ajouter un article (avec notification aux abonnés)
    public function addArticle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titre'];
            $categorie = $_POST['categorie'];
            $resume = $_POST['resume'];
            $contenu = $_POST['contenu'];
            $statut = $_POST['statut'];
            $video_url = $_POST['video_url'] ?? null;
            
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
            
            $articleId = $this->articleModel->create($titre, $categorie, $resume, $contenu, $image, $statut, $video_url);
            
            if ($articleId) {
                // Si l'article est publié, envoyer une notification aux abonnés
                if ($statut === 'publié') {
                    $article = $this->articleModel->getById($articleId);
                    $sent = $this->notifySubscribersNewArticle($article);
                    if ($sent > 0) {
                        $_SESSION['newsletter_success'] = "Article créé et notification envoyée à $sent abonné(s)";
                    }
                }
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

    // Admin : modifier un article (avec notification si passe de brouillon à publié)
    public function editArticle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = $_POST['id'];
            $titre = $_POST['titre'];
            $categorie = $_POST['categorie'];
            $resume = $_POST['resume'];
            $contenu = $_POST['contenu'];
            $statut = $_POST['statut'];
            $video_url = $_POST['video_url'] ?? null;
            
            // Récupérer l'ancien statut
            $oldArticle = $this->articleModel->getById($id);
            $oldStatut = $oldArticle['statut'];
            
            // Gestion de l'upload d'image
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                $image = time() . '_' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
            }
            
            if ($this->articleModel->update($id, $titre, $categorie, $resume, $contenu, $image, $statut, $video_url)) {
                // Si l'article passe de brouillon à publié, envoyer une notification
                if ($oldStatut !== 'publié' && $statut === 'publié') {
                    $article = $this->articleModel->getById($id);
                    $sent = $this->notifySubscribersNewArticle($article);
                    if ($sent > 0) {
                        $_SESSION['newsletter_success'] = "Article publié et notification envoyée à $sent abonné(s)";
                    }
                }
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

    // ========== MÉTHODES POUR LA NEWSLETTER ==========

    /**
     * Widget newsletter (à inclure dans les pages)
     */
    public function newsletterWidget() {
        require_once __DIR__ . '/../views/front/blog/newsletter_widget.php';
    }

    /**
     * Inscription à la newsletter (AJAX)
     */
    public function newsletterSubscribe() {
        header('Content-Type: application/json');
        
        // Forcer l'affichage des erreurs
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $nom = trim($_POST['nom'] ?? '');
            
            // Validation simple
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email invalide']);
                return;
            }
            
            try {
                // Vérifier si la table existe
                $checkTable = $this->conn->query("SHOW TABLES LIKE 'newsletter_subscribers'");
                if($checkTable->rowCount() == 0) {
                    // Créer la table
                    $this->conn->exec("CREATE TABLE newsletter_subscribers (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        email VARCHAR(150) NOT NULL UNIQUE,
                        nom VARCHAR(100),
                        date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
                        statut ENUM('actif', 'desactive') DEFAULT 'actif'
                    )");
                }
                
                // Vérifier si l'email existe déjà
                $check = $this->conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = :email");
                $check->bindParam(':email', $email);
                $check->execute();
                
                if($check->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Cet email est déjà inscrit']);
                    return;
                }
                
                // Insérer
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
    
    /**
     * Page admin pour envoyer la newsletter
     */
    public function adminNewsletter() {
        // Récupérer le nombre d'abonnés
        $subscribersCount = $this->articleModel->countSubscribers();
        // Récupérer les articles pour le select
        $articles = $this->articleModel->getAllPublished();
        
        require_once __DIR__ . '/../views/back/blog/admin_newsletter.php';
    }

    /**
     * Envoie la newsletter aux abonnés
     */
    public function sendNewsletter() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sujet = trim($_POST['sujet'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $article_id = $_POST['article_id'] ?? null;
            
            if (empty($sujet) || empty($message)) {
                $_SESSION['newsletter_error'] = "Veuillez remplir tous les champs";
                header('Location: index.php?action=adminNewsletter');
                exit;
            }
            
            // Récupérer les abonnés
            $subscribers = $this->articleModel->getSubscribers();
            
            if (empty($subscribers)) {
                $_SESSION['newsletter_error'] = "Aucun abonné à la newsletter";
                header('Location: index.php?action=adminNewsletter');
                exit;
            }
            
            // Pour chaque abonné, envoyer un email
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

    /**
     * Envoi d'un email individuel
     * @param string $to Email du destinataire
     * @param string $nom Nom du destinataire
     * @param string $sujet Sujet de l'email
     * @param string $message Message de l'email
     * @param int|null $article_id ID de l'article (optionnel)
     * @return bool
     */
    private function sendMail($to, $nom, $sujet, $message, $article_id = null) {
        $sujet = "📧 FoodSave - " . $sujet;
        
        // Récupérer l'URL de l'article si fourni
        $article_url = '';
        if($article_id) {
            $article_url = "http://" . $_SERVER['HTTP_HOST'] . "/FoodSave/public/index.php?action=detail&id=" . $article_id;
        }
        
        // Construire le message HTML
        $contenu = "
        <html>
        <head>
            <style>
                body{font-family:Arial,sans-serif;background:#0d1f14;color:#e8f5e9;padding:20px}
                .container{max-width:600px;margin:0 auto;background:rgba(255,255,255,0.05);border-radius:20px;padding:30px}
                .header{text-align:center;margin-bottom:20px}
                .btn{display:inline-block;padding:10px 20px;background:#16a34a;color:#fff;border-radius:50px;text-decoration:none}
                .footer{text-align:center;margin-top:20px;font-size:12px;color:#888}
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='color:#4ade80'>🍽️ FoodSave</h2>
                </div>
                <p>Bonjour " . htmlspecialchars($nom ?? 'cher abonné') . ",</p>
                <div>" . nl2br(htmlspecialchars($message)) . "</div>";
        
        if($article_url) {
            $contenu .= "<p style='margin-top:20px;'><a href='" . $article_url . "' class='btn'>Lire l'article</a></p>";
        }
        
        $contenu .= "
                <hr>
                <p class='footer'>FoodSave - Mangez mieux, gaspillez moins<br>
                <a href='#' style='color:#888'>Se désabonner</a></p>
            </div>
        </body>
        </html>";
        
        // Headers pour email HTML
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: FoodSave <newsletter@foodsave.com>" . "\r\n";
        
        return mail($to, $sujet, $contenu, $headers);
    }

    // ========== MÉTHODES POUR NOTIFIER LES ABONNÉS ==========

    /**
     * Envoie une notification aux abonnés pour un nouvel article
     * @param array $article Les données de l'article
     * @return int Nombre d'emails envoyés
     */
    private function notifySubscribersNewArticle($article) {
        $subscribers = $this->articleModel->getSubscribers();
        
        if (empty($subscribers)) {
            return 0;
        }
        
        $sujet = "Nouvel article sur FoodSave !";
        
        // Message HTML propre et bien formé
        $messageHtml = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Nouvel article FoodSave</title>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f5f0e8; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
                .header { text-align: center; margin-bottom: 25px; }
                .logo { font-size: 24px; font-weight: bold; color: #16a34a; }
                .article-title { font-size: 22px; color: #2d2a24; margin: 15px 0 10px 0; }
                .category { display: inline-block; background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 50px; font-size: 12px; margin-bottom: 15px; }
                .resume { color: #555; line-height: 1.6; margin: 15px 0; }
                .btn { display: inline-block; padding: 12px 24px; background: #16a34a; color: #ffffff; text-decoration: none; border-radius: 50px; font-weight: bold; margin: 20px 0; }
                .footer { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #888; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">🍽️ FoodSave</div>
                </div>
                <p>Bonjour <strong>' . htmlspecialchars($article['titre']) . '</strong>,</p>
                <p>Un nouvel article vient d\'être publié sur FoodSave :</p>
                
                <div class="article-title">📝 ' . htmlspecialchars($article['titre']) . '</div>
                <div class="category">📂 ' . htmlspecialchars($article['categorie']) . '</div>
                <div class="resume">' . nl2br(htmlspecialchars(substr(strip_tags($article['resume']), 0, 300))) . '...</div>
                
                <p style="text-align: center;">
                    <a href="http://' . $_SERVER['HTTP_HOST'] . '/FoodSave/public/index.php?action=detail&id=' . $article['id'] . '" class="btn">📖 Lire l\'article</a>
                </p>
                
                <div class="footer">
                    <p>FoodSave - Mangez mieux, gaspillez moins</p>
                    <p><a href="#" style="color: #888;">Se désabonner</a></p>
                </div>
            </div>
        </body>
        </html>';
        
        $sent = 0;
        foreach($subscribers as $sub) {
            // Passer le nom dans le message
            $messageWithName = str_replace('cher abonné', htmlspecialchars($sub['nom'] ?? 'cher abonné'), $messageHtml);
            if($this->sendSimpleMail($sub['email'], $sub['nom'], $sujet, $messageWithName)) {
                $sent++;
            }
        }
        
        return $sent;
    }

    /**
     * Envoi d'un email simple avec PHPMailer
     * @param string $to Email du destinataire
     * @param string $nom Nom du destinataire
     * @param string $sujet Sujet de l'email
     * @param string $message Message HTML de l'email
     * @return bool
     */
    private function sendSimpleMail($to, $nom, $sujet, $message) {
        require_once __DIR__ . '/../../vendor/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../../vendor/phpmailer/src/SMTP.php';
        require_once __DIR__ . '/../../vendor/phpmailer/src/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host       = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = '0a56aea2c86b2a';
            $mail->Password   = '8d35cb149ca88f';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 2525;
            
            // Encodage UTF-8 pour éviter les symboles bizarres
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            
            // Expéditeur et destinataire
            $mail->setFrom('newsletter@foodsave.com', 'FoodSave');
            $mail->addAddress($to, $nom ?? 'Abonné');
            
            // Contenu (propre et bien formaté)
            $mail->isHTML(true);
            $mail->Subject = '📧 ' . $sujet;
            
            // Nettoyer le message (enlever les balises HTML mal formées)
            $cleanMessage = preg_replace('/\s+/', ' ', $message);
            $cleanMessage = str_replace('> <', '><', $cleanMessage);
            
            $mail->Body = $cleanMessage;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $message));
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email non envoyé à $to: " . $mail->ErrorInfo);
            return false;
        }
    }

    // ========== CHATBOT IA ==========

    /**
     * Chatbot IA - Répond aux questions des utilisateurs
     */
    // ========== CHATBOT IA ==========

/**
 * Chatbot IA (en attente de configuration)
 */
// ========== CHATBOT IA ==========

public function chatbot() {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $question = trim($_POST['question'] ?? '');
        
        if (empty($question)) {
            echo json_encode(['success' => false, 'response' => 'Veuillez poser une question.']);
            return;
        }
        
        $response = $this->askAI($question);
        echo json_encode(['success' => true, 'response' => $response]);
        return;
    }
    
    echo json_encode(['success' => false, 'response' => 'Méthode non autorisée']);
}

private function askAI($question) {
    $apiKey = 'sU2zIj76Ii7G6u8tjquMtIfwBJDQ1R4P';
    
    $ch = curl_init('https://api.mistral.ai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $data = [
        'model' => 'mistral-tiny',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Tu es un assistant expert en lutte contre le gaspillage alimentaire. Tu réponds de manière utile et concise.'
            ],
            [
                'role' => 'user',
                'content' => $question
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 300
    ];
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $response = json_decode($result, true);
        if (isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        return "Je n'ai pas compris. Pouvez-vous reformuler ?";
    } else {
        error_log("Mistral API Error: HTTP $httpCode - $result");
        return "Désolé, le service IA est temporairement indisponible. Veuillez réessayer plus tard.";
    }
}
}
?>