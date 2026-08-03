<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Validator;
use App\Models\PasswordResetToken;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        View::renderInLayout('auth/login', 'auth', [
            'title' => 'Sign in',
            'csrf'  => Csrf::field(),
        ]);
    }

    public function login(): void
    {
        $this->validateCsrf();

        $email    = strtolower($this->string('email'));
        $password = (string) ($_POST['password'] ?? '');

        $validator = new Validator(['email' => $email, 'password' => $password]);
        $validator->required('email', 'Email')->email('email', 'Email')->required('password', 'Password');

        if ($validator->fails()) {
            Session::flash('old', ['email' => $email]);
            flash('error', $validator->firstError());
            redirect('/login');
        }

        if (!Auth::attempt($email, $password)) {
            Session::flash('old', ['email' => $email]);
            flash('error', 'Invalid email or password.');
            redirect('/login');
        }

        flash('success', 'Welcome back.');
        redirect('/dashboard');
    }

    public function logout(): void
    {
        $this->validateCsrf();
        Auth::logout();
        flash('success', 'You have been signed out.');
        redirect('/login');
    }

    public function showForgotPassword(): void
    {
        View::renderInLayout('auth/forgot-password', 'auth', [
            'title' => 'Forgot password',
            'csrf'  => Csrf::field(),
        ]);
    }

    public function sendResetLink(): void
    {
        $this->validateCsrf();

        $email = strtolower($this->string('email'));
        $validator = new Validator(['email' => $email]);
        $validator->required('email', 'Email')->email('email', 'Email');

        if ($validator->fails()) {
            Session::flash('old', ['email' => $email]);
            flash('error', $validator->firstError());
            redirect('/forgot-password');
        }

        $user = User::findByEmail($email);

        // Always show the same message to avoid account enumeration.
        $generic = 'If that email exists, a reset link has been sent.';

        if ($user !== null && ($user['status'] ?? '') === 'active') {
            $plainToken = bin2hex(random_bytes(32));
            PasswordResetToken::create((int) $user['id'], $plainToken);

            $resetUrl = url('/reset-password?token=' . urlencode($plainToken));
            $this->deliverResetLink($email, $resetUrl);
        }

        flash('success', $generic);
        redirect('/forgot-password');
    }

    public function showResetPassword(): void
    {
        $token = $this->string('token');
        $record = $token !== '' ? PasswordResetToken::findValid($token) : null;

        if ($record === null) {
            flash('error', 'This reset link is invalid or has expired.');
            redirect('/forgot-password');
        }

        View::renderInLayout('auth/reset-password', 'auth', [
            'title' => 'Reset password',
            'csrf'  => Csrf::field(),
            'token' => $token,
        ]);
    }

    public function resetPassword(): void
    {
        $this->validateCsrf();

        $token    = $this->string('token');
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirmation'] ?? '');

        $validator = new Validator([
            'password' => $password,
            'password_confirmation' => $confirm,
        ]);
        $validator
            ->required('password', 'Password')
            ->minLength('password', 8, 'Password')
            ->confirmed('password', 'password_confirmation', 'Password');

        $record = $token !== '' ? PasswordResetToken::findValid($token) : null;

        if ($record === null) {
            flash('error', 'This reset link is invalid or has expired.');
            redirect('/forgot-password');
        }

        if ($validator->fails()) {
            flash('error', $validator->firstError());
            redirect('/reset-password?token=' . urlencode($token));
        }

        User::updatePassword((int) $record['user_id'], $password);
        PasswordResetToken::markUsed((int) $record['id']);

        flash('success', 'Password updated. You can sign in now.');
        redirect('/login');
    }

    /**
     * Deliver reset link. Uses log file until SMTP settings (Phase 8) are available.
     * When APP_DEBUG is true, the link is also flashed once for local testing.
     */
    private function deliverResetLink(string $email, string $resetUrl): void
    {
        $line = sprintf("[%s] Password reset for %s: %s\n", date('c'), $email, $resetUrl);
        $logFile = BASE_PATH . '/storage/logs/password_resets.log';
        @file_put_contents($logFile, $line, FILE_APPEND);

        if (config('app.debug')) {
            Session::flash('debug_reset_url', $resetUrl);
        }
    }
}
