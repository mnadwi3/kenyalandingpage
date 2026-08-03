<?php
/** @var string $csrf */
$debugUrl = \App\Core\Session::getFlash('debug_reset_url');
?>
<div class="auth-form-head">
    <h2>Forgot password</h2>
    <p>We’ll send a reset link if the account exists.</p>
</div>

<?php if (!empty($debugUrl)): ?>
    <div class="toast toast-info" role="status">
        Debug reset link: <a href="<?= e($debugUrl) ?>"><?= e($debugUrl) ?></a>
    </div>
<?php endif; ?>

<form method="post" action="<?= e(url('/forgot-password')) ?>" class="stack-form" novalidate>
    <?= $csrf ?>
    <label class="field">
        <span>Email</span>
        <input type="email" name="email" value="<?= old('email') ?>" autocomplete="email" required autofocus>
    </label>
    <button type="submit" class="btn btn-primary btn-block">Send reset link</button>
</form>

<p class="auth-links">
    <a href="<?= e(url('/login')) ?>">Back to sign in</a>
</p>
