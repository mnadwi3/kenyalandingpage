<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

/** Require an authenticated admin session. */
final class AuthMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            flash('error', 'Please sign in to continue.');
            redirect('/login');
        }
    }
}
