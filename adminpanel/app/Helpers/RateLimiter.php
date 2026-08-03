<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Session;

/**
 * Simple session-backed rate limiter for auth endpoints.
 */
final class RateLimiter
{
    public static function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $bucket = self::bucket($key);
        $now = time();
        if (($bucket['reset_at'] ?? 0) <= $now) {
            return false;
        }

        return ($bucket['attempts'] ?? 0) >= $maxAttempts;
    }

    public static function hit(string $key, int $decaySeconds): int
    {
        $bucket = self::bucket($key);
        $now = time();
        if (($bucket['reset_at'] ?? 0) <= $now) {
            $bucket = ['attempts' => 0, 'reset_at' => $now + $decaySeconds];
        }
        $bucket['attempts'] = (int) ($bucket['attempts'] ?? 0) + 1;
        Session::set(self::sessionKey($key), $bucket);

        return (int) $bucket['attempts'];
    }

    public static function clear(string $key): void
    {
        Session::forget(self::sessionKey($key));
    }

    public static function availableIn(string $key): int
    {
        $bucket = self::bucket($key);
        $resetAt = (int) ($bucket['reset_at'] ?? 0);

        return max(0, $resetAt - time());
    }

    /** @return array{attempts?:int, reset_at?:int} */
    private static function bucket(string $key): array
    {
        $value = Session::get(self::sessionKey($key), []);

        return is_array($value) ? $value : [];
    }

    private static function sessionKey(string $key): string
    {
        return 'rate_limit:' . $key;
    }
}
