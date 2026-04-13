<?php
session_start();

require_once __DIR__ . '/Controller/UserController.php';

$action = $_GET['action'] ?? 'login';
$controller = new UserController();

switch ($action) {
    // Front Office Routes
    case 'login':
        $controller->login();
        break;
    case 'handleLogin':
        $controller->handleLogin();
        break;
    case 'register':
        $controller->register();
        break;
    case 'handleRegister':
        $controller->handleRegister();
        break;
    case 'dashboard':
        $controller->dashboard();
        break;
    case 'profile':
        $controller->profile();
        break;
    case 'editProfile':
        $controller->editProfile();
        break;
    case 'handleEditProfile':
        $controller->handleEditProfile();
        break;
    case 'logout':
        $controller->logout();
        break;
    default:
        header('Location: index.php?action=login');
        exit;
}
?>
