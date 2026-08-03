<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Password reset token persistence (hashed tokens only).
 */
final class PasswordResetToken extends Model
{
    public static function create(int $userId, string $plainToken, int $ttlMinutes = 60): void
    {
        // Invalidate previous unused tokens for this user.
        $invalidate = self::db()->prepare(
            'UPDATE password_reset_tokens SET used_at = NOW(3) WHERE user_id = :user_id AND used_at IS NULL'
        );
        $invalidate->execute(['user_id' => $userId]);

        $stmt = self::db()->prepare(
            'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at, created_at)
             VALUES (:user_id, :token_hash, DATE_ADD(NOW(3), INTERVAL :ttl MINUTE), NOW(3))'
        );

        $stmt->execute([
            'user_id'    => $userId,
            'token_hash' => hash('sha256', $plainToken),
            'ttl'        => $ttlMinutes,
        ]);
    }

    public static function findValid(string $plainToken): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT t.*, u.email, u.name, u.status AS user_status
             FROM password_reset_tokens t
             INNER JOIN users u ON u.id = t.user_id
             WHERE t.token_hash = :hash
               AND t.used_at IS NULL
               AND t.expires_at > NOW(3)
               AND u.status = \'active\'
             LIMIT 1'
        );
        $stmt->execute(['hash' => hash('sha256', $plainToken)]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function markUsed(int $id): void
    {
        $stmt = self::db()->prepare('UPDATE password_reset_tokens SET used_at = NOW(3) WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
