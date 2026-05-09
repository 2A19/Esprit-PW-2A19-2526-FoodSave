<?php
include_once __DIR__ . '/../config/config.php';

class UserBan {
    public static function ensureTable(): void {
        $db = config::getConnexion();

        $sql = "CREATE TABLE IF NOT EXISTS `user_bans` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `reason` TEXT NOT NULL,
            `is_permanent` TINYINT(1) NOT NULL DEFAULT 0,
            `expires_at` DATETIME NULL,
            `created_by` INT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `lifted_at` DATETIME NULL,
            INDEX `idx_user_bans_user` (`user_id`),
            INDEX `idx_user_bans_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $db->exec($sql);
    }

    public static function createBan(int $userId, string $reason, bool $isPermanent, ?int $durationDays, ?int $createdBy): void {
        self::ensureTable();
        $db = config::getConnexion();

        $expiresAt = null;
        if (!$isPermanent) {
            $days = max(1, (int)($durationDays ?? 1));
            $expiresAt = (new DateTime())->modify('+' . $days . ' days')->format('Y-m-d H:i:s');
        }

        $stmt = $db->prepare(
            'INSERT INTO user_bans (user_id, reason, is_permanent, expires_at, created_by)
             VALUES (:user_id, :reason, :is_permanent, :expires_at, :created_by)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'reason' => $reason,
            'is_permanent' => $isPermanent ? 1 : 0,
            'expires_at' => $expiresAt,
            'created_by' => $createdBy,
        ]);
    }

    public static function expireOldBans(int $userId): void {
        self::ensureTable();
        $db = config::getConnexion();
        $stmt = $db->prepare(
            'UPDATE user_bans
             SET lifted_at = NOW()
             WHERE user_id = :user_id
               AND lifted_at IS NULL
               AND is_permanent = 0
               AND expires_at IS NOT NULL
               AND expires_at <= NOW()'
        );
        $stmt->execute(['user_id' => $userId]);
    }

    public static function getActiveBan(int $userId): ?array {
        self::ensureTable();
        self::expireOldBans($userId);

        $db = config::getConnexion();
        $stmt = $db->prepare(
            'SELECT id, user_id, reason, is_permanent, expires_at, created_at
             FROM user_bans
             WHERE user_id = :user_id
               AND lifted_at IS NULL
               AND (is_permanent = 1 OR (expires_at IS NOT NULL AND expires_at > NOW()))
             ORDER BY created_at DESC
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
?>
