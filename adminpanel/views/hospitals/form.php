<?php
/** @var array<string,mixed>|null $hospital */
/** @var array<string,mixed> $values */
/** @var array<string,mixed> $options */
/** @var string $csrf */
$isEdit = $hospital !== null;
$action = $isEdit ? url('/hospitals/' . (int) $hospital['id']) : url('/hospitals');
$publicUrl = !empty($values['slug']) ? url('/hospitals/' . $values['slug']) : null;
$val = static fn (string $key, mixed $default = ''): string => e((string) ($values[$key] ?? $default));
$checkedIds = static function (string $key) use ($values): array {
    $raw = $values[$key] ?? [];

    return is_array($raw) ? $raw : [];
};
$about = (string) ($values['about'] ?? $values['description'] ?? '');
$accreditationCodes = $checkedIds('accreditation_codes');
$serviceCodes = $checkedIds('international_service_codes');
?>
<section class="page-intro page-intro-row">
    <div>
        <h1><?= $isEdit ? 'Edit Hospital' : 'Add Hospital' ?></h1>
        <p><?= $isEdit ? 'Update profile, services, and SEO.' : 'Create a new hospital profile.' ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost" href="<?= e(url('/hospitals')) ?>">Back to list</a>
        <?php if ($publicUrl): ?><a class="btn btn-ghost" href="<?= e($publicUrl) ?>" target="_blank" rel="noopener">Public URL</a><?php endif; ?>
    </div>
