<?php
/** @var string $csrf */
$importErrors = \App\Core\Session::getFlash('import_errors') ?? [];
?>
<section class="page-intro page-intro-row">
    <div><h1>Import Doctors</h1><p>Upload a CSV file with doctor fields.</p></div>
    <a class="btn btn-ghost" href="<?= e(url('/doctors')) ?>">Back</a>
</section>
<section class="panel">
    <?php if (is_array($importErrors) && $importErrors !== []): ?>
        <div class="toast toast-error"><?php foreach ($importErrors as $error): ?><div><?= e((string) $error) ?></div><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/doctors/import')) ?>" enctype="multipart/form-data" class="stack-form">
        <?= $csrf ?>
        <label class="field"><span>CSV file</span><input type="file" name="csv_file" accept=".csv,text/csv" required></label>
        <p class="muted">Headers: name, slug, qualification, years_of_experience, expertise, education, status, seo_title…</p>
        <button type="submit" class="btn btn-primary">Import</button>
    </form>
</section>
