<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'VaidTrack') ?> · VaidTrack Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('/assets/css/admin.css')) ?>">
</head>
<body class="auth-body" data-theme="light">
    <div class="auth-shell">
        <aside class="auth-brand">
            <div class="auth-brand-inner">
                <p class="brand-mark">VaidTrack</p>
                <h1>Medical tourism, managed with clarity.</h1>
                <p class="brand-copy">Secure admin access for doctors, treatments, hospitals, and patient enquiries.</p>
            </div>
        </aside>
        <main class="auth-panel">
            <div class="auth-card">
                <?php if (!empty($toast)): ?>
                    <div class="toast toast-<?= e($toast['type'] ?? 'info') ?>" role="status">
                        <?= e($toast['message'] ?? '') ?>
                    </div>
                <?php endif; ?>
                <?= $content ?>
            </div>
            <p class="auth-footer">© <?= date('Y') ?> VaidTrack</p>
        </main>
    </div>
    <script src="<?= e(url('/assets/js/admin.js')) ?>"></script>
</body>
</html>
