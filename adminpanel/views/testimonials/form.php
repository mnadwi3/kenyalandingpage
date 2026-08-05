<?php
/** @var array<string,mixed>|null $testimonial */
/** @var array<string,mixed> $values */
/** @var string $csrf */
$isEdit = $testimonial !== null;
$action = $isEdit ? url('/testimonials/' . (int) $testimonial['id']) : url('/testimonials');
$val = static fn (string $key, mixed $default = ''): string => e((string) ($values[$key] ?? $default));
?>
<section class="page-intro page-intro-row">
    <div>
        <h1><?= $isEdit ? 'Edit Testimonial' : 'Add Testimonial' ?></h1>
        <p>Shown as a video card in the homepage testimonials section.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost" href="<?= e(url('/testimonials')) ?>">Back to list</a>
    </div>
</section>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="form-grid">
    <?= $csrf ?>
    <label class="field"><span>Patient name *</span><input type="text" name="patient_name" value="<?= $val('patient_name') ?>" required></label>
    <label class="field"><span>Treatment name</span><input type="text" name="treatment_name" value="<?= $val('treatment_name') ?>"></label>
    <label class="field"><span>YouTube video ID</span><input type="text" name="youtube_id" value="<?= $val('youtube_id') ?>" placeholder="e.g. dQw4w9WgXcQ"></label>
    <label class="field full"><span>Quote / summary</span><textarea name="quote" rows="4"><?= $val('quote') ?></textarea></label>
    <label class="field"><span>Status</span>
        <select name="status">
            <?php foreach (\App\Models\Testimonial::STATUSES as $status): ?>
                <option value="<?= e($status) ?>" <?= ($values['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="field"><span>Sort order</span><input type="number" name="sort_order" value="<?= $val('sort_order', '0') ?>"></label>
    <div class="field">
        <span>Thumbnail</span>
        <?php if (!empty($values['thumbnail'])): ?>
            <div class="photo-preview"><img src="<?= e(asset((string) $values['thumbnail'])) ?>" alt=""></div>
        <?php endif; ?>
        <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp,image/gif">
    </div>
    <div class="form-footer full"><button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create testimonial' ?></button></div>
</form>
