<?php
/** @var list<array<string,mixed>> $doctors */
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

    return url('/doctors?' . http_build_query($params));
};
?>
<section class="page-intro page-intro-row">
    <div>
        <h1>Doctors</h1>
        <p>Manage doctor profiles, relationships, and SEO.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/doctors/import')) ?>">Import CSV</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/doctors/export/csv?' . http_build_query($queryBase))) ?>">CSV</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/doctors/export/json?' . http_build_query($queryBase))) ?>">JSON</a>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/doctors/export/excel?' . http_build_query($queryBase))) ?>">Excel</a>
        <a class="btn btn-primary" href="<?= e(url('/doctors/create')) ?>">Add Doctor</a>
    </div>
</section>

<form method="get" action="<?= e(url('/doctors')) ?>" class="filter-panel">
    <div class="filter-grid">
        <label class="field"><span>Search</span><input type="search" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="Name, qualification, expertise…"></label>
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
        <a class="btn btn-ghost btn-sm" href="<?= e(url('/doctors')) ?>">Reset</a>
    </div>
</form>

<form method="post" action="<?= e(url('/doctors/bulk')) ?>" id="bulk-doctors-form">
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
        <button type="submit" class="btn btn-ghost btn-sm" data-confirm="Apply bulk action to selected doctors?">Apply</button>
        <span class="muted"><?= (int) $paginator->total ?> result(s)</span>
    </div>
    <section class="panel">
        <?php if ($doctors === []): ?>
            <?php component('empty-state', ['message' => 'No doctors found. Add your first doctor to get started.']); ?>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table doctors-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Doctor</th>
                            <th><a href="<?= e($sortUrl('status')) ?>">Status</a></th>
                            <th><a href="<?= e($sortUrl('is_featured')) ?>">Featured</a></th>
                            <th><a href="<?= e($sortUrl('years_of_experience')) ?>">Years</a></th>
                            <th>Expertise</th>
                            <th><a href="<?= e($sortUrl('updated_at')) ?>">Updated</a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $doctor): ?>
                            <?php $trashed = !empty($doctor['deleted_at']); ?>
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="<?= (int) $doctor['id'] ?>" data-row-check></td>
                                <td>
                                    <div class="doctor-cell">
                                        <?php if (!empty($doctor['photo'])): ?>
                                            <img class="thumb" src="<?= e(asset((string) $doctor['photo'])) ?>" alt="">
                                        <?php else: ?>
                                            <span class="thumb thumb-fallback"><?= e(mb_strtoupper(mb_substr((string) $doctor['name'], 0, 1))) ?></span>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= e((string) $doctor['name']) ?></strong>
                                            <div class="muted"><?= e((string) ($doctor['qualification'] ?: $doctor['slug'])) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="status-pill status-<?= e((string) $doctor['status']) ?>"><?= e((string) $doctor['status']) ?></span></td>
                                <td><?= !empty($doctor['is_featured']) ? '<span class="badge badge-featured">Featured</span>' : '<span class="muted">—</span>' ?></td>
                                <td><?= e((string) ($doctor['years_of_experience'] ?? '—')) ?></td>
                                <td><?php
                                    $expertise = (string) ($doctor['expertise'] ?? $doctor['experience_summary'] ?? '');
                                    echo e($expertise !== '' ? mb_strimwidth($expertise, 0, 80, '…') : '—');
                                ?></td>
                                <td><?= e(format_datetime($doctor['updated_at'] ?? null)) ?></td>
                                <td class="actions-cell">
                                    <a href="<?= e(url('/doctors/' . (int) $doctor['id'] . '/edit')) ?>">Edit</a>
                                    <a href="<?= e(url('/doctors/' . rawurlencode((string) $doctor['slug']))) ?>" target="_blank" rel="noopener">Public</a>
                                    <?php if ($trashed): ?>
                                        <button type="submit" class="link-btn" form="doctor-restore-<?= (int) $doctor['id'] ?>" data-confirm="Restore this doctor?">Restore</button>
                                    <?php else: ?>
                                        <button type="submit" class="link-btn" form="doctor-duplicate-<?= (int) $doctor['id'] ?>">Duplicate</button>
                                        <button type="submit" class="link-btn danger" form="doctor-delete-<?= (int) $doctor['id'] ?>" data-confirm="Move this doctor to trash?">Delete</button>
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

<?php foreach ($doctors as $doctor): ?>
    <?php if (!empty($doctor['deleted_at'])): ?>
        <form id="doctor-restore-<?= (int) $doctor['id'] ?>" method="post" action="<?= e(url('/doctors/' . (int) $doctor['id'] . '/restore')) ?>" class="hidden-form"><?= $csrf ?></form>
    <?php else: ?>
        <form id="doctor-duplicate-<?= (int) $doctor['id'] ?>" method="post" action="<?= e(url('/doctors/' . (int) $doctor['id'] . '/duplicate')) ?>" class="hidden-form"><?= $csrf ?></form>
        <form id="doctor-delete-<?= (int) $doctor['id'] ?>" method="post" action="<?= e(url('/doctors/' . (int) $doctor['id'] . '/delete')) ?>" class="hidden-form"><?= $csrf ?></form>
    <?php endif; ?>
<?php endforeach; ?>

<?php component('pagination', ['paginator' => $paginator, 'baseUrl' => url('/doctors'), 'query' => $queryBase]); ?>
