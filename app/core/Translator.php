<?php
// app/core/Translator.php

class Translator {
    private static $instance = null;
    private $translations = [];
    private $currentLang = 'fr';
    
    private function __construct() {
        // Démarrer la session pour stocker la langue
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Récupérer la langue depuis la session ou l'URL
        if (isset($_GET['lang'])) {
            $this->currentLang = $_GET['lang'];
            $_SESSION['lang'] = $this->currentLang;
        } elseif (isset($_SESSION['lang'])) {
            $this->currentLang = $_SESSION['lang'];
        } else {
            $this->currentLang = 'fr';
        }
        
        // Charger les traductions
        $this->loadTranslations();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadTranslations() {
        $langFile = __DIR__ . '/../../config/lang/' . $this->currentLang . '.php';
        if (file_exists($langFile)) {
            $this->translations = require $langFile;
        } else {
            // Fallback to French
            $fallbackFile = __DIR__ . '/../../config/lang/fr.php';
            if (file_exists($fallbackFile)) {
                $this->translations = require $fallbackFile;
            } else {
                $this->translations = [];
            }
        }
    }
    
    public function getCurrentLang() {
        return $this->currentLang;
    }
    
    public function translate($key, $default = null) {
        return $this->translations[$key] ?? $default ?? $key;
    }
    
    // Helper function static
    public static function t($key, $default = null) {
        return self::getInstance()->translate($key, $default);
    }
}
?>