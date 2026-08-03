<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base controller with shared request helpers.
 */
abstract class Controller
{
    protected function view(string $template, array $data = []): void
    {
        View::render($template, $data);
    }

    protected function validateCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!Csrf::validate(is_string($token) ? $token : null)) {
            http_response_code(419);
            flash('error', 'Session expired. Please try again.');
            redirect($this->safeRedirectPath($_SERVER['HTTP_REFERER'] ?? null));
        }
    }

    /** Only allow same-origin relative redirects; never trust raw Referer hosts. */
    protected function safeRedirectPath(mixed $referer, string $fallback = '/dashboard'): string
    {
        if (!is_string($referer) || $referer === '') {
            return $fallback;
        }

        $parts = parse_url($referer);
        if ($parts === false || !isset($parts['path'])) {
            return $fallback;
        }

        $appUrl = (string) config('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $refHost = $parts['host'] ?? null;
        if ($refHost !== null && $appHost !== null && strcasecmp((string) $refHost, (string) $appHost) !== 0) {
            return $fallback;
        }

        $path = (string) $parts['path'];
        if ($path === '' || !str_starts_with($path, '/')) {
            return $fallback;
        }

        // Strip app base path if the app is mounted in a subdirectory.
        $basePath = rtrim((string) (parse_url($appUrl, PHP_URL_PATH) ?: ''), '/');
        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        if (isset($parts['query']) && $parts['query'] !== '') {
            $path .= '?' . $parts['query'];
        }

        return $path;
    }

    /** @return array<string, mixed> */
    protected function input(): array
    {
        return array_merge($_GET, $_POST);
    }

    protected function string(string $key, string $default = ''): string
    {
        $value = $this->input()[$key] ?? $default;

        return is_string($value) ? trim($value) : $default;
    }

    /** @param array<string, mixed> $payload */
    protected function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
