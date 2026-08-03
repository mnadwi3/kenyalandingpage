<?php
/**
 * @var array|null $user
 * @var list<array<string, mixed>> $stats
 * @var list<array<string, mixed>> $recent_doctors
 * @var list<array<string, mixed>> $recent_activities
 * @var array<string, mixed> $chart
 * @var list<array<string, mixed>> $quick_actions
 */

$doctorRows = [];
foreach ($recent_doctors as $doctor) {
    $status = (string) ($doctor['status'] ?? 'draft');
    $doctorRows[] = [
        e((string) ($doctor['name'] ?? '—')),
        e((string) ($doctor['qualification'] ?? '—')),
        !empty($doctor['is_featured']) ? '<span class="badge badge-featured">Featured</span>' : '<span class="muted">—</span>',
        '<span class="status-pill status-' . e($status) . '">' . e($status) . '</span>',
        e(format_datetime($doctor['updated_at'] ?? null)),
    ];
}
?>

<section class="page-intro">
    <div>
        <h1>Dashboard</h1>
        <p>Overview of doctors, treatments, hospitals, and specialties.</p>
    </div>
</section>

<div class="dashboard" data-dashboard data-loading="true">
    <div class="dashboard-skeletons" data-dashboard-skeleton>
        <?php component('skeleton', ['variant' => 'cards']); ?>
        <div class="dashboard-grid">
            <?php component('skeleton', ['variant' => 'chart']); ?>
            <?php component('skeleton', ['variant' => 'feed']); ?>
        </div>
        <?php component('skeleton', ['variant' => 'table']); ?>
    </div>

    <div class="dashboard-content is-hidden" data-dashboard-content>
        <div class="stat-grid">
            <?php foreach ($stats as $stat): ?>
                <?php component('stat-card', [
                    'label'  => $stat['label'],
                    'value'  => number_format((int) $stat['value']),
                    'hint'   => $stat['hint'],
                    'accent' => $stat['accent'],
                    'ready'  => (bool) $stat['ready'],
                ]); ?>
            <?php endforeach; ?>
        </div>

        <div class="dashboard-grid">
            <?php component('chart-panel', ['chart' => $chart]); ?>
            <?php component('activity-feed', [
                'title' => 'Recent activity',
                'items' => $recent_activities,
            ]); ?>
        </div>

        <div class="dashboard-grid dashboard-grid-bottom">
            <?php component('data-table', [
                'title'   => 'Recent doctors',
                'columns' => ['Doctor', 'Qualification', 'Featured', 'Status', 'Updated'],
                'rows'    => $doctorRows,
                'empty'   => 'No doctors yet. Add a doctor to get started.',
            ]); ?>

            <?php component('quick-actions', ['actions' => $quick_actions]); ?>
        </div>
    </div>
</div>
