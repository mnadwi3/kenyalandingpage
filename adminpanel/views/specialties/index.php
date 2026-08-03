<?php
/** @var list<array<string,mixed>> $specialties */
/** @var \App\Support\Paginator $paginator */
/** @var array<string,mixed> $filters */
/** @var string $sort */
/** @var string $direction */
/** @var array<string,mixed> $options */
/** @var string $csrf */

$queryBase = $_GET;
unset($queryBase['page']);
$sortUrl = static function (string $column) use ($queryBase, $sort, $direction): string {
    $params = $queryBase;
    $params['sort'] = $column;
    $params['direction'] = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';

    return url('/specialties?' . http_build_query($params));
};
?>
<section class="page-intro page-intro-row">
    <div>
        <h1>Specialties</h1>
        <p>Master categories used to organize doctors and treatments.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/specialties/import')) ?>">Import CSV</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/specialties/export/csv?' . http_build_query($queryBase))) ?>">CSV</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/specialties/export/json?' . http_build_query($queryBase))) ?>">JSON</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/specialties/export/excel?' . http_build_query($queryBase))) ?>">Excel</a>
        <a class="btn btn-primary" href="<?= e(url('/specialties/create')) ?>">Add Specialty</a>
    </div>
</section>

<form method="get" action="<?= e(url('/specialties')) ?>" class="filter-panel">
    <div class="filter-grid filter-grid-simple">
        <label class="field"><span>Search</span><input type="search" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="Name or slug…"></label>
        <label class="field"><span>Status</span>
            <select name="status"><option value="">All</option>
                <?php foreach ($options['statuses'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Trash</span>
            <select name="trashed">
                <option value="active" <?= ($filters['trashed'] ?? '') === 'active' ? 'selected' : '' ?>>Active records</option>
                <option value="only" <?= ($filters['trashed'] ?? '') === 'only' ? 'selected' : '' ?>>Trash only</option>
                <option value="with" <?= ($filters['trashed'] ?? '') === 'with' ? 'selected' : '' ?>>Include trash</option>
            </select>
        </label>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary btn-sm">Apply filters</button>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/specialties')) ?>">Reset</a>
    </div>
</form>

<form method="post" action="<?= e(url('/specialties/bulk')) ?>" id="bulk-specialties-form">
    <?= $csrf ?>
    <div class="bulk-bar">
        <label class="bulk-select-all"><input type="checkbox" data-select-all> <span>Select all</span></label>
        <select name="bulk_action" required>
            <option value="">Bulk action</option>
            <option value="active">Set active</option>
            <option value="inactive">Set inactive</option>
            <option value="draft">Set draft</option>
            <option value="archived">Set archived</option>
            <option value="delete">Move to trash</option>
            <option value="restore">Restore</option>
        </select>
        <button type="submit" class="btn btn-ghost btn-sm" data-confirm="Apply bulk action to selected specialties?">Apply</button>
        <span class="muted"><?= (int) $paginator->total ?> result(s)</span>
    </div>
    <section class="panel">
        <?php if ($specialties === []): ?>
            <?php component('empty-state', ['message' => 'No specialties found. Add your first specialty to get started.']); ?>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table specialties-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th><a href="<?= e($sortUrl('name')) ?>">Specialty Name</a></th>
                            <th><a href="<?= e($sortUrl('slug')) ?>">Slug</a></th>
                            <th><a href="<?= e($sortUrl('status')) ?>">Status</a></th>
                            <th><a href="<?= e($sortUrl('created_at')) ?>">Created Date</a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($specialties as $specialty): ?>
                            <?php $trashed = !empty($specialty['deleted_at']); ?>
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="<?= (int) $specialty['id'] ?>" data-row-check></td>
                                <td><strong><?= e((string) $specialty['name']) ?></strong></td>
                                <td class="muted"><?= e((string) $specialty['slug']) ?></td>
                                <td><span class="status-pill status-<?= e((string) $specialty['status']) ?>"><?= e((string) $specialty['status']) ?></span></td>
                                <td><?= e(format_datetime($specialty['created_at'] ?? null)) ?></td>
                                <td class="actions-cell">
                                    <a href="<?= e(url('/specialties/' . (int) $specialty['id'] . '/edit')) ?>">Edit</a>
                                    <?php if ($trashed): ?>
                                        <button type="submit" class="link-btn" form="specialty-restore-<?= (int) $specialty['id'] ?>" data-confirm="Restore this specialty?">Restore</button>
                                    <?php else: ?>
                                        <button type="submit" class="link-btn danger" form="specialty-delete-<?= (int) $specialty['id'] ?>" data-confirm="Move this specialty to trash?">Delete</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</form>

<?php foreach ($specialties as $specialty): ?>
    <?php if (!empty($specialty['deleted_at'])): ?>
        <form id="specialty-restore-<?= (int) $specialty['id'] ?>" method="post" action="<?= e(url('/specialties/' . (int) $specialty['id'] . '/restore')) ?>" class="hidden-form"><?= $csrf ?></form>
    <?php else: ?>
        <form id="specialty-delete-<?= (int) $specialty['id'] ?>" method="post" action="<?= e(url('/specialties/' . (int) $specialty['id'] . '/delete')) ?>" class="hidden-form"><?= $csrf ?></form>
    <?php endif; ?>
<?php endforeach; ?>

<?php component('pagination', ['paginator' => $paginator, 'baseUrl' => url('/specialties'), 'query' => $queryBase]); ?>
