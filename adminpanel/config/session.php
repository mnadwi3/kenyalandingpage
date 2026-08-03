<?php

declare(strict_types=1);

return [
    'name'     => env('SESSION_NAME', 'vaidtrack_admin_session'),
    'lifetime' => (int) env('SESSION_LIFETIME', 7200),
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
    'path'     => '/',
];
