<?php
/** @var array<string,mixed> $treatment */
/** @var string $title */
$introduction = (string) ($treatment['introduction'] ?? $treatment['overview'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <?php if (!empty($treatment['seo_description'])): ?>
        <meta name="description" content="<?= e((string) $treatment['seo_description']) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= e(url((string) $treatment['public_url'])) ?>">
    <style>
        body{font-family:Georgia,serif;margin:0;background:#f8fafc;color:#0f172a}
        main{max-width:720px;margin:2rem auto;padding:1.5rem;background:#fff;border:1px solid #e2e8f0;border-radius:12px}
        .meta{color:#64748b;font-family:system-ui,sans-serif;font-size:.9rem}
        img{width:100%;max-height:280px;object-fit:cover;border-radius:12px}
        .note{margin-top:1.5rem;padding:.85rem;background:#eff6ff;border-radius:8px;font-family:system-ui,sans-serif;font-size:.9rem}
        h2{font-size:1.15rem;margin-top:1.4rem}
    </style>
</head>
<body>
<main>
    <?php if (!empty($treatment['featured_image'])): ?>
        <img src="<?= e(asset((string) $treatment['featured_image'])) ?>" alt="<?= e((string) $treatment['name']) ?>">
    <?php endif; ?>
    <h1><?= e((string) $treatment['name']) ?></h1>
    <?php if (!empty($treatment['category'])): ?>
        <p class="meta"><?= e((string) $treatment['category']) ?></p>
    <?php endif; ?>
    <?php if (!empty($treatment['specialty_name'])): ?>
        <p class="meta">Specialty: <?= e((string) $treatment['specialty_name']) ?></p>
    <?php endif; ?>
    <p class="meta">Public URL: <?= e((string) $treatment['public_url']) ?></p>
    <?php if ($introduction !== ''): ?>
        <h2>Introduction</h2>
        <p><?= nl2br(e($introduction)) ?></p>
    <?php endif; ?>
    <?php if (!empty($treatment['symptoms'])): ?>
        <h2>Symptoms</h2>
        <p><?= nl2br(e((string) $treatment['symptoms'])) ?></p>
    <?php endif; ?>
    <?php if (!empty($treatment['when_needed'])): ?>
        <h2>When is this treatment needed?</h2>
        <p><?= nl2br(e((string) $treatment['when_needed'])) ?></p>
    <?php endif; ?>
    <?php if (!empty($treatment['procedure_overview'])): ?>
        <h2>Procedure overview</h2>
        <p><?= nl2br(e((string) $treatment['procedure_overview'])) ?></p>
    <?php endif; ?>
    <?php if (!empty($treatment['recovery'])): ?>
        <h2>Recovery &amp; hospital stay</h2>
        <p><?= nl2br(e((string) $treatment['recovery'])) ?></p>
    <?php endif; ?>
    <?php if (!empty($treatment['why_choose'])): ?>
        <h2>Why choose VaidTrack?</h2>
        <p><?= nl2br(e((string) $treatment['why_choose'])) ?></p>
    <?php endif; ?>
    <div class="note">Routing and data prepared for frontend integration. Full public page ships later.</div>
</main>
</body>
</html>
