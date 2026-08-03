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
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            if (is_string($referer) && $referer !== '') {
                header('Location: ' . $referer);
                exit;
            }
            redirect('/dashboard');
        }
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
