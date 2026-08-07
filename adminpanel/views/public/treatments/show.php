<?php
/** @var array<string,mixed> $treatment */
/** @var string $title */

$name = (string) ($treatment['name'] ?? '');
$category = (string) ($treatment['category'] ?? '');
$specialty = (string) ($treatment['specialty_name'] ?? '');
$priceFrom = (string) ($treatment['price_from'] ?? '');
$overview = (string) ($treatment['introduction'] ?? $treatment['overview'] ?? '');
$symptoms = (string) ($treatment['symptoms'] ?? '');
$whenNeeded = (string) ($treatment['when_needed'] ?? '');
$procedure = (string) ($treatment['procedure_overview'] ?? '');
$recovery = (string) ($treatment['recovery'] ?? '');
$whyChoose = (string) ($treatment['why_choose'] ?? '');
$image = !empty($treatment['featured_image']) ? asset((string) $treatment['featured_image']) : null;
$canonical = 'https://www.vaidtrack.com/treatments/' . ((string) ($treatment['slug'] ?? ''));
$waNumber = '918979983149';
$waMessage = 'Hi, I need ' . $name . ' treatment guidance and a free plan.';
$waUrl = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waMessage);

$lines = static function (string $text): array {
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: [])));
};

/** Render a block of admin-authored text: a bulleted list if it has multiple
 *  lines, a plain paragraph block otherwise. Keeps sections visually varied
 *  instead of every section looking identical. */
$renderBlock = static function (string $text) use ($lines): string {
    $items = $lines($text);
    if (count($items) > 1) {
        $html = '<ul class="sec-list">';
        foreach ($items as $item) {
            $html .= '<li>' . e($item) . '</li>';
        }
        return $html . '</ul>';
    }
    return '<p>' . nl2br(e($text)) . '</p>';
};

$jsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => 'Organization', 'name' => 'VaidTrack.com', 'url' => 'https://www.vaidtrack.com', 'logo' => 'https://www.vaidtrack.com/images/vaidtrack-wordmark.png'],
        [
            '@type' => 'MedicalWebPage',
            'name' => $title,
            'url' => $canonical,
            'about' => ['@type' => 'MedicalCondition', 'name' => $name],
            'description' => (string) ($treatment['seo_description'] ?? $overview),
            'isPartOf' => ['@type' => 'WebSite', 'name' => 'VaidTrack.com', 'url' => 'https://www.vaidtrack.com'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://www.vaidtrack.com/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Treatment', 'item' => 'https://www.vaidtrack.com/treatment'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $canonical],
            ],
        ],
    ],
];

/** Icon-led section header. $icon is a raw <svg> string. */
$secHead = static function (string $icon, string $title): string {
    return '<div class="sec-icon-head reveal"><span class="sec-icon" aria-hidden="true">' . $icon . '</span><h2>' . e($title) . '</h2></div>';
};

