<?php
/** @var list<array{code:string,label:string,logo:string}> $accreditationTypes */
/** @var array<string,string> $quickFactTypes */
/** @var array<string,string> $quickFactIcons */
/** @var string $csrf */
?>
<section class="page-intro page-intro-row">
    <div>
        <h1>Icons</h1>
        <p>Manage icon images used across the site: accreditation badges, specialty icons, and hospital quick-fact icons.</p>
    </div>
</section>

<section class="page-intro">
    <h2>Icons for Accreditations</h2>
    <p>Badges hospitals can display (e.g. JCI, NABH, NABL). Add, edit, or remove as many as you need.</p>
</section>

<table class="data-table">
    <thead>
        <tr><th>Icon</th><th>Label &amp; new icon</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($accreditationTypes as $type): ?>
            <tr>
                <td>
                    <?php if (!empty($type['logo'])): ?>
                        <div class="photo-preview"><img src="<?= e(asset((string) $type['logo'])) ?>" alt="<?= e($type['label']) ?>" style="width:40px;height:40px;object-fit:contain;"></div>
                    <?php else: ?>
                        <span>No icon</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" action="<?= e(url('/settings/icons/accreditations/' . $type['code'])) ?>" enctype="multipart/form-data" class="form-inline">
                        <?= $csrf ?>
                        <input type="text" name="label" value="<?= e($type['label']) ?>" required>
                        <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif">
                        <button type="submit" class="btn btn-ghost btn-sm">Save</button>
                    </form>
                </td>
                <td class="row-actions">
                    <form method="post" action="<?= e(url('/settings/icons/accreditations/' . $type['code'] . '/delete')) ?>" onsubmit="return confirm('Remove this accreditation type?');">
                        <?= $csrf ?>
                        <button type="submit" class="btn btn-ghost btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($accreditationTypes === []): ?>
            <tr><td colspan="3">No accreditation types yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<form method="post" action="<?= e(url('/settings/icons/accreditations')) ?>" enctype="multipart/form-data" class="form-grid">
    <?= $csrf ?>
    <label class="field"><span>Label</span><input type="text" name="label" placeholder="e.g. NABH" required></label>
    <label class="field">
        <span>Icon image</span>
        <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif" required>
    </label>
    <div class="form-footer full"><button type="submit" class="btn btn-primary">Add accreditation type</button></div>
</form>

<section class="page-intro" style="margin-top:2rem;">
    <h2>Icons for Specialities</h2>
    <p>Specialty icons are managed on each specialty's own edit page — the icon shown next to a specialty's name there is what appears on hospital pages.</p>
</section>
<div class="page-actions" style="margin-bottom:2rem;">
    <a class="btn btn-ghost" href="<?= e(url('/specialties')) ?>">Go to Specialties</a>
</div>

<section class="page-intro">
    <h2>Icons for Quick Facts</h2>
    <p>These 6 icons appear on every hospital's Quick Facts strip. Upload one image per fact type.</p>
</section>

<table class="data-table">
    <thead>
        <tr><th>Icon</th><th>Quick Fact</th><th>Update icon</th></tr>
    </thead>
    <tbody>
        <?php foreach ($quickFactTypes as $code => $label): ?>
            <tr>
                <td>
                    <?php if (!empty($quickFactIcons[$code])): ?>
                        <div class="photo-preview"><img src="<?= e(asset((string) $quickFactIcons[$code])) ?>" alt="<?= e($label) ?>" style="width:40px;height:40px;object-fit:contain;"></div>
                    <?php else: ?>
                        <span>No icon</span>
                    <?php endif; ?>
                </td>
                <td><?= e($label) ?></td>
                <td>
                    <form method="post" action="<?= e(url('/settings/icons/quick-facts/' . $code)) ?>" enctype="multipart/form-data" class="form-inline">
                        <?= $csrf ?>
                        <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif" required>
                        <button type="submit" class="btn btn-ghost btn-sm">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
