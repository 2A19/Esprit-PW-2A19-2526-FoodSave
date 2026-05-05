<?php
require_once __DIR__ . '/../config/config.php';

class PasswordResetCode {
    public static function ensureTable(): void {
        $db = config::getConnexion();
        $sql = "CREATE TABLE IF NOT EXISTS password_reset_codes (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    user_id INT NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    code_hash CHAR(64) NOT NULL,
                    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
                    expires_at DATETIME NOT NULL,
                    verified_at DATETIME NULL,
                    used_at DATETIME NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_email_created (email, created_at),
                    INDEX idx_user_created (user_id, created_at),
                    CONSTRAINT fk_password_reset_user
                        FOREIGN KEY (user_id) REFERENCES user(id)
                        ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $db->exec($sql);
    }

    public static function createCode(int $userId, string $email, string $code, int $ttlMinutes = 10): void {
        self::ensureTable();
        $db = config::getConnexion();

        $cleanup = $db->prepare(
            "UPDATE password_reset_codes
             SET used_at = NOW()
             WHERE email = :email AND used_at IS NULL"
        );
        $cleanup->execute(['email' => $email]);

        $stmt = $db->prepare(
            "INSERT INTO password_reset_codes (user_id, email, code_hash, expires_at)
             VALUES (:user_id, :email, :code_hash, DATE_ADD(NOW(), INTERVAL :ttl MINUTE))"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':code_hash', hash('sha256', $code));
        $stmt->bindValue(':ttl', $ttlMinutes, PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function verifyCode(string $email, string $code): bool {
        self::ensureTable();
        $db = config::getConnexion();
        $stmt = $db->prepare(
            "SELECT id, code_hash, attempts
             FROM password_reset_codes
             WHERE email = :email
               AND used_at IS NULL
               AND expires_at >= NOW()
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        if ((int) $row['attempts'] >= 5) {
            return false;
        }

        $ok = hash_equals((string) $row['code_hash'], hash('sha256', $code));
        if (!$ok) {
            $inc = $db->prepare("UPDATE password_reset_codes SET attempts = attempts + 1 WHERE id = :id");
            $inc->execute(['id' => $row['id']]);
            return false;
        }

        $mark = $db->prepare("UPDATE password_reset_codes SET verified_at = NOW() WHERE id = :id");
        $mark->execute(['id' => $row['id']]);
        return true;
    }

    public static function hasVerifiedCode(string $email): bool {
        self::ensureTable();
        $db = config::getConnexion();
        $stmt = $db->prepare(
            "SELECT id
             FROM password_reset_codes
             WHERE email = :email
               AND used_at IS NULL
               AND verified_at IS NOT NULL
               AND expires_at >= NOW()
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function consumeCodes(string $email): void {
        self::ensureTable();
        $db = config::getConnexion();
        $stmt = $db->prepare(
            "UPDATE password_reset_codes
             SET used_at = NOW()
             WHERE email = :email AND used_at IS NULL"
        );
        $stmt->execute(['email' => $email]);
    }
}
