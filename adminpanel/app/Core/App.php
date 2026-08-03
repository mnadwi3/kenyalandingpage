<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Application bootstrap: env, session, routes, dispatch.
 */
final class App
{
    private Router $router;

    public function __construct()
    {
        $this->loadEnv(BASE_PATH . '/.env');
        date_default_timezone_set((string) config('app.timezone'));

        Session::start(config('session'));

        $this->router = new Router();
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = $_SERVER['REQUEST_URI'] ?? '/';

        $this->router->dispatch($method, $uri);
    }

    private function loadEnv(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\"'");

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
