<?php
/** @var string $message */
$message = $message ?? 'Nothing to show yet.';
?>
<div class="empty-state">
    <p><?= e($message) ?></p>
</div>
