<?php
/**
 * FoodSave — Configuration des clés API
 */

class ApiKeys {
    // Clé API Mistral AI
    const MISTRAL_API_KEY = 'u9VfKkQhKMljN66YFEgUnRiz2Sd77RUV';
    // Clé API pour QR (si nécessaire, ici qrserver est gratuit)
    const QR_API_KEY = '';

    // Clé API Brevo (Sendinblue) pour l'envoi d'emails
    const BREVO_API_KEY = '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';
    const BREVO_SENDER_EMAIL = 'farousachihaoui@gmail.com';
    const BREVO_SENDER_NAME  = 'FoodSave';

    // Clé API Facebook (App ID pour le sharer)
    const FACEBOOK_APP_ID = '3DOBx2AQSJgE7K0OGb5801PoB8F_6WqkDAvGH6QokCPjTmR9c';
    const FACEBOOK_APP_SECRET = 'YOUR_FACEBOOK_APP_SECRET';
    const FACEBOOK_ACCESS_TOKEN = 'YOUR_APP_ACCESS_TOKEN'; // Pour posting si nécessaire

    // Pour partage, on peut utiliser le sharer sans token
}
?>