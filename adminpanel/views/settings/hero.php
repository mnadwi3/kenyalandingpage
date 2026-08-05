<?php
/** @var array<string,mixed> $hero */
/** @var string $csrf */
$trustPoints = implode("\n", (array) ($hero['trust_points'] ?? []));
?>
<section class="page-intro page-intro-row">
    <div>
        <h1>Hero Content</h1>
        <p>Edit the homepage hero banner text, trust points, and image.</p>
    </div>
</section>

<form method="post" action="<?= e(url('/settings/hero')) ?>" enctype="multipart/form-data" class="form-grid">
    <?= $csrf ?>
    <label class="field full"><span>Eyebrow</span><input type="text" name="eyebrow" value="<?= e((string) ($hero['eyebrow'] ?? '')) ?>"></label>
    <label class="field full"><span>Headline</span><input type="text" name="headline" value="<?= e((string) ($hero['headline'] ?? '')) ?>"></label>
    <label class="field full"><span>Subheadline</span><textarea name="subheadline" rows="4"><?= e((string) ($hero['subheadline'] ?? '')) ?></textarea></label>
    <label class="field full"><span>Trust points (one per line)</span><textarea name="trust_points" rows="4"><?= e($trustPoints) ?></textarea></label>
    <div class="field">
        <span>Hero image</span>
        <?php if (!empty($hero['image'])): ?>
            <div class="photo-preview"><img src="<?= e(asset((string) $hero['image'])) ?>" alt=""></div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
    </div>
    <div class="form-footer full"><button type="submit" class="btn btn-primary">Save changes</button></div>
</form>
