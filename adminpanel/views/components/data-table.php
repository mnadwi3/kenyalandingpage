<?php
/**
 * @var string $title
 * @var list<string> $columns
 * @var list<list<string>> $rows
 * @var string|null $empty
 */
$title   = $title ?? 'Table';
$columns = $columns ?? [];
$rows    = $rows ?? [];
$empty   = $empty ?? 'No records yet.';
?>
<section class="panel">
    <div class="panel-head">
        <h2><?= e($title) ?></h2>
    </div>
    <?php if ($rows === []): ?>
        <?php component('empty-state', ['message' => $empty]); ?>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <th><?= e($column) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                                <td><?= $cell ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
