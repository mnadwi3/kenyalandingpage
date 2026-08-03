<?php
/**
 * @var array|null $user
 * @var list<array<string, mixed>> $stats
 * @var list<array<string, mixed>> $recent_enquiries
 * @var list<array<string, mixed>> $recent_activities
 * @var array<string, mixed> $chart
 * @var list<array<string, mixed>> $quick_actions
 */

$enquiryRows = [];
foreach ($recent_enquiries as $enquiry) {
    $status = (string) ($enquiry['status'] ?? 'new');
    $enquiryRows[] = [
        e((string) ($enquiry['patient_name'] ?? '—')),
        e((string) ($enquiry['treatment_name'] ?? '—')),
        e((string) ($enquiry['hospital_name'] ?? '—')),
        e((string) ($enquiry['country_name'] ?? '—')),
        '<span class="status-pill status-' . e($status) . '">' . e($status) . '</span>',
        e(format_datetime($enquiry['created_at'] ?? null)),
    ];
}
?>

<section class="page-intro">
    <div>
        <h1>Dashboard</h1>
        <p>Overview of platform health. Content counts unlock as modules are installed.</p>
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
                'title'   => 'Recent enquiries',
                'columns' => ['Patient', 'Treatment', 'Hospital', 'Country', 'Status', 'Created'],
                'rows'    => $enquiryRows,
                'empty'   => 'No enquiries yet. Leads will appear here after the Enquiries module (Phase 7).',
            ]); ?>

            <?php component('quick-actions', ['actions' => $quick_actions]); ?>
        </div>
    </div>
</div>
