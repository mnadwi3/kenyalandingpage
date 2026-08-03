<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class User extends Model
{
    public static function findByEmail(string $email): ?array
    {
        $sql = 'SELECT u.*, r.slug AS role_slug, r.name AS role_name
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.email = :email
                LIMIT 1';

        $stmt = self::db()->prepare($sql);
        $stmt->execute(['email' => strtolower($email)]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $sql = 'SELECT u.id, u.uuid, u.role_id, u.name, u.email, u.phone, u.avatar,
                       u.status, u.last_login_at, u.created_at, u.updated_at,
                       r.slug AS role_slug, r.name AS role_name
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.id = :id
                LIMIT 1';

        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function touchLogin(int $id): void
    {
        $stmt = self::db()->prepare('UPDATE users SET last_login_at = NOW(3) WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function updatePassword(int $id, string $plainPassword): void
    {
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $stmt = self::db()->prepare(
            'UPDATE users SET password_hash = :hash, password_changed_at = NOW(3), updated_at = NOW(3) WHERE id = :id'
        );
        $stmt->execute(['hash' => $hash, 'id' => $id]);
    }

    public static function hasPermission(int $userId, string $permissionSlug): bool
    {
        $sql = 'SELECT 1
                FROM users u
                INNER JOIN role_permissions rp ON rp.role_id = u.role_id
                INNER JOIN permissions p ON p.id = rp.permission_id
                WHERE u.id = :user_id AND p.slug = :slug
                LIMIT 1';

        $stmt = self::db()->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'slug' => $permissionSlug]);

        return (bool) $stmt->fetchColumn();
    }
}
