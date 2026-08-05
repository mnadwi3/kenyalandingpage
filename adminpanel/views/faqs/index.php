<?php
/** @var list<array<string,mixed>> $faqs */
/** @var \App\Support\Paginator $paginator */
/** @var array<string,mixed> $filters */
/** @var string $csrf */
$queryBase = $_GET;
unset($queryBase['page']);
?>
<section class="page-intro page-intro-row">
    <div>
        <h1>FAQs</h1>
        <p>Manage the homepage frequently-asked-questions list.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?= e(url('/faqs/create')) ?>">Add FAQ</a>
    </div>
</section>

<form method="get" action="<?= e(url('/faqs')) ?>" class="filter-panel">
    <div class="filter-grid">
        <label class="field"><span>Search</span><input type="search" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="Question…"></label>
        <label class="field"><span>Status</span>
            <select name="status">
                <option value="">All</option>
                <?php foreach (\App\Models\Faq::STATUSES as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="field"><span>&nbsp;</span><button type="submit" class="btn btn-ghost">Filter</button></div>
    </div>
</form>

<table class="data-table">
    <thead>
        <tr><th>Question</th><th>Status</th><th>Order</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($faqs as $row): ?>
            <tr>
                <td><?= e((string) $row['question']) ?></td>
                <td><?= e(ucfirst((string) $row['status'])) ?></td>
                <td><?= (int) $row['sort_order'] ?></td>
                <td class="row-actions">
                    <a class="btn btn-ghost btn-sm" href="<?= e(url('/faqs/' . (int) $row['id'] . '/edit')) ?>">Edit</a>
                    <form method="post" action="<?= e(url('/faqs/' . (int) $row['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this FAQ?');">
                        <?= $csrf ?>
                        <button type="submit" class="btn btn-ghost btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($faqs === []): ?>
            <tr><td colspan="4">No FAQs yet.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php component('pagination', ['paginator' => $paginator, 'baseUrl' => url('/faqs'), 'query' => $queryBase]); ?>
