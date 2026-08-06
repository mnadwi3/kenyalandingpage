<?php
/** @var string $activeNav */
$activeNav = $activeNav ?? 'dashboard';

$links = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => url('/dashboard')],
    ['key' => 'doctors', 'label' => 'Doctors', 'href' => url('/doctors')],
    ['key' => 'treatments', 'label' => 'Treatments', 'href' => url('/treatments')],
    ['key' => 'hospitals', 'label' => 'Hospitals', 'href' => url('/hospitals')],
    ['key' => 'specialties', 'label' => 'Specialties', 'href' => url('/specialties')],
    ['key' => 'testimonials', 'label' => 'Testimonials', 'href' => url('/testimonials')],
    ['key' => 'faqs', 'label' => 'FAQs', 'href' => url('/faqs')],
    ['key' => 'settings', 'label' => 'Hero Content', 'href' => url('/settings/hero')],
    ['key' => 'settings-accreditations', 'label' => 'Accreditation Icons', 'href' => url('/settings/accreditations')],
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
