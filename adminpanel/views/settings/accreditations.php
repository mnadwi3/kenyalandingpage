<?php
/** @var list<array{code:string,label:string,logo:string}> $accreditationTypes */
/** @var string $csrf */
?>
<section class="page-intro page-intro-row">
    <div>
        <h1>Accreditation Icons</h1>
        <p>Manage the accreditation badges (e.g. JCI, NABH, NABL) hospitals can display, including their icon images.</p>
    </div>
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
                    <form method="post" action="<?= e(url('/settings/accreditations/' . $type['code'])) ?>" enctype="multipart/form-data" class="form-inline">
                        <?= $csrf ?>
                        <input type="text" name="label" value="<?= e($type['label']) ?>" required>
                        <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif">
                        <button type="submit" class="btn btn-ghost btn-sm">Save</button>
                    </form>
                </td>
                <td class="row-actions">
                    <form method="post" action="<?= e(url('/settings/accreditations/' . $type['code'] . '/delete')) ?>" onsubmit="return confirm('Remove this accreditation type?');">
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

<section class="page-intro">
    <h2>Add new accreditation type</h2>
</section>
<form method="post" action="<?= e(url('/settings/accreditations')) ?>" enctype="multipart/form-data" class="form-grid">
    <?= $csrf ?>
    <label class="field"><span>Label</span><input type="text" name="label" placeholder="e.g. NABH" required></label>
    <label class="field">
        <span>Icon image</span>
        <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif" required>
    </label>
    <div class="form-footer full"><button type="submit" class="btn btn-primary">Add accreditation type</button></div>
</form>
