<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight method + path router.
 */
final class Router
{
    /** @var array<int, array{methods: string[], path: string, action: callable|array, middleware: string[]}> */
    private array $routes = [];

    public function get(string $path, callable|array $action, array $middleware = []): void
    {
        $this->add(['GET'], $path, $action, $middleware);
    }

    public function post(string $path, callable|array $action, array $middleware = []): void
    {
        $this->add(['POST'], $path, $action, $middleware);
    }

    private function add(array $methods, string $path, callable|array $action, array $middleware): void
    {
        $this->routes[] = [
            'methods'    => $methods,
            'path'       => $this->normalize($path),
            'action'     => $action,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalize(parse_url($uri, PHP_URL_PATH) ?? '/');

        // Strip public base path if app is in a subdirectory.
        $basePath = parse_url((string) config('app.url'), PHP_URL_PATH) ?: '';
        $basePath = rtrim($basePath, '/');

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
            $path = $this->normalize($path);
        }

        foreach ($this->routes as $route) {
            $params = $this->match($route['path'], $path);

            if ($params === null || !in_array(strtoupper($method), $route['methods'], true)) {
                continue;
            }

            foreach ($route['middleware'] as $middlewareClass) {
                (new $middlewareClass())->handle();
            }

            $this->invoke($route['action'], $params);
            return;
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Not Found']);
    }

    /** @return array<string, string>|null */
    private function match(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    private function invoke(callable|array $action, array $params): void
    {
        if (is_array($action)) {
            [$class, $method] = $action;
            $controller = new $class();
            $controller->{$method}(...array_values($params));
            return;
        }

        $action(...array_values($params));
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
