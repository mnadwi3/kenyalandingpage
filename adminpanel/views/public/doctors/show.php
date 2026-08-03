<?php
/** @var array<string,mixed> $doctor */
/** @var string $title */
$expertise = (string) ($doctor['expertise'] ?? $doctor['experience_summary'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <?php if (!empty($doctor['seo_description'])): ?>
        <meta name="description" content="<?= e((string) $doctor['seo_description']) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= e(url((string) $doctor['public_url'])) ?>">
    <style>
        body{font-family:Georgia,serif;margin:0;background:#f8fafc;color:#0f172a}
        main{max-width:720px;margin:2rem auto;padding:1.5rem;background:#fff;border:1px solid #e2e8f0;border-radius:12px}
        .meta{color:#64748b;font-family:system-ui,sans-serif;font-size:.9rem}
        img{width:120px;height:120px;object-fit:cover;border-radius:999px}
        .note{margin-top:1.5rem;padding:.85rem;background:#eff6ff;border-radius:8px;font-family:system-ui,sans-serif;font-size:.9rem}
    </style>
</head>
<body>
<main>
    <?php if (!empty($doctor['photo'])): ?>
        <img src="<?= e(asset((string) $doctor['photo'])) ?>" alt="<?= e((string) $doctor['name']) ?>">
    <?php endif; ?>
    <h1><?= e((string) $doctor['name']) ?></h1>
    <p class="meta"><?= e((string) ($doctor['qualification'] ?? '')) ?></p>
    <?php if ($doctor['years_of_experience'] !== null && $doctor['years_of_experience'] !== ''): ?>
        <p class="meta"><?= e((string) $doctor['years_of_experience']) ?> years of experience</p>
    <?php endif; ?>
    <p class="meta">Public URL: <?= e((string) $doctor['public_url']) ?></p>
    <?php if ($expertise !== ''): ?>
        <h2>Expertise</h2>
        <p><?= nl2br(e($expertise)) ?></p>
    <?php endif; ?>
    <?php if (!empty($doctor['education'])): ?>
        <h2>Education</h2>
        <p><?= nl2br(e((string) $doctor['education'])) ?></p>
    <?php endif; ?>
    <div class="note">Data endpoint prepared for frontend integration (Phase 9).</div>
</main>
</body>
</html>
