<?php
/**
 * Main Application Router - Authentication & Navigation Hub
 * 
 * All requests go through here first.
 * - Unauthenticated users → FoodSave Login
 * - Authenticated users → Dashboard or Forum
 * - Admins → Can access backoffice
 */
session_start();

// ===== AUTHENTICATION CHECK =====
$isAuthenticated = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// If not authenticated, redirect to login
if (!$isAuthenticated) {
    header('Location: /foodsaveforum/foodsave/index.php?action=login');
    exit;
}

// Get the requested section
$section = $_GET['section'] ?? 'forum';
$action = $_GET['action'] ?? '';

// Route to appropriate location
switch ($section) {
    case 'dashboard':
    case 'home':
        header('Location: /foodsaveforum/foodsave/index.php?action=dashboard');
        exit;
    
    case 'profile':
        header('Location: /foodsaveforum/foodsave/index.php?action=profile');
        exit;
    
    case 'forum':
    default:
        header('Location: /foodsaveforum/forum/index.php?action=' . urlencode($action ?: 'list'));
        exit;
    
    case 'admin':
        if (!$isAdmin) {
            $_SESSION['error'] = 'Accès refusé. Vous devez être administrateur.';
            header('Location: /foodsaveforum/foodsave/index.php?action=dashboard');
            exit;
        }
        header('Location: /foodsaveforum/foodsave/admin.php?action=' . urlencode($action ?: 'dashboard'));
        exit;
    
    case 'forum-admin':
    case 'admin-forum':
        if (!$isAdmin) {
            $_SESSION['error'] = 'Accès refusé. Vous devez être administrateur.';
            header('Location: /foodsaveforum/foodsave/index.php?action=dashboard');
            exit;
        }
        header('Location: /foodsaveforum/forum/admin.php?action=' . urlencode($action ?: 'dashboard'));
        exit;
    
    case 'logout':
        session_destroy();
        header('Location: /foodsaveforum/foodsave/index.php?action=login');
        exit;
}

// If somehow we get here, load the old forum code as fallback
require_once __DIR__ . '/Model/Database.php';
require_once __DIR__ . '/Model/CommentaireModel.php';
require_once __DIR__ . '/Controller/PostController.php';
require_once __DIR__ . '/Controller/CommentaireController.php';

// Fallback: Show forum listing
header('Location: /foodsaveforum/forum/index.php?action=list');
exit;
?>
