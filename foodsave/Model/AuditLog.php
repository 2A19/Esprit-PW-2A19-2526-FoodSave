<?php
include_once __DIR__ . '/../config/config.php';

class AuditLog {
    public static function ensureTable(): void {
        $db = config::getConnexion();
        $sqlWithFk = "CREATE TABLE IF NOT EXISTS `audit_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `action` VARCHAR(120) NOT NULL,
            `details` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_audit_user_id` (`user_id`),
            INDEX `idx_audit_created_at` (`created_at`),
            CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            $db->exec($sqlWithFk);
            return;
        } catch (Throwable $e) {
            // Fallback if FK creation fails on this MySQL setup.
        }

        $sqlFallback = "CREATE TABLE IF NOT EXISTS `audit_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `action` VARCHAR(120) NOT NULL,
            `details` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_audit_user_id` (`user_id`),
            INDEX `idx_audit_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $db->exec($sqlFallback);
    }

    public static function record(int $userId, string $action, ?string $details = null): void {
        if ($userId <= 0 || trim($action) === '') {
            return;
        }

        self::ensureTable();
        $db = config::getConnexion();
        $stmt = $db->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (:user_id, :action, :details)');
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
        ]);
    }

    public static function listByUser(int $userId, int $limit = 200): array {
        self::ensureTable();
        $db = config::getConnexion();
        $stmt = $db->prepare(
            'SELECT id, user_id, action, details, created_at
             FROM audit_logs
             WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
