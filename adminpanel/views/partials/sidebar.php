<?php
/** @var string $activeNav */
$activeNav = $activeNav ?? 'dashboard';

$links = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => url('/dashboard'), 'ready' => true],
    ['key' => 'doctors', 'label' => 'Doctors', 'href' => url('/doctors'), 'ready' => true],
    ['key' => 'treatments', 'label' => 'Treatments', 'href' => url('/treatments'), 'ready' => true],
    ['key' => 'hospitals', 'label' => 'Hospitals', 'href' => url('/hospitals'), 'ready' => true],
    ['key' => 'specialties', 'label' => 'Specialties', 'href' => '#', 'ready' => false, 'phase' => 6],
    ['key' => 'enquiries', 'label' => 'Enquiries', 'href' => '#', 'ready' => false, 'phase' => 7],
    ['key' => 'settings', 'label' => 'Settings', 'href' => '#', 'ready' => false, 'phase' => 8],
];
?>
<aside class="admin-sidebar" data-sidebar>
    <p class="sidebar-section">Main</p>
    <nav class="sidebar-nav" aria-label="Primary">
        <?php foreach ($links as $link): ?>
            <?php if ($link['ready']): ?>
                <a
                    class="nav-item <?= $activeNav === $link['key'] ? 'is-active' : '' ?>"
                    href="<?= e($link['href']) ?>"
                ><?= e($link['label']) ?></a>
            <?php else: ?>
                <button
                    type="button"
                    class="nav-item nav-item-btn is-disabled"
                    data-toast-message="<?= e($link['label']) ?> arrives in Phase <?= (int) $link['phase'] ?>"
                    data-toast-type="info"
                >
                    <span><?= e($link['label']) ?></span>
                    <span class="badge badge-muted">Soon</span>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</aside>
