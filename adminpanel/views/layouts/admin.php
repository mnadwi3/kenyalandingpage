<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> · VaidTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('/assets/css/admin.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/dashboard.css')) ?>">
    <?php if (($activeNav ?? '') === 'doctors'): ?>
        <link rel="stylesheet" href="<?= e(url('/assets/css/doctors.css')) ?>">
    <?php endif; ?>
    <?php if (($activeNav ?? '') === 'treatments'): ?>
        <link rel="stylesheet" href="<?= e(url('/assets/css/treatments.css')) ?>">
    <?php endif; ?>
</head>
<body class="admin-body" data-theme="light">
    <?php require BASE_PATH . '/views/partials/header.php'; ?>

    <div class="admin-shell">
        <?php require BASE_PATH . '/views/partials/sidebar.php'; ?>

        <main class="admin-main">
            <div id="toast-stack" class="toast-stack" aria-live="polite">
                <?php if (!empty($toast)): ?>
                    <div class="toast toast-<?= e($toast['type'] ?? 'info') ?>" role="status">
                        <?= e($toast['message'] ?? '') ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($breadcrumbs)): ?>
                <?php component('breadcrumb', ['items' => $breadcrumbs]); ?>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>

    <script src="<?= e(url('/assets/js/admin.js')) ?>"></script>
    <script src="<?= e(url('/assets/js/dashboard.js')) ?>"></script>
    <?php if (($activeNav ?? '') === 'doctors'): ?>
        <script src="<?= e(url('/assets/js/doctors.js')) ?>"></script>
    <?php endif; ?>
    <?php if (($activeNav ?? '') === 'treatments'): ?>
        <script src="<?= e(url('/assets/js/treatments.js')) ?>"></script>
    <?php endif; ?>
</body>
</html>
