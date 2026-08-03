<?php
/**
 * Loading skeleton placeholders.
 * @var string $variant cards|table|feed|chart
 */
$variant = $variant ?? 'cards';
?>
<div class="skeleton-block skeleton-<?= e($variant) ?>" aria-hidden="true">
    <?php if ($variant === 'cards'): ?>
        <div class="skeleton-grid">
            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="skeleton-card">
                    <div class="skeleton-line w-40"></div>
                    <div class="skeleton-line w-24 lg"></div>
                    <div class="skeleton-line w-60"></div>
                </div>
            <?php endfor; ?>
        </div>
    <?php elseif ($variant === 'table'): ?>
        <div class="skeleton-card">
            <div class="skeleton-line w-40"></div>
            <?php for ($i = 0; $i < 5; $i++): ?>
                <div class="skeleton-line"></div>
            <?php endfor; ?>
        </div>
    <?php elseif ($variant === 'feed'): ?>
        <div class="skeleton-card">
            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="skeleton-line"></div>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <div class="skeleton-card skeleton-chart">
            <div class="skeleton-line w-40"></div>
            <div class="skeleton-bars">
                <?php for ($i = 0; $i < 7; $i++): ?>
                    <span style="--h: <?= 30 + ($i * 8) % 50 ?>%"></span>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
