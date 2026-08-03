<?php
/** @var string $csrf */
?>
<div class="auth-form-head">
    <h2>Sign in</h2>
    <p>Enter your admin credentials to continue.</p>
</div>

<form method="post" action="<?= e(url('/login')) ?>" class="stack-form" novalidate>
    <?= $csrf ?>
    <label class="field">
        <span>Email</span>
        <input type="email" name="email" value="<?= old('email') ?>" autocomplete="username" required autofocus>
    </label>
    <label class="field">
        <span>Password</span>
        <input type="password" name="password" autocomplete="current-password" required>
    </label>
    <button type="submit" class="btn btn-primary btn-block">Sign in</button>
</form>

<p class="auth-links">
    <a href="<?= e(url('/forgot-password')) ?>">Forgot password?</a>
</p>
