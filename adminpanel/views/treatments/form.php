<?php
/** @var array<string,mixed>|null $treatment */
/** @var array<string,mixed> $values */
/** @var array<string,mixed> $options */
/** @var string $csrf */
$isEdit = $treatment !== null;
$action = $isEdit ? url('/treatments/' . (int) $treatment['id']) : url('/treatments');
$publicUrl = !empty($values['slug']) ? url('/treatments/' . $values['slug']) : null;
$val = static fn (string $key, mixed $default = ''): string => e((string) ($values[$key] ?? $default));
$checkedIds = static function (string $key) use ($values): array {
    $raw = $values[$key] ?? [];

    return is_array($raw) ? $raw : [];
};
$introduction = (string) ($values['introduction'] ?? $values['overview'] ?? '');
?>
<section class="page-intro page-intro-row">
    <div>
        <h1><?= $isEdit ? 'Edit Treatment' : 'Add Treatment' ?></h1>
        <p><?= $isEdit ? 'Update content, relationships, and SEO.' : 'Create a new treatment page.' ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost" href="<?= e(url('/treatments')) ?>">Back to list</a>
        <?php if ($publicUrl): ?><a class="btn btn-ghost" href="<?= e($publicUrl) ?>" target="_blank" rel="noopener">Public URL</a><?php endif; ?>
    </div>
</section>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="treatment-form" data-treatment-form>
    <?= $csrf ?>
    <div class="tabs" data-tabs>
        <div class="tab-list" role="tablist">
            <button type="button" class="tab is-active" data-tab="general">General</button>
            <button type="button" class="tab" data-tab="content">Content</button>
            <button type="button" class="tab" data-tab="relationships">Relationships</button>
            <button type="button" class="tab" data-tab="seo">SEO</button>
            <button type="button" class="tab" data-tab="preview">Preview</button>
        </div>

        <div class="tab-panel is-active" data-tab-panel="general">
            <div class="form-grid">
                <label class="field"><span>Treatment name *</span><input type="text" name="name" value="<?= $val('name') ?>" required data-slug-source></label>
                <label class="field"><span>Slug</span><input type="text" name="slug" value="<?= $val('slug') ?>" data-slug-target placeholder="auto-generated"></label>
                <label class="field"><span>Treatment category</span>
                    <input type="text" name="category" value="<?= $val('category') ?>" list="treatment-categories" placeholder="e.g. Oncology, Cardiology">
                    <datalist id="treatment-categories">
                        <?php foreach ($options['categories'] as $category): ?>
                            <option value="<?= e($category) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </label>
                <label class="field"><span>Specialty</span>
                    <select name="specialty_id">
                        <option value="">Select specialty</option>
                        <?php foreach ($options['specialties'] as $item): ?>
                            <option value="<?= (int) $item['id'] ?>" <?= (string) ($values['specialty_id'] ?? '') === (string) $item['id'] ? 'selected' : '' ?>><?= e($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="field"><span>Status</span>
                    <select name="status">
                        <?php foreach ($options['statuses'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= ($values['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="field checkbox-field"><input type="checkbox" name="is_featured" value="1" <?= !empty($values['is_featured']) ? 'checked' : '' ?>><span>Featured treatment</span></label>
                <div class="field">
                    <span>Featured image</span>
                    <?php if (!empty($values['featured_image'])): ?>
                        <div class="photo-preview">
                            <img src="<?= e(asset((string) $values['featured_image'])) ?>" alt="">
                            <label class="checkbox-field"><input type="checkbox" name="remove_image" value="1"><span>Remove current image</span></label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp,image/gif">
                </div>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="content">
            <div class="form-grid">
                <label class="field full"><span>Introduction</span><textarea name="introduction" rows="5" data-preview-intro><?= e($introduction) ?></textarea></label>
                <label class="field full"><span>Symptoms</span><textarea name="symptoms" rows="4"><?= $val('symptoms') ?></textarea></label>
                <label class="field full"><span>When is this treatment needed?</span><textarea name="when_needed" rows="4"><?= $val('when_needed') ?></textarea></label>
                <label class="field full"><span>Procedure overview</span><textarea name="procedure_overview" rows="5"><?= $val('procedure_overview') ?></textarea></label>
                <label class="field full"><span>Recovery &amp; hospital stay</span><textarea name="recovery" rows="4"><?= $val('recovery') ?></textarea></label>
                <label class="field full"><span>Why choose VaidTrack?</span><textarea name="why_choose" rows="4"><?= $val('why_choose') ?></textarea></label>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="relationships">
            <div class="relation-grid">
                <?php component('multi-select', ['name' => 'doctor_ids[]', 'label' => 'Related doctors', 'options' => $options['doctors'], 'selected' => $checkedIds('doctor_ids')]); ?>
                <?php component('multi-select', ['name' => 'hospital_ids[]', 'label' => 'Related hospitals', 'options' => $options['hospitals'], 'selected' => $checkedIds('hospital_ids')]); ?>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="seo">
            <div class="form-grid">
                <label class="field full"><span>SEO title</span><input type="text" name="seo_title" value="<?= $val('seo_title') ?>" maxlength="255"></label>
                <label class="field full"><span>Meta description</span><textarea name="seo_description" rows="4" maxlength="320"><?= $val('seo_description') ?></textarea></label>
                <div class="field full"><span>Public URL</span><code class="url-preview"><?= e($publicUrl ?: url('/treatments/{slug}')) ?></code></div>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="preview">
            <article class="preview-card">
                <div class="preview-top">
                    <?php if (!empty($values['featured_image'])): ?>
                        <img class="preview-image" src="<?= e(asset((string) $values['featured_image'])) ?>" alt="">
                    <?php else: ?>
                        <span class="thumb thumb-rect thumb-fallback lg"><?= e(mb_strtoupper(mb_substr((string) ($values['name'] ?? 'T'), 0, 1))) ?></span>
                    <?php endif; ?>
                    <div>
                        <h2 data-preview-name><?= e((string) ($values['name'] ?? 'Treatment name')) ?></h2>
                        <p class="muted" data-preview-category><?= e((string) ($values['category'] ?? 'Category')) ?></p>
                        <p class="muted" data-preview-slug><?= e((string) ($values['slug'] ?? 'slug')) ?></p>
                    </div>
                </div>
                <p data-preview-intro-text><?= e($introduction !== '' ? $introduction : 'Introduction preview will appear here.') ?></p>
            </article>
        </div>
    </div>
    <div class="form-footer"><button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create treatment' ?></button></div>
</form>
