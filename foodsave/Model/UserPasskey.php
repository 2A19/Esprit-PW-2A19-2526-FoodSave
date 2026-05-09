<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Repository for WebAuthn passkeys (fingerprint / Windows Hello credentials).
 */
class UserPasskey {

    public static function create(
        int $userId,
        string $credentialId,
        string $publicKeyPem,
        int $signCount,
        ?string $transports = null,
        ?string $label = null
    ): bool {
        $sql = "INSERT INTO user_passkeys
                    (user_id, credential_id, public_key, sign_count, transports, label, created_at)
                VALUES
                    (:user_id, :credential_id, :public_key, :sign_count, :transports, :label, NOW())";
        $db = config::getConnexion();
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'user_id'       => $userId,
            'credential_id' => $credentialId,
            'public_key'    => $publicKeyPem,
            'sign_count'    => $signCount,
            'transports'    => $transports,
            'label'         => $label,
        ]);
    }

    public static function findByCredentialId(string $credentialId): ?array {
        $db = config::getConnexion();
        $stmt = $db->prepare(
            'SELECT * FROM user_passkeys WHERE credential_id = :cid LIMIT 1'
        );
        $stmt->bindValue(':cid', $credentialId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findByUserId(int $userId): array {
        $db = config::getConnexion();
        $stmt = $db->prepare(
            'SELECT * FROM user_passkeys WHERE user_id = :uid ORDER BY created_at DESC'
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findUserByEmail(string $email): ?array {
        $db = config::getConnexion();
        $stmt = $db->prepare('SELECT * FROM user WHERE email = :email LIMIT 1');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findUserById(int $userId): ?array {
        $db = config::getConnexion();
        $stmt = $db->prepare('SELECT * FROM user WHERE id = :id LIMIT 1');
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function updateSignCount(int $id, int $signCount): bool {
        $db = config::getConnexion();
        $stmt = $db->prepare(
            'UPDATE user_passkeys SET sign_count = :sc, last_used_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([
            'sc' => $signCount,
            'id' => $id,
        ]);
    }

    public static function deleteByIdForUser(int $id, int $userId): bool {
        $db = config::getConnexion();
        $stmt = $db->prepare(
            'DELETE FROM user_passkeys WHERE id = :id AND user_id = :uid'
        );
        return $stmt->execute([
            'id'  => $id,
            'uid' => $userId,
        ]);
    }
}
