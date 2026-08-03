<?php
/** @var string $csrf */
/** @var string $token */
?>
<div class="auth-form-head">
    <h2>Reset password</h2>
    <p>Choose a new password for your account.</p>
</div>

<form method="post" action="<?= e(url('/reset-password')) ?>" class="stack-form" novalidate>
    <?= $csrf ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <label class="field">
        <span>New password</span>
        <input type="password" name="password" autocomplete="new-password" minlength="8" required autofocus>
    </label>
    <label class="field">
        <span>Confirm password</span>
        <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
    </label>
    <button type="submit" class="btn btn-primary btn-block">Update password</button>
</form>

<p class="auth-links">
    <a href="<?= e(url('/login')) ?>">Back to sign in</a>
</p>
