<?php
/** @var string $activeNav */
$activeNav = $activeNav ?? 'dashboard';

$links = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => url('/dashboard')],
    ['key' => 'doctors', 'label' => 'Doctors', 'href' => url('/doctors')],
    ['key' => 'treatments', 'label' => 'Treatments', 'href' => url('/treatments')],
    ['key' => 'hospitals', 'label' => 'Hospitals', 'href' => url('/hospitals')],
    ['key' => 'specialties', 'label' => 'Specialties', 'href' => url('/specialties')],
];
?>
<aside class="admin-sidebar" data-sidebar>
    <p class="sidebar-section">Main</p>
    <nav class="sidebar-nav" aria-label="Primary">
        <?php foreach ($links as $link): ?>
            <a
                class="nav-item <?= $activeNav === $link['key'] ? 'is-active' : '' ?>"
                href="<?= e($link['href']) ?>"
            ><?= e($link['label']) ?></a>
        <?php endforeach; ?>
    </nav>
</aside>
