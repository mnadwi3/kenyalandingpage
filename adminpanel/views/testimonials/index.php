<?php
/** @var list<array<string,mixed>> $testimonials */
/** @var \App\Support\Paginator $paginator */
/** @var array<string,mixed> $filters */
/** @var string $csrf */
$queryBase = $_GET;
unset($queryBase['page']);
?>
<section class="page-intro page-intro-row">
    <div>
        <h1>Testimonials</h1>
        <p>Manage the video testimonials shown on the homepage.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?= e(url('/testimonials/create')) ?>">Add Testimonial</a>
    </div>
</section>

<form method="get" action="<?= e(url('/testimonials')) ?>" class="filter-panel">
    <div class="filter-grid">
        <label class="field"><span>Search</span><input type="search" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="Patient or treatment…"></label>
        <label class="field"><span>Status</span>
            <select name="status">
                <option value="">All</option>
                <?php foreach (\App\Models\Testimonial::STATUSES as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="field"><span>&nbsp;</span><button type="submit" class="btn btn-ghost">Filter</button></div>
    </div>
</form>

<table class="data-table">
    <thead>
        <tr><th>Patient</th><th>Treatment</th><th>Status</th><th>Order</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($testimonials as $row): ?>
            <tr>
                <td><?= e((string) $row['patient_name']) ?></td>
                <td><?= e((string) ($row['treatment_name'] ?? '—')) ?></td>
                <td><?= e(ucfirst((string) $row['status'])) ?></td>
                <td><?= (int) $row['sort_order'] ?></td>
                <td class="row-actions">
                    <a class="btn btn-ghost btn-sm" href="<?= e(url('/testimonials/' . (int) $row['id'] . '/edit')) ?>">Edit</a>
                    <form method="post" action="<?= e(url('/testimonials/' . (int) $row['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this testimonial?');">
                        <?= $csrf ?>
                        <button type="submit" class="btn btn-ghost btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($testimonials === []): ?>
            <tr><td colspan="5">No testimonials yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php component('pagination', ['paginator' => $paginator, 'baseUrl' => url('/testimonials'), 'query' => $queryBase]); ?>
