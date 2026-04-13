<?php
require_once __DIR__ . '/../Model/User.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Affiche la page de login
     */
    public function login() {
        include __DIR__ . '/../View/Front/user/login.html';
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
        $password = htmlspecialchars(trim($_POST['password'] ?? ''));

        $user = $this->userModel->login($email, $password);

        if ($user) {
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
        } else {
            $errors = $this->userModel->errors;
            include __DIR__ . '/../View/Front/user/login.html';
        }
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=register');
            exit;
        }

        $this->userModel->prenom = htmlspecialchars(trim($_POST['prenom'] ?? ''));
        $this->userModel->nom = htmlspecialchars(trim($_POST['nom'] ?? ''));
        $this->userModel->email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $this->userModel->password = htmlspecialchars(trim($_POST['password'] ?? ''));
        $this->userModel->telephone = htmlspecialchars(trim($_POST['telephone'] ?? ''));
        $this->userModel->date_naissance = htmlspecialchars(trim($_POST['date_naissance'] ?? ''));

        if ($this->userModel->create()) {
            $_SESSION['success'] = 'Inscription réussie ! Veuillez vous connecter.';
            header('Location: index.php?action=login');
            exit;
        } else {
            $errors = $this->userModel->errors;
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

        $user = $this->userModel->getById($_SESSION['user_id']);
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

        $user = $this->userModel->getById($_SESSION['user_id']);
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

        $user = $this->userModel->getById($_SESSION['user_id']);
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

        $this->userModel->id = $_SESSION['user_id'];
        $this->userModel->prenom = htmlspecialchars(trim($_POST['prenom'] ?? ''));
        $this->userModel->nom = htmlspecialchars(trim($_POST['nom'] ?? ''));
        $this->userModel->email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $this->userModel->telephone = htmlspecialchars(trim($_POST['telephone'] ?? ''));
        $this->userModel->date_naissance = htmlspecialchars(trim($_POST['date_naissance'] ?? ''));

        if ($this->userModel->update($_SESSION['user_id'])) {
            $_SESSION['success'] = 'Profil mis à jour avec succès !';
            $_SESSION['user_prenom'] = $this->userModel->prenom;
            $_SESSION['user_nom'] = $this->userModel->nom;
            $_SESSION['user_email'] = $this->userModel->email;
            header('Location: index.php?action=profile');
            exit;
        } else {
            $errors = $this->userModel->errors;
            $user = $this->userModel->getById($_SESSION['user_id']);
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

        $users = $this->userModel->getAll();
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

        $users = $this->userModel->getAll();
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

        $user = $this->userModel->getById($userId);
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

        $user = $this->userModel->getById($userId);
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

        $this->userModel->id = $userId;
        $this->userModel->prenom = htmlspecialchars(trim($_POST['prenom'] ?? ''));
        $this->userModel->nom = htmlspecialchars(trim($_POST['nom'] ?? ''));
        $this->userModel->email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $this->userModel->telephone = htmlspecialchars(trim($_POST['telephone'] ?? ''));
        $this->userModel->date_naissance = htmlspecialchars(trim($_POST['date_naissance'] ?? ''));
        $this->userModel->statut = htmlspecialchars(trim($_POST['statut'] ?? ''));

        if ($this->userModel->update($userId)) {
            $_SESSION['success'] = 'Utilisateur mis à jour avec succès !';
            header('Location: admin.php?action=user_details&id=' . $userId);
            exit;
        } else {
            $errors = $this->userModel->errors;
            $user = $this->userModel->getById($userId);
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

        if ($this->userModel->changeRole($userId, $role)) {
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

        if ($this->userModel->delete($userId)) {
            $_SESSION['success'] = 'Utilisateur supprimé avec succès !';
            header('Location: admin.php?action=users');
            exit;
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression de l\'utilisateur';
            header('Location: admin.php?action=user_details&id=' . $userId);
            exit;
        }
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
}
?>
