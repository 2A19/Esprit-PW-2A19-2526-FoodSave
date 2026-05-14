<?php
if (!defined('BREVO_API_KEY')) {
    define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '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');
}
if (!defined('BREVO_SENDER_EMAIL')) {
    define('BREVO_SENDER_EMAIL', getenv('BREVO_SENDER_EMAIL') ?: 'fatenkaroui1@gmail.com');
}
if (!defined('BREVO_SENDER_NAME')) {
    define('BREVO_SENDER_NAME', getenv('BREVO_SENDER_NAME') ?: 'FoodSave');
}

class config {
    private static $pdo = null;

    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=foodsave_db',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
