<?php
/**
 * @var string $label
 * @var int|string $value
 * @var string $hint
 * @var string $accent
 * @var bool $ready
 */
$accent = $accent ?? 'blue';
$ready  = $ready ?? false;
?>
<article class="stat-card accent-<?= e($accent) ?> <?= $ready ? '' : 'is-pending' ?>">
    <div class="stat-card-top">
        <p class="stat-label"><?= e($label) ?></p>
        <?php if (!$ready): ?>
            <span class="badge badge-muted">Soon</span>
        <?php endif; ?>
    </div>
    <p class="stat-value" data-stat-value><?= e((string) $value) ?></p>
    <p class="stat-hint"><?= e($hint) ?></p>
</article>
