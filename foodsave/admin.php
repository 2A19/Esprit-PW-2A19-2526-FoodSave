<?php
session_start();

require_once __DIR__ . '/Controller/UserController.php';

// Vérifier que l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php?action=login');
    exit;
}

$action = $_GET['action'] ?? 'dashboard';
$controller = new UserController();

switch ($action) {
    // Backend/Admin Routes
    case 'dashboard':
        $controller->adminDashboard();
        break;
    case 'users':
        $controller->usersList();
        break;
    case 'user_details':
        $controller->userDetails();
        break;
    case 'edit_user':
        $controller->editUser();
        break;
    case 'handleEditUser':
        $controller->handleEditUser();
        break;
    case 'changeUserRole':
        $controller->changeUserRole();
        break;
    case 'deleteUser':
        $controller->deleteUser();
        break;
    default:
        header('Location: admin.php?action=dashboard');
        exit;
}
?>
