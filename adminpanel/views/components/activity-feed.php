<?php
/**
 * @var list<array{type?: string, title: string, meta?: string, timestamp?: ?string}> $items
 * @var string $title
 */
$title = $title ?? 'Recent activity';
$items = $items ?? [];
?>
<section class="panel">
    <div class="panel-head">
        <h2><?= e($title) ?></h2>
    </div>
    <?php if ($items === []): ?>
        <?php component('empty-state', ['message' => 'No recent activity yet. Sign-ins will appear here.']); ?>
    <?php else: ?>
        <ul class="activity-feed">
            <?php foreach ($items as $item): ?>
                <li class="activity-item">
                    <div class="activity-dot" aria-hidden="true"></div>
                    <div>
                        <p class="activity-title"><?= e($item['title']) ?></p>
                        <?php if (!empty($item['meta'])): ?>
                            <p class="activity-meta"><?= e($item['meta']) ?></p>
                        <?php endif; ?>
                        <p class="activity-time"><?= e(format_datetime($item['timestamp'] ?? null)) ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