</section>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="hospital-form" data-hospital-form>
    <?= $csrf ?>
    <div class="tabs" data-tabs>
        <div class="tab-list" role="tablist">
            <button type="button" class="tab is-active" data-tab="general">General</button>
            <button type="button" class="tab" data-tab="basic">Basic Information</button>
            <button type="button" class="tab" data-tab="services">International Services</button>
            <button type="button" class="tab" data-tab="relationships">Relationships</button>
            <button type="button" class="tab" data-tab="seo">SEO</button>
            <button type="button" class="tab" data-tab="preview">Preview</button>
        </div>

        <div class="tab-panel is-active" data-tab-panel="general">
            <div class="form-grid">
                <label class="field"><span>Hospital name *</span><input type="text" name="name" value="<?= $val('name') ?>" required data-slug-source></label>
                <label class="field"><span>Slug</span><input type="text" name="slug" value="<?= $val('slug') ?>" data-slug-target placeholder="auto-generated"></label>
                <label class="field"><span>Status</span>
                    <select name="status">
                        <?php foreach ($options['statuses'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= ($values['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="field checkbox-field"><input type="checkbox" name="is_featured" value="1" <?= !empty($values['is_featured']) ? 'checked' : '' ?>><span>Featured hospital</span></label>
                <label class="field checkbox-field"><input type="checkbox" name="is_verified" value="1" <?= !empty($values['is_verified']) ? 'checked' : '' ?>><span>Verification badge</span></label>
                <div class="field">
                    <span>Hospital logo</span>
                    <?php if (!empty($values['logo'])): ?>
                        <div class="photo-preview">
                            <img src="<?= e(asset((string) $values['logo'])) ?>" alt="">
                            <label class="checkbox-field"><input type="checkbox" name="remove_logo" value="1"><span>Remove current logo</span></label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif">
                </div>
                <div class="field">
                    <span>Hero image (cover)</span>
                    <?php if (!empty($values['cover_image'])): ?>
                        <div class="photo-preview">
                            <img class="cover-preview" src="<?= e(asset((string) $values['cover_image'])) ?>" alt="">
                            <label class="checkbox-field"><input type="checkbox" name="remove_cover" value="1"><span>Remove current cover</span></label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp,image/gif">
                </div>
                <label class="field"><span>Hero image alt text</span><input type="text" name="hero_image_alt" value="<?= $val('hero_image_alt') ?>" placeholder="Describe the hero image"></label>
                <label class="field"><span>Website</span><input type="url" name="website" value="<?= $val('website') ?>" placeholder="https://example.com"></label>
                <fieldset class="field full accreditation-fieldset">
                    <legend>Accreditation</legend>
                    <div class="checkbox-grid">
                        <?php foreach ($options['accreditations'] as $code => $meta): ?>
                            <label class="checkbox-field">
                                <input type="checkbox" name="accreditation_codes[]" value="<?= e($code) ?>" <?= in_array($code, $accreditationCodes, true) ? 'checked' : '' ?>>
                                <span class="accreditation-option">
                                    <img src="<?= e(url($meta['logo'])) ?>" alt="" width="28" height="28">
                                    <?= e($meta['label']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="basic">
            <div class="form-grid">
                <label class="field full"><span>Short description</span><textarea name="short_description" rows="2" maxlength="320" placeholder="One or two lines shown on hospital cards"><?= $val('short_description') ?></textarea></label>
                <label class="field full"><span>About hospital *</span><textarea name="about" id="about-editor" rows="8" required data-preview-about><?= e($about) ?></textarea></label>
                <label class="field"><span>Hospital type</span>
                    <select name="hospital_type">
                        <option value="">Select type</option>
                        <?php foreach ($options['types'] as $type): ?>
                            <option value="<?= e($type) ?>" <?= ($values['hospital_type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="field full"><span>Address line 1</span><input type="text" name="address_line1" value="<?= $val('address_line1') ?>"></label>
                <label class="field full"><span>Address line 2</span><input type="text" name="address_line2" value="<?= $val('address_line2') ?>"></label>
                <label class="field"><span>City</span><input type="text" name="city" value="<?= $val('city') ?>"></label>
                <label class="field"><span>State</span><input type="text" name="state" value="<?= $val('state') ?>"></label>
                <label class="field"><span>Pincode</span><input type="text" name="pincode" value="<?= $val('pincode') ?>"></label>
                <label class="field"><span>Country</span><input type="text" name="country" value="<?= $val('country') ?>"></label>
            </div>

            <fieldset class="field full">
                <legend>Quick Facts</legend>
                <div class="form-grid">
                    <label class="field"><span>Established (year)</span><input type="number" min="1800" max="<?= (int) date('Y') + 1 ?>" name="established_year" value="<?= $val('established_year') ?>" placeholder="e.g. 2009"></label>
                    <label class="field"><span>Beds</span><input type="number" min="0" name="number_of_beds" value="<?= $val('number_of_beds') ?>" placeholder="e.g. 1200"></label>
                    <label class="field"><span>ICU Beds</span><input type="number" min="0" name="icu_beds" value="<?= $val('icu_beds') ?>" placeholder="e.g. 250"></label>
                    <label class="field"><span>Operation Theatres</span><input type="number" min="0" name="operation_theatres" value="<?= $val('operation_theatres') ?>" placeholder="e.g. 35"></label>
                    <label class="field"><span>International Patients (Per Year)</span><input type="number" min="0" name="international_patients_per_year" value="<?= $val('international_patients_per_year') ?>" placeholder="e.g. 20000"></label>
                    <label class="field"><span>Airport Distance</span><input type="text" name="airport_distance" value="<?= $val('airport_distance') ?>" placeholder="e.g. 18 km"></label>
                </div>
                <p class="muted form-hint">Icons for these 6 facts are managed under <a href="<?= e(url('/settings/icons')) ?>" target="_blank" rel="noopener">Settings &rarr; Icons</a>.</p>
            </fieldset>
        </div>

        <div class="tab-panel" data-tab-panel="services">
            <div class="checkbox-grid services-grid">
                <?php foreach ($options['international_services'] as $code => $label): ?>
                    <label class="checkbox-field">
                        <input type="checkbox" name="international_service_codes[]" value="<?= e($code) ?>" <?= in_array($code, $serviceCodes, true) ? 'checked' : '' ?>>
                        <span><?= e($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="relationships">
            <div class="relation-grid">
                <?php component('multi-select', ['name' => 'treatment_ids[]', 'label' => 'Related treatments', 'options' => $options['treatments'], 'selected' => $checkedIds('treatment_ids')]); ?>
            </div>

            <fieldset class="field full">
                <legend>Specialties (Multi Select)</legend>
                <div class="checkbox-grid">
                    <?php foreach ($options['specialties'] as $specialty): ?>
                        <label class="checkbox-field">
                            <input type="checkbox" name="specialty_ids[]" value="<?= (int) $specialty['id'] ?>" <?= in_array((string) $specialty['id'], array_map('strval', $checkedIds('specialty_ids')), true) ? 'checked' : '' ?>>
                            <span class="accreditation-option">
                                <?php if (!empty($specialty['image'])): ?>
                                    <img src="<?= e(asset((string) $specialty['image'])) ?>" alt="" width="24" height="24">
                                <?php endif; ?>
                                <?= e((string) $specialty['name']) ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                    <?php if ($options['specialties'] === []): ?>
                        <p class="muted">No specialties yet — add some from the Specialties module.</p>
                    <?php endif; ?>
                </div>
            </fieldset>

            <div class="relation-grid">
                <?php component('multi-select', ['name' => 'doctor_ids[]', 'label' => 'Top Doctors', 'options' => $options['doctors'], 'selected' => $checkedIds('doctor_ids')]); ?>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="seo">
            <div class="form-grid">
                <label class="field full"><span>SEO title</span><input type="text" name="seo_title" value="<?= $val('seo_title') ?>" maxlength="255"></label>
                <label class="field full"><span>Meta description</span><textarea name="seo_description" rows="4" maxlength="320"><?= $val('seo_description') ?></textarea></label>
                <div class="field full"><span>Public URL</span><code class="url-preview"><?= e($publicUrl ?: url('/hospitals/{slug}')) ?></code></div>
            </div>
        </div>

        <div class="tab-panel" data-tab-panel="preview">
            <article class="preview-card hospital-preview">
                <div class="preview-top">
                    <?php if (!empty($values['logo'])): ?>
                        <img src="<?= e(asset((string) $values['logo'])) ?>" alt="">
                    <?php else: ?>
                        <span class="thumb thumb-rect thumb-fallback lg"><?= e(mb_strtoupper(mb_substr((string) ($values['name'] ?? 'H'), 0, 1))) ?></span>
                    <?php endif; ?>
                    <div>
                        <h2 data-preview-name><?= e((string) ($values['name'] ?? 'Hospital name')) ?></h2>
                        <p class="muted" data-preview-location><?php
                            $loc = array_filter([(string) ($values['city'] ?? ''), (string) ($values['country'] ?? '')]);
                            echo e($loc !== [] ? implode(', ', $loc) : 'Location');
                        ?></p>
                        <p class="muted" data-preview-slug><?= e((string) ($values['slug'] ?? 'slug')) ?></p>
                    </div>
                </div>
                <p data-preview-about-text><?= e($about !== '' ? $about : 'About preview will appear here.') ?></p>
            </article>
        </div>
    </div>
    <div class="form-footer"><button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create hospital' ?></button></div>
</form>
