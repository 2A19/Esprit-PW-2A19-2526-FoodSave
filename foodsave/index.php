<?php
session_start();

require_once __DIR__ . '/Controller/UserController.php';
require_once __DIR__ . '/Controller/WebAuthnController.php';
require_once __DIR__ . '/Controller/ArticleController.php';
require_once __DIR__ . '/Controller/AvisController.php';
require_once __DIR__ . '/Controller/EvenementController.php';
require_once __DIR__ . '/Controller/DechetController.php';
require_once __DIR__ . '/Controller/CollecteController.php';
require_once __DIR__ . '/Controller/CategoryController.php';
require_once __DIR__ . '/Controller/MetierController.php';

$action = $_GET['action'] ?? 'login';
$controller = new UserController();
$webauthn = new WebAuthnController();
$articleController = new ArticleController();
$avisController = new AvisController();
$evenementController = new EvenementController();
$dechetController    = new DechetController();
$collecteController  = new CollecteController();
$categoryController  = new CategoryController();
$metierController    = new MetierController();
$isWebAuthnAction = strncmp((string) $action, 'webauthn', 8) === 0;

if ($isWebAuthnAction) {
    if (ob_get_level() === 0) {
        ob_start();
    }

    set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
}

try {
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
        case 'forgotPassword':
            $controller->forgotPassword();
            break;
        case 'handleForgotPasswordSendCode':
            $controller->handleForgotPasswordSendCode();
            break;
        case 'handleForgotPasswordVerifyCode':
            $controller->handleForgotPasswordVerifyCode();
            break;
        case 'handleForgotPasswordReset':
            $controller->handleForgotPasswordReset();
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

        // Blog Routes (Front Office)
        case 'blog':
            $articleController->blog();
            break;
        case 'detail':
            $articleController->detail();
            break;
        case 'conseils':
            $articleController->conseils();
            break;
        case 'recettes':
            $articleController->recettes();
            break;
        case 'rechercher':
            $articleController->rechercher();
            break;
        case 'newsletterSubscribe':
            $articleController->newsletterSubscribe();
            break;
        case 'chatbot':
            $articleController->chatbot();
            break;

        // Reviews Routes (Front Office)
        case 'showAvis':
            $avisController->show();
            break;
        case 'addAvisForm':
            $avisController->addForm();
            break;
        case 'addAvis':
            $avisController->add();
            break;
        case 'editUserAvis':
            $avisController->editUserForm();
            break;
        case 'editUserAvisSubmit':
            $avisController->editUser();
            break;

        // WebAuthn / passkey routes
        case 'webauthnRegisterOptions':
            $webauthn->optionsRegister();
            break;
        case 'webauthnRegisterVerify':
            $webauthn->verifyRegister();
            break;
        case 'webauthnLoginOptions':
            $webauthn->optionsLogin();
            break;
        case 'webauthnLoginVerify':
            $webauthn->verifyLogin();
            break;
        case 'webauthnPasskeysList':
            $webauthn->listMyPasskeys();
            break;
        case 'webauthnPasskeyDelete':
            $webauthn->deletePasskey();
            break;

        // ========== EVENEMENT Routes (Front Office) ==========
        case 'evenements':
            $evenementController->frontList();
            break;
        case 'evenementDetail':
            $evenementController->frontDetail();
            break;
        case 'evenementInscription':
            $evenementController->frontInscription();
            break;

        // ========== DÉCHETS (Fares) - Front Office ==========
        case 'dechet_index':
            $dechetController->index();
            break;
        case 'dechet_create':
            $dechetController->create();
            break;
        case 'dechet_store':
            $dechetController->store();
            break;
        case 'dechet_edit':
            $dechetController->edit();
            break;
        case 'dechet_update':
            $dechetController->update();
            break;
        case 'dechet_delete':
            $dechetController->delete();
            break;
        case 'dechet_search':
            $dechetController->search();
            break;

        // ========== COLLECTES (Fares) - Front Office ==========
        case 'collecte_index':
            $collecteController->index();
            break;
        case 'collecte_show':
            $collecteController->show();
            break;
        case 'collecte_recap':
            $collecteController->recap();
            break;
        case 'collecte_store':
            $collecteController->store();
            break;
        case 'collecte_update':
            $collecteController->update();
            break;
        case 'collecte_delete':
            $collecteController->delete();
            break;
        case 'collecte_search':
            $collecteController->search();
            break;

        // ========== CATÉGORIES (Fares) - Front Office ==========
        case 'category_index':
            $categoryController->index();
            break;
        case 'category_store':
            $categoryController->store();
            break;
        case 'category_update':
            $categoryController->update();
            break;
        case 'category_delete':
            $categoryController->delete();
            break;

        // ========== MÉTIERS (Fares) - Front Office ==========
        case 'metier_index':
            $metierController->index();
            break;
        case 'metier_store':
            $metierController->store();
            break;
        case 'metier_update':
            $metierController->update();
            break;
        case 'metier_delete':
            $metierController->delete();
            break;

        // ========== ACCUEIL (Landing page v4) ==========
        case 'accueil':
            require_once __DIR__ . '/View/Front/accueil.php';
            break;

        // ========== DASHBOARD SPA (v4 - Gestion déchets/collectes/catégories) ==========
        case 'dashboard_spa':
            require_once __DIR__ . '/View/Front/dashboard_spa.php';
            break;

        default:
            header('Location: index.php?action=accueil');
            exit;
    }
} catch (Throwable $e) {
    if (!$isWebAuthnAction) {
        throw $e;
    }

    if (ob_get_level() > 0) {
        ob_clean();
    }

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'error' => 'Erreur serveur WebAuthn.',
        'details' => $e->getMessage(),
    ]);
    exit;
} finally {
    if ($isWebAuthnAction) {
        restore_error_handler();
    }
}
?>
