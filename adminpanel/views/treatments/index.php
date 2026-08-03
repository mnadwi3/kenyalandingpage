<?php
/** @var list<array<string,mixed>> $treatments */
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

    return url('/treatments?' . http_build_query($params));
};
?>
<section class="page-intro page-intro-row">
    <div>
        <h1>Treatments</h1>
        <p>Manage treatment pages, relationships, and SEO.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/treatments/import')) ?>">Import CSV</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/treatments/export/csv?' . http_build_query($queryBase))) ?>">CSV</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/treatments/export/json?' . http_build_query($queryBase))) ?>">JSON</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/treatments/export/excel?' . http_build_query($queryBase))) ?>">Excel</a>
        <a class="btn btn-primary" href="<?= e(url('/treatments/create')) ?>">Add Treatment</a>
    </div>
</section>

<form method="get" action="<?= e(url('/treatments')) ?>" class="filter-panel">
    <div class="filter-grid">
        <label class="field"><span>Search</span><input type="search" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="Name, category, introduction…"></label>
        <label class="field"><span>Status</span>
            <select name="status"><option value="">All</option>
                <?php foreach ($options['statuses'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Featured</span>
            <select name="is_featured">
                <option value="">All</option>
                <option value="1" <?= (string) ($filters['is_featured'] ?? '') === '1' ? 'selected' : '' ?>>Featured</option>
                <option value="0" <?= (string) ($filters['is_featured'] ?? '') === '0' ? 'selected' : '' ?>>Not featured</option>
            </select>
        </label>
        <label class="field"><span>Category</span>
            <select name="category"><option value="">All</option>
                <?php foreach ($options['categories'] as $category): ?>
                    <option value="<?= e($category) ?>" <?= ($filters['category'] ?? '') === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Specialty</span>
            <select name="specialty_id"><option value="">All</option>
                <?php foreach ($options['specialties'] as $item): ?>
                    <option value="<?= (int) $item['id'] ?>" <?= (string) ($filters['specialty_id'] ?? '') === (string) $item['id'] ? 'selected' : '' ?>><?= e($item['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Hospital</span>
            <select name="hospital_id"><option value="">All</option>
                <?php foreach ($options['hospitals'] as $item): ?>
                    <option value="<?= (int) $item['id'] ?>" <?= (string) ($filters['hospital_id'] ?? '') === (string) $item['id'] ? 'selected' : '' ?>><?= e($item['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Doctor</span>
            <select name="doctor_id"><option value="">All</option>
                <?php foreach ($options['doctors'] as $item): ?>
                    <option value="<?= (int) $item['id'] ?>" <?= (string) ($filters['doctor_id'] ?? '') === (string) $item['id'] ? 'selected' : '' ?>><?= e($item['name']) ?></option>
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
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/treatments')) ?>">Reset</a>
    </div>
</form>

<form method="post" action="<?= e(url('/treatments/bulk')) ?>" id="bulk-treatments-form">
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
        <button type="submit" class="btn btn-ghost btn-sm" data-confirm="Apply bulk action to selected treatments?">Apply</button>
        <span class="muted"><?= (int) $paginator->total ?> result(s)</span>
    </div>
    <section class="panel">
        <?php if ($treatments === []): ?>
            <?php component('empty-state', ['message' => 'No treatments found. Add your first treatment to get started.']); ?>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table treatments-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Treatment</th>
                            <th><a href="<?= e($sortUrl('category')) ?>">Category</a></th>
                            <th>Specialty</th>
                            <th><a href="<?= e($sortUrl('status')) ?>">Status</a></th>
                            <th><a href="<?= e($sortUrl('is_featured')) ?>">Featured</a></th>
                            <th><a href="<?= e($sortUrl('updated_at')) ?>">Updated</a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($treatments as $treatment): ?>
                            <?php $trashed = !empty($treatment['deleted_at']); ?>
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="<?= (int) $treatment['id'] ?>" data-row-check></td>
                                <td>
                                    <div class="treatment-cell">
                                        <?php if (!empty($treatment['featured_image'])): ?>
                                            <img class="thumb thumb-rect" src="<?= e(asset((string) $treatment['featured_image'])) ?>" alt="">
                                        <?php else: ?>
                                            <span class="thumb thumb-rect thumb-fallback"><?= e(mb_strtoupper(mb_substr((string) $treatment['name'], 0, 1))) ?></span>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= e((string) $treatment['name']) ?></strong>
                                            <div class="muted"><?= e((string) $treatment['slug']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e((string) ($treatment['category'] ?: '—')) ?></td>
                                <td><?= e((string) ($treatment['specialty_name'] ?: '—')) ?></td>
                                <td><span class="status-pill status-<?= e((string) $treatment['status']) ?>"><?= e((string) $treatment['status']) ?></span></td>
                                <td><?= !empty($treatment['is_featured']) ? '<span class="badge badge-featured">Featured</span>' : '<span class="muted">—</span>' ?></td>
                                <td><?= e(format_datetime($treatment['updated_at'] ?? null)) ?></td>
                                <td class="actions-cell">
                                    <a href="<?= e(url('/treatments/' . (int) $treatment['id'] . '/edit')) ?>">Edit</a>
                                    <a href="<?= e(url('/treatments/' . rawurlencode((string) $treatment['slug']))) ?>" target="_blank" rel="noopener">Public</a>
                                    <?php if ($trashed): ?>
                                        <button type="submit" class="link-btn" form="treatment-restore-<?= (int) $treatment['id'] ?>" data-confirm="Restore this treatment?">Restore</button>
                                    <?php else: ?>
                                        <button type="submit" class="link-btn" form="treatment-duplicate-<?= (int) $treatment['id'] ?>">Duplicate</button>
                                        <button type="submit" class="link-btn danger" form="treatment-delete-<?= (int) $treatment['id'] ?>" data-confirm="Move this treatment to trash?">Delete</button>
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

<?php foreach ($treatments as $treatment): ?>
    <?php if (!empty($treatment['deleted_at'])): ?>
        <form id="treatment-restore-<?= (int) $treatment['id'] ?>" method="post" action="<?= e(url('/treatments/' . (int) $treatment['id'] . '/restore')) ?>" class="hidden-form"><?= $csrf ?></form>
    <?php else: ?>
        <form id="treatment-duplicate-<?= (int) $treatment['id'] ?>" method="post" action="<?= e(url('/treatments/' . (int) $treatment['id'] . '/duplicate')) ?>" class="hidden-form"><?= $csrf ?></form>
        <form id="treatment-delete-<?= (int) $treatment['id'] ?>" method="post" action="<?= e(url('/treatments/' . (int) $treatment['id'] . '/delete')) ?>" class="hidden-form"><?= $csrf ?></form>
    <?php endif; ?>
<?php endforeach; ?>

<?php component('pagination', ['paginator' => $paginator, 'baseUrl' => url('/treatments'), 'query' => $queryBase]); ?>