$ICON_INFO = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 11v5.5M12 8v.01"/></svg>';
$ICON_ALERT = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4.5M12 17v.01M10.3 4.6L2.9 18a1.8 1.8 0 001.6 2.6h15a1.8 1.8 0 001.6-2.6L13.7 4.6a1.8 1.8 0 00-3.4 0z"/></svg>';
$ICON_CALENDAR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path stroke-linecap="round" d="M8 3v4M16 3v4M3 10h18M8.5 14.5l2 2 4-4"/></svg>';
$ICON_CLIPBOARD = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="4" width="12" height="17" rx="2"/><path stroke-linecap="round" d="M9 4V3a1 1 0 011-1h4a1 1 0 011 1v1M9 11h6M9 15h6"/></svg>';
$ICON_HEART = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.35-9.5-8.5C.9 8.2 2.4 5 5.6 5c1.9 0 3.3 1 4.4 2.5C11.1 6 12.5 5 14.4 5c3.2 0 4.7 3.2 3.1 6.5C15 15.65 12 20 12 20z"/></svg>';
$ICON_SHIELD = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.4-3 7.6-7 9-4-1.4-7-4.6-7-9V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>';
$ICON_USERS = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.2"/><path stroke-linecap="round" d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><circle cx="17.5" cy="9" r="2.6"/><path stroke-linecap="round" d="M15.5 14.6c2.6.3 4.5 2.2 4.5 5.4"/></svg>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<?php if (!empty($treatment['seo_description'])): ?>
<meta name="description" content="<?= e((string) $treatment['seo_description']) ?>">
<?php endif; ?>
<meta name="theme-color" content="#ffffff">
<link rel="icon" href="/favicon.ico" sizes="any">
<script>window.dataLayer=window.dataLayer||[];window.gtag=window.gtag||function(){window.dataLayer.push(arguments);};</script>
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($title) ?>">
<?php if (!empty($treatment['seo_description'])): ?>
<meta property="og:description" content="<?= e((string) $treatment['seo_description']) ?>">
<?php endif; ?>
<meta property="og:url" content="<?= e($canonical) ?>">
<?php if ($image): ?><meta property="og:image" content="<?= e($image) ?>"><?php endif; ?>
<link rel="dns-prefetch" href="https://www.googletagmanager.com">
<link rel="preload" href="/assets/fonts/inter-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/fonts-inter.css?v=20260803-perf3">
<link rel="stylesheet" href="/assets/css/treatment-page.css?v=20260807-unified1">
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</head>
<body>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KZ86XPT5" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<div class="scroll-progress" aria-hidden="true"></div>

<div class="topbar">Need urgent guidance? <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener">WhatsApp us - 24/7</a> &middot; Free second opinion, response <b>under 15 minutes</b></div>

<header class="tp-header">
  <div class="nav">
    <a class="tp-logo" href="/" aria-label="VaidTrack.com Home"><picture><source srcset="/images/vaidtrack-wordmark-nav.webp?v=20260803-perf3" type="image/webp"><img src="/images/vaidtrack-wordmark.png?v=20260803-perf3" alt="VaidTrack.com - For Medical Solutions" width="220" height="44" decoding="async" fetchpriority="high"></picture></a>
    <nav class="tp-nav" aria-label="Primary">
      <a href="/">Home</a>
      <a href="/treatment">Treatments</a>
      <a href="/all-doctors">Doctors</a>
      <a href="/faq">FAQ</a>
    </nav>
    <div class="navcta">
      <a class="btn btn-outline btn-sm" href="<?= e($waUrl) ?>" target="_blank" rel="noopener">WhatsApp</a>
      <a class="btn btn-primary btn-sm" href="/book-appointment"><span class="navcta-full">Book Appointment</span><span class="navcta-short">Book</span></a>
    </div>
  </div>
</header>

<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-copy reveal">
      <div class="eyebrow"><?= e($specialty !== '' ? $specialty : ($category !== '' ? $category : 'Oncology')) ?> &middot; India</div>
      <h1><?= e($name) ?> Treatment in India</h1>
      <?php if ($overview !== ''): ?>
        <p class="hero-sub"><?= e(mb_strimwidth($overview, 0, 220, '...')) ?></p>
      <?php endif; ?>
      <div class="hero-ctas">
        <a class="btn btn-primary" href="/book-appointment">Get Free Treatment Plan</a>
        <a class="btn btn-outline-dark" href="<?= e($waUrl) ?>" target="_blank" rel="noopener">Message on WhatsApp</a>
      </div>
    </div>

    <div class="formcard reveal">
      <div class="form-fields">
        <h3>Get a free second opinion</h3>
        <p class="resp">Reviewed by an oncologist &middot; response <b>under 15 minutes</b></p>
        <?php if ($priceFrom !== ''): ?><p class="resp">Packages typically start from <b><?= e($priceFrom) ?></b>.</p><?php endif; ?>
        <a class="btn btn-primary submit" href="<?= e($waUrl) ?>" target="_blank" rel="noopener">Chat on WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php if ($overview !== ''): ?>
