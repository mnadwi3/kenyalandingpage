<?php
/**
 * @var array{title: string, subtitle?: string, labels: list<string>, values: list<int>, is_placeholder: bool} $chart
 */
$chart = $chart ?? ['title' => 'Chart', 'labels' => [], 'values' => [], 'is_placeholder' => true];
?>
<section class="panel chart-panel">
    <div class="panel-head">
        <div>
            <h2><?= e($chart['title']) ?></h2>
            <?php if (!empty($chart['subtitle'])): ?>
                <p class="muted"><?= e($chart['subtitle']) ?></p>
            <?php endif; ?>
        </div>
        <?php if (!empty($chart['is_placeholder'])): ?>
            <span class="badge badge-muted">Sample</span>
        <?php endif; ?>
    </div>
    <div class="chart-wrap">
        <canvas
            id="overviewChart"
            width="640"
            height="260"
            data-chart='<?= e(json_encode([
                'labels' => $chart['labels'],
                'values' => $chart['values'],
                'placeholder' => !empty($chart['is_placeholder']),
            ], JSON_UNESCAPED_UNICODE)) ?>'
        ></canvas>
    </div>
</section>
