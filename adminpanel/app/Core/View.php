<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple PHP view renderer.
 */
final class View
{
    public static function render(string $template, array $data = []): void
    {
        $path = BASE_PATH . '/views/' . str_replace('.', '/', $template) . '.php';

        if (!is_file($path)) {
            throw new \RuntimeException("View [{$template}] not found.");
        }

        extract($data, EXTR_SKIP);

        $toast = Session::getFlash('toast');

        require $path;
    }

    /** Render a view into a layout. */
    public static function renderInLayout(string $template, string $layout, array $data = []): void
    {
        $contentPath = BASE_PATH . '/views/' . str_replace('.', '/', $template) . '.php';
        $layoutPath  = BASE_PATH . '/views/layouts/' . $layout . '.php';

        if (!is_file($contentPath) || !is_file($layoutPath)) {
            throw new \RuntimeException("View or layout not found for [{$template}].");
        }

        extract($data, EXTR_SKIP);

        $toast = Session::getFlash('toast');

        ob_start();
        require $contentPath;
        $content = ob_get_clean();

        require $layoutPath;
    }
}
