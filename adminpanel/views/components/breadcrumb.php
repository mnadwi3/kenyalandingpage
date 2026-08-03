<?php
/** @var list<array{label: string, href?: string}> $items */
$items = $items ?? [];
?>
<nav class="breadcrumb" aria-label="Breadcrumb">
    <ol>
        <?php foreach ($items as $index => $item): ?>
            <li>
                <?php if (!empty($item['href']) && $index < count($items) - 1): ?>
                    <a href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a>
                <?php else: ?>
                    <span aria-current="page"><?= e($item['label']) ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
