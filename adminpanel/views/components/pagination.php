<?php
/** @var \App\Support\Paginator $paginator */
/** @var string $baseUrl */
/** @var array<string, mixed> $query */
if (!$paginator->hasPages()) {
    return;
}
?>
<nav class="pagination" aria-label="Pagination">
    <?php for ($page = 1; $page <= $paginator->lastPage(); $page++): ?>
        <?php $params = $query; $params['page'] = $page; ?>
        <a class="page-link <?= $page === $paginator->page ? 'is-active' : '' ?>" href="<?= e($baseUrl . '?' . http_build_query($params)) ?>"><?= $page ?></a>
    <?php endfor; ?>
</nav>
