<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Authentication state and permission checks.
 */
final class Auth
{
    private const USER_KEY = 'auth_user_id';

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);

        if ($user === null || ($user['status'] ?? '') !== 'active') {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Rehash if algorithm/cost changed.
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            User::updatePassword((int) $user['id'], $password);
        }

        Session::regenerate();
        Session::set(self::USER_KEY, (int) $user['id']);
        User::touchLogin((int) $user['id']);

        return true;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function id(): ?int
    {
        $id = Session::get(self::USER_KEY);

        return is_int($id) ? $id : (is_numeric($id) ? (int) $id : null);
    }

    public static function user(): ?array
    {
        $id = self::id();

        return $id ? User::findById($id) : null;
    }

    public static function logout(): void
    {
        Session::forget(self::USER_KEY);
        Session::regenerate();
    }

    /** Check permission slug, e.g. doctors.create. Super admin bypasses. */
    public static function can(string $permissionSlug): bool
    {
        $user = self::user();

        if ($user === null) {
            return false;
        }

        if (($user['role_slug'] ?? '') === 'super_admin') {
            return true;
        }

        return User::hasPermission((int) $user['id'], $permissionSlug);
    }

    public static function requirePermission(string $permissionSlug): void
    {
        if (!self::can($permissionSlug)) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Forbidden']);
            exit;
        }
    }
}
