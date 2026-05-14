<?php
/**
 * FoodSave — SMS provider config
 */
declare(strict_types=1);

class SmsConfig {
    private const TWILIO_ACCOUNT_SID = '';
    private const TWILIO_AUTH_TOKEN = '';
    private const TWILIO_FROM_NUMBER = '';

    public static function get(string $name): ?string {
        $value = getenv($name);
        if ($value !== false && trim($value) !== '') {
            return trim($value);
        }

        if ($name === 'TWILIO_ACCOUNT_SID') {
            return self::TWILIO_ACCOUNT_SID ?: null;
        }
        if ($name === 'TWILIO_AUTH_TOKEN') {
            return self::TWILIO_AUTH_TOKEN ?: null;
        }
        if ($name === 'TWILIO_FROM_NUMBER') {
            return self::TWILIO_FROM_NUMBER ?: null;
        }

        return null;
    }
}

class SmsProvider {
    public static function send(string $to, string $body): bool {
        $accountSid = SmsConfig::get('TWILIO_ACCOUNT_SID');
        $authToken = SmsConfig::get('TWILIO_AUTH_TOKEN');
        $from = SmsConfig::get('TWILIO_FROM_NUMBER');

        if (!$accountSid || !$authToken || !$from) {
            throw new RuntimeException('SMS API non configuré. Complétez config/Sms.php ou définissez les variables d\'environnement TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN et TWILIO_FROM_NUMBER.');
        }

        $url = sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', urlencode($accountSid));
        $postFields = http_build_query([
            'To'   => $to,
            'From' => $from,
            'Body' => $body,
        ]);

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => sprintf('%s:%s', $accountSid, $authToken),
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $result = curl_exec($curl);
        $error = curl_error($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($error) {
            throw new RuntimeException('Erreur cURL SMS : ' . $error);
        }

        $response = json_decode($result ?: '{}', true);
        if ($status >= 200 && $status < 300 && !empty($response['sid'])) {
            return true;
        }

        $message = $response['message'] ?? $response['error_message'] ?? 'Échec de l\'envoi SMS.';
        throw new RuntimeException(sprintf('Erreur SMS API (%d) : %s', $status, $message));
    }
}
