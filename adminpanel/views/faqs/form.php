<?php
/** @var array<string,mixed>|null $faq */
/** @var array<string,mixed> $values */
/** @var string $csrf */
$isEdit = $faq !== null;
$action = $isEdit ? url('/faqs/' . (int) $faq['id']) : url('/faqs');
$val = static fn (string $key, mixed $default = ''): string => e((string) ($values[$key] ?? $default));
?>
<section class="page-intro page-intro-row">
    <div>
        <h1><?= $isEdit ? 'Edit FAQ' : 'Add FAQ' ?></h1>
        <p>Shown in the homepage FAQ accordion.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost" href="<?= e(url('/faqs')) ?>">Back to list</a>
    </div>
</section>

<form method="post" action="<?= e($action) ?>" class="form-grid">
    <?= $csrf ?>
    <label class="field full"><span>Question *</span><input type="text" name="question" value="<?= $val('question') ?>" required></label>
    <label class="field full"><span>Answer</span><textarea name="answer" rows="5"><?= $val('answer') ?></textarea></label>
    <label class="field"><span>Status</span>
        <select name="status">
            <?php foreach (\App\Models\Faq::STATUSES as $status): ?>
                <option value="<?= e($status) ?>" <?= ($values['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="field"><span>Sort order</span><input type="number" name="sort_order" value="<?= $val('sort_order', '0') ?>"></label>
    <div class="form-footer full"><button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create FAQ' ?></button></div>
</form>
