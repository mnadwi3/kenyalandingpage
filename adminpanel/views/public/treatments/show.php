<?php
/** @var array<string,mixed> $treatment */
/** @var string $title */

$name = (string) ($treatment['name'] ?? '');
$category = (string) ($treatment['category'] ?? '');
$specialty = (string) ($treatment['specialty_name'] ?? '');
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

/** Section header. */
$secHead = static function (string $title): string {
    return '<div class="sec-icon-head reveal"><h2>' . e($title) . '</h2></div>';
};
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
<link rel="stylesheet" href="/assets/css/treatment-page.css?v=20260807-layout2">
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

<section class="hero hero-plain">
  <div class="wrap">
    <div class="eyebrow center reveal"><?= e($specialty !== '' ? $specialty : ($category !== '' ? $category : 'Oncology')) ?> &middot; India</div>
    <h1 class="center reveal"><?= e($name) ?> Treatment in India</h1>
  </div>
</section>

<section class="tp-section" aria-label="Treatment details">
  <div class="wrap detail-layout">
    <div class="detail-main">
      <?php if ($overview !== ''): ?>
      <div class="detail-block">
        <?= $secHead('About ' . $name) ?>
        <div class="sec-body reveal"><?= $renderBlock($overview) ?></div>
      </div>
      <?php endif; ?>

      <?php if ($symptoms !== ''): ?>
      <div class="detail-block">
        <?= $secHead('Symptoms to Watch For') ?>
        <div class="sec-body reveal"><?= $renderBlock($symptoms) ?></div>
      </div>
      <?php endif; ?>

      <?php if ($whenNeeded !== ''): ?>
      <div class="detail-block">
        <?= $secHead('When You Should Seek Treatment') ?>
        <div class="sec-body reveal"><?= $renderBlock($whenNeeded) ?></div>
      </div>
      <?php endif; ?>

      <?php if ($procedure !== ''): ?>
      <div class="detail-block">
        <?= $secHead('How the Treatment Works') ?>
        <div class="sec-body reveal"><?= $renderBlock($procedure) ?></div>
      </div>
      <?php endif; ?>

      <?php if ($recovery !== ''): ?>
      <div class="detail-block">
        <?= $secHead('Recovery & Hospital Stay') ?>
        <div class="sec-body reveal"><?= $renderBlock($recovery) ?></div>
      </div>
      <?php endif; ?>

      <?php if ($whyChoose !== ''): ?>
      <div class="detail-block">
        <?= $secHead('Why Choose VaidTrack.com') ?>
        <div class="sec-body reveal"><?= $renderBlock($whyChoose) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <div class="detail-sidebar">
      <div class="sidebar-cta reveal">
        <a class="btn btn-primary" href="/book-appointment">Get Free Treatment Plan</a>
        <a class="btn btn-wa" href="<?= e($waUrl) ?>" target="_blank" rel="noopener">Chat on WhatsApp</a>
        <p class="sidebar-note">Get Response within 2 Hours</p>
      </div>
    </div>
  </div>
</section>

<section class="tp-section" id="doctors">
  <div class="wrap">
    <?= $secHead('Top Doctors for ' . $name) ?>
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
