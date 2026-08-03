<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

/** Block authenticated users from guest-only pages (login, etc.). */
final class GuestMiddleware
{
    public function handle(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
    }
}
