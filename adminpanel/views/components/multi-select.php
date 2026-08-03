<?php
/** @var string $name */
/** @var string $label */
/** @var list<array{id:int|string,name:string}> $options */
/** @var list<int|string> $selected */
$name = $name ?? 'ids[]';
$label = $label ?? 'Select';
$options = $options ?? [];
$selected = array_map('strval', $selected ?? []);
?>
<div class="multi-select" data-multi-select>
    <span class="field-label"><?= e($label) ?></span>
    <input type="search" class="multi-select-search" placeholder="Search <?= e(strtolower($label)) ?>…" data-multi-search>
    <div class="multi-select-list">
        <?php foreach ($options as $option): ?>
            <?php $id = (string) $option['id']; ?>
            <label class="multi-select-option" data-label="<?= e(strtolower((string) $option['name'])) ?>">
                <input type="checkbox" name="<?= e($name) ?>" value="<?= e($id) ?>" <?= in_array($id, $selected, true) ? 'checked' : '' ?>>
                <span><?= e((string) $option['name']) ?></span>
            </label>
        <?php endforeach; ?>
        <?php if ($options === []): ?><p class="muted multi-select-empty">No options available yet.</p><?php endif; ?>
    </div>
</div>
