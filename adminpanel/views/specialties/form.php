<?php
/** @var array<string,mixed>|null $specialty */
/** @var array<string,mixed> $values */
/** @var array<string,mixed> $options */
/** @var string $csrf */
$isEdit = $specialty !== null;
$action = $isEdit ? url('/specialties/' . (int) $specialty['id']) : url('/specialties');
$val = static fn (string $key, mixed $default = ''): string => e((string) ($values[$key] ?? $default));
?>
<section class="page-intro page-intro-row">
    <div>
        <h1><?= $isEdit ? 'Edit Specialty' : 'Add Specialty' ?></h1>
        <p><?= $isEdit ? 'Update this specialty category.' : 'Create a master specialty category.' ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost" href="<?= e(url('/specialties')) ?>">Back to list</a>
    </div>
</section>

<form method="post" action="<?= e($action) ?>" class="specialty-form panel" data-specialty-form>
    <?= $csrf ?>
    <div class="form-grid form-grid-simple">
        <label class="field"><span>Specialty name *</span><input type="text" name="name" value="<?= $val('name') ?>" required data-slug-source maxlength="150"></label>
        <label class="field"><span>Slug</span><input type="text" name="slug" value="<?= $val('slug') ?>" data-slug-target placeholder="auto-generated" maxlength="191"></label>
        <label class="field"><span>Status</span>
            <select name="status">
                <?php foreach ($options['statuses'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($values['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <p class="muted form-hint">Doctors and treatments reference specialties automatically. No relationship assignment is needed here.</p>
    <div class="form-footer"><button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create specialty' ?></button></div>
</form>
