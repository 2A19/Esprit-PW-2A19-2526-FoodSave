<?php
session_start();

require_once __DIR__ . '/Controller/UserController.php';
require_once __DIR__ . '/Controller/WebAuthnController.php';

$action = $_GET['action'] ?? 'login';
$controller = new UserController();
$webauthn = new WebAuthnController();
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

        default:
            header('Location: index.php?action=login');
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
