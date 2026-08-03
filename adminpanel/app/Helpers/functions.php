<?php

declare(strict_types=1);

/**
 * Read an environment variable with optional default.
 */
function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

/**
 * Escape output for HTML context (XSS protection).
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Build a URL relative to the public app base.
 */
function url(string $path = ''): string
{
    $base = rtrim((string) config('app.url'), '/');
    $path = '/' . ltrim($path, '/');

    return $base . ($path === '/' ? '' : $path);
}

/**
 * Redirect and stop execution.
 */
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Retrieve nested config: config('app.name').
 */
function config(string $key, mixed $default = null): mixed
{
    static $cache = [];

    $parts = explode('.', $key, 2);
    $file  = $parts[0];
    $item  = $parts[1] ?? null;

    if (!isset($cache[$file])) {
        $path = BASE_PATH . '/config/' . $file . '.php';
        $cache[$file] = is_file($path) ? require $path : [];
    }

    if ($item === null) {
        return $cache[$file] ?? $default;
    }

    return $cache[$file][$item] ?? $default;
}

/**
 * Generate a UUID v4 string.
 */
function uuid(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Flash a session message (toast).
 */
function flash(string $type, string $message): void
{
    \App\Core\Session::flash('toast', ['type' => $type, 'message' => $message]);
}

/**
 * Old input helper for form redisplay.
 */
function old(string $key, string $default = ''): string
{
    $old = \App\Core\Session::getFlash('old') ?? [];

    return e((string) ($old[$key] ?? $default));
}

/**
 * Render a reusable view component from views/components.
 *
 * @param array<string, mixed> $data
 */
function component(string $name, array $data = []): void
{
    $path = BASE_PATH . '/views/components/' . $name . '.php';

    if (!is_file($path)) {
        throw new RuntimeException("Component [{$name}] not found.");
    }

    extract($data, EXTR_SKIP);
    require $path;
}

/**
 * Build a public asset/upload URL.
 */
function asset(string $path): string
{
    return url('/' . ltrim($path, '/'));
}

/**
 * Format a datetime string for admin UI.
 */
function format_datetime(?string $value, string $format = 'M j, Y H:i'): string
{
    if ($value === null || $value === '') {
        return '—';
    }

    $ts = strtotime($value);

    return $ts ? date($format, $ts) : '—';
}
