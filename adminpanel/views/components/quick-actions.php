<?php
/**
 * @var list<array{label: string, href: string, enabled: bool, phase: int}> $actions
 */
$actions = $actions ?? [];
?>
<section class="panel">
    <div class="panel-head">
        <h2>Quick actions</h2>
    </div>
    <div class="quick-actions">
        <?php foreach ($actions as $action): ?>
            <?php if (!empty($action['enabled'])): ?>
                <a class="btn btn-primary" href="<?= e($action['href']) ?>"><?= e($action['label']) ?></a>
            <?php else: ?>
                <button
                    type="button"
                    class="btn btn-ghost"
                    data-toast-message="Available in Phase <?= (int) $action['phase'] ?>"
                    data-toast-type="info"
                >
                    <?= e($action['label']) ?>
                    <span class="badge badge-muted">Phase <?= (int) $action['phase'] ?></span>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>
