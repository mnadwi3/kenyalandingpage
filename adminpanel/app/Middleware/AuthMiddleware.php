<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

/** Require an authenticated active admin session. */
final class AuthMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            flash('error', 'Please sign in to continue.');
            redirect('/login');
        }

        $user = Auth::user();
        if ($user === null || ($user['status'] ?? '') !== 'active') {
            Auth::logout();
            flash('error', 'Your account is not active. Please sign in again.');
            redirect('/login');
        }
    }
}
