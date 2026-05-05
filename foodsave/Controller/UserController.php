<?php
include(__DIR__ . '/../config/config.php');
include(__DIR__ . '/../Model/User.php');
include(__DIR__ . '/../Model/PasswordResetCode.php');

class UserController {
    private function isStrongPassword(string $password): bool {
        return strlen($password) >= 8
            && preg_match('/[a-z]/', $password)
            && preg_match('/[A-Z]/', $password)
            && preg_match('/\d/', $password);
    }

    private function sendBrevoResetCode(string $email, string $code): array {
        if (trim(BREVO_API_KEY) === '') {
            return [false, 'BREVO_API_KEY non configuré.'];
        }

        $payload = [
            'sender' => [
                'name' => BREVO_SENDER_NAME,
                'email' => BREVO_SENDER_EMAIL,
            ],
            'to' => [[
                'email' => $email,
            ]],
            'subject' => 'Code de réinitialisation FoodSave',
            'htmlContent' => '<p>Bonjour,</p>'
                . '<p>Voici votre code de réinitialisation FoodSave :</p>'
                . '<p style="font-size:26px;font-weight:bold;letter-spacing:4px;">' . $code . '</p>'
                . '<p>Ce code expire dans 10 minutes.</p>'
                . '<p>Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.</p>',
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'api-key: ' . BREVO_API_KEY,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 20,
        ]);
        $resp = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return [false, 'Erreur réseau Brevo: ' . $curlErr];
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            return [false, 'Brevo a refusé l\'envoi (HTTP ' . $httpCode . ').'];
        }
        return [true, null];
    }


    /**
     * Récupère tous les utilisateurs avec statistiques (JOIN avec listes)
     */
    public function listUsers() {
        $sql = "SELECT 
                    u.id,
                    u.nom,
                    u.prenom,
                    u.email,
                    u.telephone,
                    u.date_naissance,
                    u.role,
                    u.statut,
                    u.date_inscription,
                    COUNT(DISTINCT l.id) as nombre_listes,
                    COUNT(DISTINCT al.id) as nombre_articles
                FROM user u
                LEFT JOIN listes l ON u.id = l.user_id AND l.statut = 'active'
                LEFT JOIN articles_liste al ON l.id = al.liste_id
                GROUP BY u.id
                ORDER BY u.date_inscription DESC";
        
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deleteUserById($id) {
        $sql = "DELETE FROM user WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addUser(User $user) {
        $sql = "INSERT INTO user (nom, prenom, email, password, telephone, date_naissance, role, statut, date_inscription) 
                VALUES (:nom, :prenom, :email, :password, :telephone, :date_naissance, :role, :statut, :date_inscription)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'password' => password_hash($user->getPassword(), PASSWORD_BCRYPT),
                'telephone' => $user->getTelephone(),
                'date_naissance' => $user->getDateNaissance() ? $user->getDateNaissance()->format('Y-m-d') : null,
                'role' => $user->getRole(),
                'statut' => $user->getStatut(),
                'date_inscription' => $user->getDateInscription() ? $user->getDateInscription()->format('Y-m-d H:i:s') : null
            ]);
            return true;
        } catch (Exception $e) {
            error_log('Erreur lors de l\'ajout utilisateur: ' . $e->getMessage());
            return false;
        }
    }

    public function updateUser(User $user, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE user SET
                    prenom = :prenom,
                    nom = :nom,
                    email = :email,
                    telephone = :telephone,
                    date_naissance = :date_naissance,
                    role = :role,
                    statut = :statut
                WHERE id = :id'
            );
            $query->execute([
                'id' => $id,
                'prenom' => $user->getPrenom(),
                'nom' => $user->getNom(),
                'email' => $user->getEmail(),
                'telephone' => $user->getTelephone(),
                'date_naissance' => $user->getDateNaissance() ? $user->getDateNaissance()->format('Y-m-d') : null,
                'role' => $user->getRole(),
                'statut' => $user->getStatut()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showUser($id) {
        $sql = "SELECT * FROM user WHERE id = $id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);

        try {
            $query->execute();
            $user = $query->fetch();
            return $user;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Affiche la page de login
     */
    public function login() {
        include __DIR__ . '/../View/Front/user/login.html';
    }

    public function forgotPassword() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        include __DIR__ . '/../View/Front/user/forgot_password.html';
    }

    public function handleForgotPasswordSendCode() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=forgotPassword');
            exit;
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Veuillez saisir une adresse email valide.';
            header('Location: index.php?action=forgotPassword');
            exit;
        }

        $db = config::getConnexion();
        $stmt = $db->prepare('SELECT id, email FROM user WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            PasswordResetCode::createCode((int) $user['id'], $user['email'], $code, 10);
            [$ok, $error] = $this->sendBrevoResetCode($user['email'], $code);
            if (!$ok) {
                $_SESSION['error'] = $error ?: 'Impossible d\'envoyer l\'email de réinitialisation.';
                header('Location: index.php?action=forgotPassword');
                exit;
            }
        }

        $_SESSION['password_reset_email'] = $email;
        $_SESSION['password_reset_step'] = 'code';
        $_SESSION['success'] = 'Si cet email existe, un code à 6 chiffres a été envoyé.';
        header('Location: index.php?action=forgotPassword');
        exit;
    }

    public function handleForgotPasswordVerifyCode() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=forgotPassword');
            exit;
        }

        $email = trim((string) ($_SESSION['password_reset_email'] ?? ''));
        $code = preg_replace('/\D+/', '', (string) ($_POST['code'] ?? ''));
        if (!$email || strlen($code) !== 6) {
            $_SESSION['error'] = 'Code invalide. Entrez un code à 6 chiffres.';
            $_SESSION['password_reset_step'] = 'code';
            header('Location: index.php?action=forgotPassword');
            exit;
        }

        $ok = PasswordResetCode::verifyCode($email, $code);
        if (!$ok) {
            $_SESSION['error'] = 'Code incorrect, expiré, ou trop de tentatives.';
            $_SESSION['password_reset_step'] = 'code';
            header('Location: index.php?action=forgotPassword');
            exit;
        }

        $_SESSION['password_reset_step'] = 'new_password';
        $_SESSION['success'] = 'Code validé. Choisissez votre nouveau mot de passe.';
        header('Location: index.php?action=forgotPassword');
        exit;
    }

    public function handleForgotPasswordReset() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=forgotPassword');
            exit;
        }

        $email = trim((string) ($_SESSION['password_reset_email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        if (!$email || !PasswordResetCode::hasVerifiedCode($email)) {
            $_SESSION['error'] = 'Session de réinitialisation invalide. Recommencez.';
            unset($_SESSION['password_reset_step'], $_SESSION['password_reset_email']);
            header('Location: index.php?action=forgotPassword');
            exit;
        }
        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'Les mots de passe ne correspondent pas.';
            $_SESSION['password_reset_step'] = 'new_password';
            header('Location: index.php?action=forgotPassword');
            exit;
        }
        if (!$this->isStrongPassword($password)) {
            $_SESSION['error'] = 'Le mot de passe doit contenir 8 caractères, une majuscule, une minuscule et un chiffre.';
            $_SESSION['password_reset_step'] = 'new_password';
            header('Location: index.php?action=forgotPassword');
            exit;
        }

        $db = config::getConnexion();
        $stmt = $db->prepare('UPDATE user SET password = :password WHERE email = :email');
        $stmt->execute([
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'email' => $email,
        ]);
        PasswordResetCode::consumeCodes($email);

        unset($_SESSION['password_reset_step'], $_SESSION['password_reset_email']);
        $_SESSION['success'] = 'Mot de passe mis à jour. Vous pouvez vous connecter.';
        header('Location: index.php?action=login');
        exit;
    }

    /**
     * Traite la soumission du login
     */
    public function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=login');
            exit;
        }

        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';  // NE PAS modifier le mot de passe

        if (empty($email) || empty($password)) {
            $errors['login'] = 'Veuillez remplir tous les champs';
            include __DIR__ . '/../View/Front/user/login.html';
            return;
        }

        $db = config::getConnexion();
        $query = 'SELECT * FROM user WHERE email = :email LIMIT 1';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            $errors['login'] = 'Email ou mot de passe incorrect';
            include __DIR__ . '/../View/Front/user/login.html';
            return;
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier le mot de passe avec password_verify (bcrypt)
        if (!password_verify($password, $user['password'])) {
            $errors['login'] = 'Email ou mot de passe incorrect';
            include __DIR__ . '/../View/Front/user/login.html';
            return;
        }

        // Vérifier le statut de l'utilisateur
        if ($user['statut'] !== 'actif') {
            $errors['login'] = 'Votre compte n\'est pas actif. Veuillez contacter l\'administrateur.';
            include __DIR__ . '/../View/Front/user/login.html';
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin.php?action=dashboard');
        } else {
            header('Location: index.php?action=dashboard');
        }
        exit;
    }

    /**
     * Affiche la page d'inscription
     */
    public function register() {
        include __DIR__ . '/../View/Front/user/register.html';
    }

    /**
     * Traite la soumission de l'inscription
     */
    public function handleRegister() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=register');
            exit;
        }

        $user = new User();
        $user->setPrenom(htmlspecialchars(trim($_POST['prenom'] ?? '')));
        $user->setNom(htmlspecialchars(trim($_POST['nom'] ?? '')));
        $user->setEmail(htmlspecialchars(trim($_POST['email'] ?? '')));
        $user->setPassword(htmlspecialchars(trim($_POST['password'] ?? '')));
        $user->setTelephone(htmlspecialchars(trim($_POST['telephone'] ?? '')));
        $date_naissance = htmlspecialchars(trim($_POST['date_naissance'] ?? ''));
        if ($date_naissance) {
            $user->setDateNaissance(new DateTime($date_naissance));
        }
        $user->setRole('user');
        $user->setStatut('actif');
        $user->setDateInscription(new DateTime());

        if ($user->validate()) {
            if ($this->addUser($user)) {
                $_SESSION['success'] = 'Inscription réussie ! Veuillez vous connecter.';
                header('Location: index.php?action=login');
                exit;
            } else {
                $errors['general'] = 'Erreur lors de la création du compte. Veuillez réessayer.';
                include __DIR__ . '/../View/Front/user/register.html';
            }
        } else {
            $errors = $user->errors;
            include __DIR__ . '/../View/Front/user/register.html';
        }
    }

    /**
     * Affiche le dashboard utilisateur
     */
    public function dashboard() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $user = $this->showUser($_SESSION['user_id']);
        include __DIR__ . '/../View/Front/user/dashboard.html';
    }

    /**
     * Affiche le profil utilisateur
     */
    public function profile() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $user = $this->showUser($_SESSION['user_id']);
        include __DIR__ . '/../View/Front/user/profile.html';
    }

    /**
     * Affiche la page d'édition du profil
     */
    public function editProfile() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $user = $this->showUser($_SESSION['user_id']);
        include __DIR__ . '/../View/Front/user/edit_profile.html';
    }

    /**
     * Traite la mise à jour du profil
     */
    public function handleEditProfile() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=profile');
            exit;
        }

        $user = new User();
        $user->setId($_SESSION['user_id']);
        $user->setPrenom(htmlspecialchars(trim($_POST['prenom'] ?? '')));
        $user->setNom(htmlspecialchars(trim($_POST['nom'] ?? '')));
        $user->setEmail(htmlspecialchars(trim($_POST['email'] ?? '')));
        $user->setTelephone(htmlspecialchars(trim($_POST['telephone'] ?? '')));
        $date_naissance = htmlspecialchars(trim($_POST['date_naissance'] ?? ''));
        if ($date_naissance) {
            $user->setDateNaissance(new DateTime($date_naissance));
        }

        if ($user->validate()) {
            $this->updateUser($user, $_SESSION['user_id']);
            $_SESSION['success'] = 'Profil mis à jour avec succès !';
            $_SESSION['user_prenom'] = $user->getPrenom();
            $_SESSION['user_nom'] = $user->getNom();
            $_SESSION['user_email'] = $user->getEmail();
            header('Location: index.php?action=profile');
            exit;
        } else {
            $errors = $user->errors;
            $user = $this->showUser($_SESSION['user_id']);
            include __DIR__ . '/../View/Front/user/edit_profile.html';
        }
    }

    /**
     * Dashboard admin - Affiche la liste des utilisateurs
     */
    public function adminDashboard() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }

        $users = $this->listUsers();
        include __DIR__ . '/../View/Back/user/admin_dashboard.html';
    }

    /**
     * Affiche la liste complète des utilisateurs (admin)
     */
    public function usersList() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }

        $users = $this->listUsers();
        include __DIR__ . '/../View/Back/user/users_list.html';
    }

    /**
     * Affiche les détails d'un utilisateur (admin)
     */
    public function userDetails() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }

        $userId = $_GET['id'] ?? null;
        if (!$userId) {
            header('Location: admin.php?action=users');
            exit;
        }

        $user = $this->showUser($userId);
        if (!$user) {
            header('Location: admin.php?action=users');
            exit;
        }

        include __DIR__ . '/../View/Back/user/user_details.html';
    }

    /**
     * Affiche la page d'édition d'un utilisateur (admin)
     */
    public function editUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?action=login');
            exit;
        }

        $userId = $_GET['id'] ?? null;
        if (!$userId) {
            header('Location: admin.php?action=users');
            exit;
        }

        $user = $this->showUser($userId);
        if (!$user) {
            header('Location: admin.php?action=users');
            exit;
        }

        include __DIR__ . '/../View/Back/user/edit_user.html';
    }

    /**
     * Traite la mise à jour d'un utilisateur (admin)
     */
    public function handleEditUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin.php?action=users');
            exit;
        }

        $userId = $_POST['id'] ?? null;
        if (!$userId) {
            header('Location: admin.php?action=users');
            exit;
        }

        $user = new User();
        $user->setId($userId);
        $current = $this->showUser($userId);
        $user->setPrenom(htmlspecialchars(trim($_POST['prenom'] ?? '')));
        $user->setNom(htmlspecialchars(trim($_POST['nom'] ?? '')));
        $user->setEmail(htmlspecialchars(trim($_POST['email'] ?? '')));
        $user->setTelephone(htmlspecialchars(trim($_POST['telephone'] ?? '')));
        $date_naissance = htmlspecialchars(trim($_POST['date_naissance'] ?? ''));
        if ($date_naissance) {
            $user->setDateNaissance(new DateTime($date_naissance));
        }
        $user->setRole($current['role']);
        $user->setStatut(htmlspecialchars(trim($_POST['statut'] ?? '')));

        if ($user->validate()) {
            $this->updateUser($user, $userId);
            $_SESSION['success'] = 'Utilisateur mis à jour avec succès !';
            header('Location: admin.php?action=user_details&id=' . $userId);
            exit;
        } else {
            $errors = $user->errors;
            $user = $this->showUser($userId);
            include __DIR__ . '/../View/Back/user/edit_user.html';
        }
    }

    /**
     * Change le rôle d'un utilisateur (admin)
     */
    public function changeUserRole() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin.php?action=users');
            exit;
        }

        $userId = $_POST['id'] ?? null;
        $role = $_POST['role'] ?? null;

        if (!$userId || !$role) {
            header('Location: admin.php?action=users');
            exit;
        }

        if (!in_array($role, ['user', 'admin'])) {
            $_SESSION['error'] = 'Le rôle est invalide';
            header('Location: admin.php?action=user_details&id=' . $userId);
            exit;
        }

        $db = config::getConnexion();
        $query = 'UPDATE user SET role = :role WHERE id = :id';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->bindParam(':role', $role);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Rôle de l\'utilisateur modifié avec succès !';
            header('Location: admin.php?action=user_details&id=' . $userId);
            exit;
        } else {
            $_SESSION['error'] = 'Erreur lors de la modification du rôle';
            header('Location: admin.php?action=user_details&id=' . $userId);
            exit;
        }
    }

    /**
     * Supprime un utilisateur (admin)
     */
    public function deleteUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin.php?action=users');
            exit;
        }

        $userId = $_POST['id'] ?? null;
        if (!$userId) {
            header('Location: admin.php?action=users');
            exit;
        }

        $this->deleteUserById($userId);
        $_SESSION['success'] = 'Utilisateur supprimé avec succès !';
        header('Location: admin.php?action=users');
        exit;
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header('Location: index.php?action=login');
        exit;
    }

    /**
     * Récupère les statistiques d'un utilisateur avec ses listes et articles
     * JOIN entre user, listes, articles_liste, aliments
     */
    public function getUserStatistics($user_id) {
        $sql = "SELECT 
                    u.id as user_id,
                    u.prenom,
                    u.nom,
                    u.email,
                    COUNT(DISTINCT l.id) as total_listes,
                    COUNT(DISTINCT al.id) as total_articles,
                    COUNT(DISTINCT CASE WHEN al.statut = 'achete' THEN al.id END) as articles_achetes,
                    COUNT(DISTINCT CASE WHEN al.statut = 'a_acheter' THEN al.id END) as articles_a_acheter,
                    COUNT(DISTINCT CASE WHEN l.type = 'courses' THEN l.id END) as listes_courses,
                    COUNT(DISTINCT CASE WHEN l.type = 'stock' THEN l.id END) as listes_stock
                FROM user u
                LEFT JOIN listes l ON u.id = l.user_id AND l.statut = 'active'
                LEFT JOIN articles_liste al ON l.id = al.liste_id
                WHERE u.id = :user_id
                GROUP BY u.id";
        
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $req->execute();
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les statistiques globales du système (pour admin)
     * JOIN entre user, listes, recettes, aliments
     */
    public function getSystemStatistics() {
        $sql = "SELECT 
                    COUNT(DISTINCT u.id) as total_utilisateurs,
                    COUNT(DISTINCT CASE WHEN u.statut = 'actif' THEN u.id END) as utilisateurs_actifs,
                    COUNT(DISTINCT CASE WHEN u.role = 'admin' THEN u.id END) as admins,
                    COUNT(DISTINCT l.id) as total_listes,
                    COUNT(DISTINCT al.id) as total_articles,
                    COUNT(DISTINCT r.id) as total_recettes,
                    COUNT(DISTINCT a.id) as total_aliments
                FROM user u
                LEFT JOIN listes l ON u.id = l.user_id
                LEFT JOIN articles_liste al ON l.id = al.liste_id
                LEFT JOIN recettes r ON 1=1
                LEFT JOIN aliments a ON 1=1";
        
        $db = config::getConnexion();
        try {
            $stats = $db->query($sql);
            return $stats->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Récupère le top 10 des aliments les plus utilisés (JOIN avec articles_liste)
     */
    public function getTopAliments($limit = 10) {
        $sql = "SELECT 
                    a.id,
                    a.nom,
                    c.nom as categorie,
                    COUNT(al.id) as nombre_utilisations,
                    COUNT(DISTINCT l.user_id) as nombre_utilisateurs
                FROM aliments a
                LEFT JOIN categories c ON a.categorie_id = c.id
                LEFT JOIN articles_liste al ON a.id = al.aliment_id
                LEFT JOIN listes l ON al.liste_id = l.id
                GROUP BY a.id
                ORDER BY nombre_utilisations DESC
                LIMIT :limit";
        
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':limit', $limit, PDO::PARAM_INT);
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Récupère le détail d'un utilisateur avec toutes ses données
     * JOIN complet: user, listes, articles, aliments, recettes
     */
    public function getCompleteUserData($user_id) {
        $data = [
            'user' => $this->showUser($user_id),
            'statistics' => $this->getUserStatistics($user_id),
            'listes' => [],
            'recettes_favorites' => []
        ];
        
        // Récupérer les listes avec article count
        $sql_listes = "SELECT 
                        l.id,
                        l.titre,
                        l.type,
                        l.statut,
                        l.date_creation,
                        COUNT(al.id) as nombre_articles
                    FROM listes l
                    LEFT JOIN articles_liste al ON l.id = al.liste_id
                    WHERE l.user_id = :user_id
                    GROUP BY l.id
                    ORDER BY l.date_modification DESC";
        
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql_listes);
            $req->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $req->execute();
            $data['listes'] = $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
        
        return $data;
    }
}
?>