<section class="tp-section" aria-label="About this treatment">
  <div class="wrap">
    <?= $secHead($ICON_INFO, 'About ' . $name) ?>
    <div class="sec-body reveal"><?= $renderBlock($overview) ?></div>
  </div>
</section>
<?php endif; ?>

<?php if ($symptoms !== ''): ?>
<section class="tp-section" aria-label="Symptoms">
  <div class="wrap">
    <?= $secHead($ICON_ALERT, 'Symptoms to Watch For') ?>
    <div class="sec-body reveal"><?= $renderBlock($symptoms) ?></div>
  </div>
</section>
<?php endif; ?>

<?php if ($whenNeeded !== ''): ?>
<section class="tp-section" aria-label="When treatment is needed">
  <div class="wrap">
    <?= $secHead($ICON_CALENDAR, 'When You Should Seek Treatment') ?>
    <div class="sec-body reveal"><?= $renderBlock($whenNeeded) ?></div>
  </div>
</section>
<?php endif; ?>

<?php if ($procedure !== ''): ?>
<section class="tp-section" aria-label="Procedure overview">
  <div class="wrap">
    <?= $secHead($ICON_CLIPBOARD, 'How the Treatment Works') ?>
    <div class="sec-body reveal"><?= $renderBlock($procedure) ?></div>
  </div>
</section>
<?php endif; ?>

<?php if ($recovery !== ''): ?>
<section class="tp-section" aria-label="Recovery and hospital stay">
  <div class="wrap">
    <?= $secHead($ICON_HEART, 'Recovery & Hospital Stay') ?>
    <div class="sec-body reveal"><?= $renderBlock($recovery) ?></div>
  </div>
</section>
<?php endif; ?>

<?php if ($whyChoose !== ''): ?>
<section class="tp-section" aria-label="Why choose VaidTrack">
  <div class="wrap">
    <?= $secHead($ICON_SHIELD, 'Why Choose VaidTrack.com') ?>
    <div class="sec-body reveal"><?= $renderBlock($whyChoose) ?></div>
  </div>
</section>
<?php endif; ?>

<section class="tp-section" id="doctors">
  <div class="wrap">
    <?= $secHead($ICON_USERS, 'The Team Reviewing Your Case') ?>
    <div class="docgrid" data-treatment="<?= e($name) ?>" aria-live="polite"></div>
  </div>
</section>

<footer class="tp-footer"><div class="wrap">&copy; 2026 VaidTrack.com &middot; Medical tourism facilitation platform. Content is educational, not a substitute for personalised medical advice. <a href="/privacy-policy">Privacy</a> &middot; <a href="/disclaimer">Disclaimer</a></div></footer>

<div class="sticky-bar" role="region" aria-label="Quick actions">
  <div class="sticky-bar-panel">
    <div class="sticky-bar-copy">
      <p class="sticky-bar-title">Send your reports. Hear back in under 15 minutes.</p>
      <p class="sticky-bar-sub">No obligation, no upfront cost to get a written opinion and estimate.</p>
    </div>
    <div class="sticky-bar-inner">
      <a class="btn btn-wa-rect" href="<?= e($waUrl) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">WhatsApp Us</a>
    </div>
    <div class="sticky-bar-legal">&copy; 2026 VaidTrack.com &middot; Medical tourism facilitation platform. Content is educational, not a substitute for personalised medical advice. <a href="/privacy-policy">Privacy</a> &middot; <a href="/disclaimer">Disclaimer</a></div>
  </div>
</div>

<script src="/assets/js/analytics.js?v=20260803-perf3" defer></script>
<script src="/assets/js/doctors.js?v=20260807-cachefix1" defer></script>
<script src="/assets/js/treatment-page.js?v=20260803-perf3" defer></script>
</body>
</html>
