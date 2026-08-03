<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Helpers\RateLimiter;
use App\Helpers\Validator;
use App\Models\PasswordResetToken;
use App\Models\User;

final class AuthController extends Controller
{
    private const LOGIN_MAX_ATTEMPTS = 8;
    private const LOGIN_DECAY_SECONDS = 900;
    private const RESET_MAX_ATTEMPTS = 5;
    private const RESET_DECAY_SECONDS = 900;

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
        $throttleKey = 'login:' . $this->clientIp() . ':' . $email;

        if (RateLimiter::tooManyAttempts($throttleKey, self::LOGIN_MAX_ATTEMPTS, self::LOGIN_DECAY_SECONDS)) {
            Session::flash('old', ['email' => $email]);
            flash('error', 'Too many login attempts. Try again in ' . RateLimiter::availableIn($throttleKey) . ' seconds.');
            redirect('/login');
        }

        $validator = new Validator(['email' => $email, 'password' => $password]);
        $validator->required('email', 'Email')->email('email', 'Email')->required('password', 'Password');

        if ($validator->fails()) {
            RateLimiter::hit($throttleKey, self::LOGIN_DECAY_SECONDS);
            Session::flash('old', ['email' => $email]);
            flash('error', $validator->firstError());
            redirect('/login');
        }

        if (!Auth::attempt($email, $password)) {
            RateLimiter::hit($throttleKey, self::LOGIN_DECAY_SECONDS);
            Session::flash('old', ['email' => $email]);
            flash('error', 'Invalid email or password.');
            redirect('/login');
        }

        RateLimiter::clear($throttleKey);
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
        $throttleKey = 'reset:' . $this->clientIp() . ':' . $email;

        if (RateLimiter::tooManyAttempts($throttleKey, self::RESET_MAX_ATTEMPTS, self::RESET_DECAY_SECONDS)) {
            flash('error', 'Too many reset requests. Try again later.');
            redirect('/forgot-password');
        }
        RateLimiter::hit($throttleKey, self::RESET_DECAY_SECONDS);

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
            $this->deliverResetLink($email, $resetUrl, (int) $user['id']);
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
     * Deliver reset link. Does not persist plaintext tokens to disk.
     * When APP_DEBUG is true, the link is flashed once for local testing.
     */
    private function deliverResetLink(string $email, string $resetUrl, int $userId): void
    {
        $line = sprintf("[%s] Password reset requested for user_id=%d email=%s\n", date('c'), $userId, $email);
        $logFile = BASE_PATH . '/storage/logs/password_resets.log';
        @file_put_contents($logFile, $line, FILE_APPEND);

        if (config('app.debug')) {
            Session::flash('debug_reset_url', $resetUrl);
        }
    }

    private function clientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        return is_string($ip) && $ip !== '' ? $ip : 'unknown';
    }
}
