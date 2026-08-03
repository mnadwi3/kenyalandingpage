<?php
/** @var list<array<string,mixed>> $hospitals */
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

    return url('/hospitals?' . http_build_query($params));
};
?>
<section class="page-intro page-intro-row">
    <div>
        <h1>Hospitals</h1>
        <p>Manage hospital profiles, accreditations, and relationships.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/hospitals/import')) ?>">Import CSV</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/hospitals/export/csv?' . http_build_query($queryBase))) ?>">CSV</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/hospitals/export/json?' . http_build_query($queryBase))) ?>">JSON</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/hospitals/export/excel?' . http_build_query($queryBase))) ?>">Excel</a>
        <a class="btn btn-primary" href="<?= e(url('/hospitals/create')) ?>">Add Hospital</a>
    </div>
</section>

<form method="get" action="<?= e(url('/hospitals')) ?>" class="filter-panel">
    <div class="filter-grid">
        <label class="field"><span>Search</span><input type="search" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="Name, city, country…"></label>
        <label class="field"><span>Status</span>
            <select name="status"><option value="">All</option>
                <?php foreach ($options['statuses'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Type</span>
            <select name="hospital_type"><option value="">All</option>
                <?php foreach ($options['types'] as $type): ?>
                    <option value="<?= e($type) ?>" <?= ($filters['hospital_type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field"><span>Accreditation</span>
            <select name="accreditation"><option value="">All</option>
                <?php foreach ($options['accreditations'] as $code => $meta): ?>
                    <option value="<?= e($code) ?>" <?= ($filters['accreditation'] ?? '') === $code ? 'selected' : '' ?>><?= e($meta['label']) ?></option>
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
        <label class="field"><span>Verified</span>
            <select name="is_verified">
                <option value="">All</option>
                <option value="1" <?= (string) ($filters['is_verified'] ?? '') === '1' ? 'selected' : '' ?>>Verified</option>
                <option value="0" <?= (string) ($filters['is_verified'] ?? '') === '0' ? 'selected' : '' ?>>Not verified</option>
            </select>
        </label>
        <label class="field"><span>Treatment</span>
            <select name="treatment_id"><option value="">All</option>
                <?php foreach ($options['treatments'] as $item): ?>
                    <option value="<?= (int) $item['id'] ?>" <?= (string) ($filters['treatment_id'] ?? '') === (string) $item['id'] ? 'selected' : '' ?>><?= e($item['name']) ?></option>
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
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/hospitals')) ?>">Reset</a>
    </div>
</form>

<form method="post" action="<?= e(url('/hospitals/bulk')) ?>" id="bulk-hospitals-form">
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
        <button type="submit" class="btn btn-ghost btn-sm" data-confirm="Apply bulk action to selected hospitals?">Apply</button>
        <span class="muted"><?= (int) $paginator->total ?> result(s)</span>
    </div>
    <section class="panel">
        <?php if ($hospitals === []): ?>
            <?php component('empty-state', ['message' => 'No hospitals found. Add your first hospital to get started.']); ?>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table hospitals-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Hospital</th>
                            <th><a href="<?= e($sortUrl('hospital_type')) ?>">Type</a></th>
                            <th><a href="<?= e($sortUrl('city')) ?>">Location</a></th>
                            <th><a href="<?= e($sortUrl('status')) ?>">Status</a></th>
                            <th><a href="<?= e($sortUrl('is_featured')) ?>">Featured</a></th>
                            <th><a href="<?= e($sortUrl('updated_at')) ?>">Updated</a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hospitals as $hospital): ?>
                            <?php $trashed = !empty($hospital['deleted_at']); ?>
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="<?= (int) $hospital['id'] ?>" data-row-check></td>
                                <td>
                                    <div class="hospital-cell">
                                        <?php if (!empty($hospital['logo'])): ?>
                                            <img class="thumb thumb-rect" src="<?= e(asset((string) $hospital['logo'])) ?>" alt="">
                                        <?php else: ?>
                                            <span class="thumb thumb-rect thumb-fallback"><?= e(mb_strtoupper(mb_substr((string) $hospital['name'], 0, 1))) ?></span>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= e((string) $hospital['name']) ?></strong>
                                            <div class="muted"><?= e((string) $hospital['slug']) ?></div>
                                            <?php if (!empty($hospital['is_verified'])): ?>
                                                <span class="badge badge-verified">Verified</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e((string) ($hospital['hospital_type'] ?: '—')) ?></td>
                                <td><?php
                                    $loc = array_filter([
                                        (string) ($hospital['city'] ?? ''),
                                        (string) ($hospital['country'] ?? ''),
                                    ]);
                                    echo e($loc !== [] ? implode(', ', $loc) : '—');
                                ?></td>
                                <td><span class="status-pill status-<?= e((string) $hospital['status']) ?>"><?= e((string) $hospital['status']) ?></span></td>
                                <td><?= !empty($hospital['is_featured']) ? '<span class="badge badge-featured">Featured</span>' : '<span class="muted">—</span>' ?></td>
                                <td><?= e(format_datetime($hospital['updated_at'] ?? null)) ?></td>
                                <td class="actions-cell">
                                    <a href="<?= e(url('/hospitals/' . (int) $hospital['id'] . '/edit')) ?>">Edit</a>
                                    <a href="<?= e(url('/hospitals/' . rawurlencode((string) $hospital['slug']))) ?>" target="_blank" rel="noopener">Public</a>
                                    <?php if ($trashed): ?>
                                        <button type="submit" class="link-btn" form="hospital-restore-<?= (int) $hospital['id'] ?>" data-confirm="Restore this hospital?">Restore</button>
                                    <?php else: ?>
                                        <button type="submit" class="link-btn" form="hospital-duplicate-<?= (int) $hospital['id'] ?>">Duplicate</button>
                                        <button type="submit" class="link-btn danger" form="hospital-delete-<?= (int) $hospital['id'] ?>" data-confirm="Move this hospital to trash?">Delete</button>
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

<?php foreach ($hospitals as $hospital): ?>
    <?php if (!empty($hospital['deleted_at'])): ?>
        <form id="hospital-restore-<?= (int) $hospital['id'] ?>" method="post" action="<?= e(url('/hospitals/' . (int) $hospital['id'] . '/restore')) ?>" class="hidden-form"><?= $csrf ?></form>
    <?php else: ?>
        <form id="hospital-duplicate-<?= (int) $hospital['id'] ?>" method="post" action="<?= e(url('/hospitals/' . (int) $hospital['id'] . '/duplicate')) ?>" class="hidden-form"><?= $csrf ?></form>
        <form id="hospital-delete-<?= (int) $hospital['id'] ?>" method="post" action="<?= e(url('/hospitals/' . (int) $hospital['id'] . '/delete')) ?>" class="hidden-form"><?= $csrf ?></form>
    <?php endif; ?>
<?php endforeach; ?>

<?php component('pagination', ['paginator' => $paginator, 'baseUrl' => url('/hospitals'), 'query' => $queryBase]); ?>
