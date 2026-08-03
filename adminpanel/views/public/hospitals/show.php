<?php
/** @var array<string,mixed> $hospital */
/** @var string $title */
$page = is_array($hospital['page'] ?? null) ? $hospital['page'] : [];
$card = is_array($hospital['homepage_card'] ?? null) ? $hospital['homepage_card'] : [];
$about = (string) ($page['about'] ?? $hospital['about'] ?? $hospital['description'] ?? '');
$featuredDoctors = is_array($page['featured_doctors'] ?? null) ? $page['featured_doctors'] : [];
$treatments = is_array($page['related_treatments'] ?? null) ? $page['related_treatments'] : [];
$services = is_array($page['international_patient_services'] ?? null) ? $page['international_patient_services'] : [];
$accreditations = is_array($hospital['accreditations'] ?? null) ? $hospital['accreditations'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <?php if (!empty($hospital['seo_description'])): ?>
        <meta name="description" content="<?= e((string) $hospital['seo_description']) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= e(url((string) $hospital['public_url'])) ?>">
    <style>
        body{font-family:Georgia,serif;margin:0;background:#f8fafc;color:#0f172a}
        main{max-width:820px;margin:2rem auto;padding:1.5rem;background:#fff;border:1px solid #e2e8f0;border-radius:12px}
        .meta{color:#64748b;font-family:system-ui,sans-serif;font-size:.9rem}
        .cover{width:100%;max-height:280px;object-fit:cover;border-radius:12px}
        .logo{width:72px;height:72px;object-fit:contain;border-radius:10px;border:1px solid #e2e8f0;background:#fff}
        .row{display:flex;gap:1rem;align-items:center;margin:.75rem 0 1rem}
        .badge{display:inline-block;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;border-radius:999px;padding:.15rem .55rem;font:600 .75rem system-ui}
        .logos{display:flex;gap:.5rem;flex-wrap:wrap;margin:.5rem 0 1rem}
        .logos img{width:36px;height:36px}
        ul{padding-left:1.2rem}
        .note{margin-top:1.5rem;padding:.85rem;background:#eff6ff;border-radius:8px;font-family:system-ui,sans-serif;font-size:.9rem}
        .card-preview{margin-top:1.25rem;padding:1rem;border:1px dashed #cbd5e1;border-radius:10px;font-family:system-ui,sans-serif;font-size:.9rem}
        h2{font-size:1.15rem;margin-top:1.4rem}
    </style>
</head>
<body>
<main>
    <?php if (!empty($hospital['cover_image'])): ?>
        <img class="cover" src="<?= e(asset((string) $hospital['cover_image'])) ?>" alt="">
    <?php endif; ?>
    <div class="row">
        <?php if (!empty($hospital['logo'])): ?>
            <img class="logo" src="<?= e(asset((string) $hospital['logo'])) ?>" alt="">
        <?php endif; ?>
        <div>
            <h1><?= e((string) $hospital['name']) ?></h1>
            <?php if (!empty($hospital['is_verified'])): ?><span class="badge">Verified</span><?php endif; ?>
            <p class="meta"><?= e((string) ($hospital['location'] ?: '—')) ?></p>
        </div>
    </div>
    <?php if ($accreditations !== []): ?>
        <div class="logos">
            <?php foreach ($accreditations as $item): ?>
                <img src="<?= e(url((string) $item['logo'])) ?>" alt="<?= e((string) $item['label']) ?>" title="<?= e((string) $item['label']) ?>">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <p class="meta">
        <?= e((string) ($hospital['hospital_type'] ?? '')) ?>
        <?php if (!empty($hospital['established_year'])): ?> · Est. <?= e((string) $hospital['established_year']) ?><?php endif; ?>
        <?php if (!empty($hospital['number_of_beds'])): ?> · <?= e((string) $hospital['number_of_beds']) ?> beds<?php endif; ?>
    </p>
    <?php if ($about !== ''): ?>
        <h2>About hospital</h2>
        <p><?= nl2br(e($about)) ?></p>
    <?php endif; ?>
    <?php if ($services !== []): ?>
        <h2>International patient services</h2>
        <ul><?php foreach ($services as $service): ?><li><?= e((string) $service['label']) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <?php if ($treatments !== []): ?>
        <h2>Related treatments</h2>
        <ul><?php foreach ($treatments as $treatment): ?><li><?= e((string) $treatment['name']) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <h2>Featured doctors</h2>
    <?php if ($featuredDoctors === []): ?>
        <p class="meta">No linked doctors yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($featuredDoctors as $doctor): ?>
                <li>
                    <?= e((string) $doctor['name']) ?>
                    <?php if (!empty($doctor['is_featured'])): ?> (Featured)<?php endif; ?>
                    <?php if (!empty($doctor['qualification'])): ?> — <?= e((string) $doctor['qualification']) ?><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <h2>Contact form</h2>
    <p class="meta">Form endpoint prepared: <?= e((string) ($page['contact_form']['action'] ?? '/enquiries')) ?></p>
    <div class="card-preview">
        <strong>Homepage card data ready:</strong>
        logo, cover, name, verification, accreditation logos, year, beds, type, location, short description, Book Appointment / WhatsApp / View More URLs.
        <?php if (!empty($card['short_description'])): ?>
            <div style="margin-top:.5rem"><?= e((string) $card['short_description']) ?></div>
        <?php endif; ?>
    </div>
    <div class="note">Routing and data prepared for frontend integration. Full public page ships later.</div>
</main>
</body>
</html>
