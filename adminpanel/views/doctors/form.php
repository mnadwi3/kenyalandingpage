<?php
/** @var array<string,mixed>|null $doctor */
/** @var array<string,mixed> $values */
/** @var array<string,mixed> $options */
/** @var string $csrf */
$isEdit = $doctor !== null;
$action = $isEdit ? url('/doctors/' . (int) $doctor['id']) : url('/doctors');
$publicUrl = !empty($values['slug']) ? url('/doctors/' . $values['slug']) : null;
$val = static fn (string $key, mixed $default = ''): string => e((string) ($values[$key] ?? $default));
$checkedIds = static function (string $key) use ($values): array {
    $raw = $values[$key] ?? [];

    return is_array($raw) ? $raw : [];
};
$expertiseValue = (string) ($values['expertise'] ?? $values['experience_summary'] ?? '');
?>
<section class="page-intro page-intro-row">
    <div>
        <h1><?= $isEdit ? 'Edit Doctor' : 'Add Doctor' ?></h1>
        <p><?= $isEdit ? 'Update profile, relationships, and SEO.' : 'Create a new doctor profile.' ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost" href="<?= e(url('/doctors')) ?>">Back to list</a>
        <?php if ($publicUrl): ?><a class="btn btn-ghost" href="<?= e($publicUrl) ?>" target="_blank" rel="noopener">Public URL</a><?php endif; ?>
    </div>
</section>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="doctor-form" data-doctor-form>
    <?= $csrf ?>
    <div class="tabs" data-tabs>
        <div class="tab-list" role="tablist">
            <button type="button" class="tab is-active" data-tab="general">General</button>
            <button type="button" class="tab" data-tab="professional">Professional</button>
            <button type="button" class="tab" data-tab="relationships">Relationships</button>
            <button type="button" class="tab" data-tab="seo">SEO</button>
            <button type="button" class="tab" data-tab="preview">Preview</button>
        </div>

        <div class="tab-panel is-active" data-tab-panel="general">
            <div class="form-grid">
                <label class="field"><span>Full name *</span><input type="text" name="name" value="<?= $val('name') ?>" required data-slug-source></label>
                <label class="field"><span>Slug</span><input type="text" name="slug" value="<?= $val('slug') ?>" data-slug-target placeholder="auto-generated"></label>
                <label class="field"><span>Status</span>
                    <select name="status">
                        <?php foreach ($options['statuses'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= ($values['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="field checkbox-field"><input type="checkbox" name="is_featured" value="1" <?= !empty($values['is_featured']) ? 'checked' : '' ?>><span>Featured doctor</span></label>
                <div class="field">
                    <span>Doctor photo</span>
                    <?php if (!empty($values['photo'])): ?>
                        <div class="photo-preview">
                            <img src="<?= e(asset((string) $values['photo'])) ?>" alt="">
                            <label class="checkbox-field"><input type="checkbox" name="remove_photo" value="1"><span>Remove current photo</span></label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
                </div>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="professional">
            <div class="form-grid">
                <label class="field"><span>Qualification</span><input type="text" name="qualification" value="<?= $val('qualification') ?>"></label>
                <label class="field"><span>Years of experience</span><input type="number" min="0" max="80" name="years_of_experience" value="<?= $val('years_of_experience') ?>"></label>
                <label class="field full"><span>Expertise</span><textarea name="expertise" rows="4" placeholder="e.g. Knee Replacement&#10;Hip Replacement&#10;Sports Injury&#10;Arthroscopy"><?= e($expertiseValue) ?></textarea></label>
                <label class="field full"><span>Education</span><textarea name="education" rows="4"><?= $val('education') ?></textarea></label>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="relationships">
            <div class="relation-grid">
                <?php component('multi-select', ['name' => 'language_ids[]', 'label' => 'Languages', 'options' => $options['languages'], 'selected' => $checkedIds('language_ids')]); ?>
                <?php component('multi-select', ['name' => 'specialty_ids[]', 'label' => 'Specialties', 'options' => $options['specialties'], 'selected' => $checkedIds('specialty_ids')]); ?>
                <?php component('multi-select', ['name' => 'treatment_ids[]', 'label' => 'Treatments', 'options' => $options['treatments'], 'selected' => $checkedIds('treatment_ids')]); ?>
                <?php component('multi-select', ['name' => 'hospital_ids[]', 'label' => 'Hospitals', 'options' => $options['hospitals'], 'selected' => $checkedIds('hospital_ids')]); ?>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="seo">
            <div class="form-grid">
                <label class="field full"><span>SEO title</span><input type="text" name="seo_title" value="<?= $val('seo_title') ?>" maxlength="255"></label>
                <label class="field full"><span>Meta description</span><textarea name="seo_description" rows="4" maxlength="320"><?= $val('seo_description') ?></textarea></label>
                <div class="field full"><span>Public URL</span><code class="url-preview"><?= e($publicUrl ?: url('/doctors/{slug}')) ?></code></div>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="preview">
            <article class="preview-card">
                <div class="preview-top">
                    <?php if (!empty($values['photo'])): ?>
                        <img src="<?= e(asset((string) $values['photo'])) ?>" alt="">
                    <?php else: ?>
                        <span class="thumb thumb-fallback lg"><?= e(mb_strtoupper(mb_substr((string) ($values['name'] ?? 'D'), 0, 1))) ?></span>
                    <?php endif; ?>
                    <div>
                        <h2 data-preview-name><?= e((string) ($values['name'] ?? 'Doctor name')) ?></h2>
                        <p class="muted" data-preview-qualification><?= e((string) ($values['qualification'] ?? 'Qualification')) ?></p>
                        <p class="muted" data-preview-slug><?= e((string) ($values['slug'] ?? 'slug')) ?></p>
                    </div>
                </div>
                <p data-preview-expertise><?= e($expertiseValue !== '' ? $expertiseValue : 'Expertise preview will appear here.') ?></p>
            </article>
        </div>
    </div>
    <div class="form-footer"><button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create doctor' ?></button></div>
</form>
