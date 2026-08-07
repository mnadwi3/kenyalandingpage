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

$faqs = [
    [
        'q' => 'Is ' . $name . ' treatment in India safe for international patients?',
        'a' => 'Yes. Partner hospitals follow international protocols, with NABH/JCI accreditation pathways, experienced specialists, and dedicated international patient desks.',
    ],
];
if ($priceFrom !== '') {
    $faqs[] = [
        'q' => 'How much does ' . $name . ' treatment cost in India?',
        'a' => 'Packages typically start around ' . $priceFrom . ', but the final cost depends on stage, treatment plan and hospital. We share a written estimate after report review.',
    ];
}
$faqs[] = [
    'q' => 'How fast can I get a second opinion?',
    'a' => 'Most families receive an initial specialist opinion within 24-48 hours after sharing complete reports.',
];
$faqs[] = [
    'q' => 'Do I need a medical visa?',
    'a' => 'Usually yes. We guide invitation letters, hospital documentation, and visa steps for the patient and one attendant.',
];

$faqSchema = array_map(static fn (array $f): array => [
    '@type' => 'Question',
    'name' => $f['q'],
    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
], $faqs);

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
        ['@type' => 'FAQPage', 'mainEntity' => $faqSchema],
    ],
];
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
<link rel="stylesheet" href="/assets/css/treatment-page.css?v=20260803-perf3">
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

    <?php if ($overview !== ''): ?>
    <div class="hero-overview">
      <div class="sechead reveal">
        <div class="kicker">Overview</div>
        <h2>Understanding <?= e($name) ?></h2>
      </div>
      <div class="overview-grid reveal">
        <article class="overview-card">
          <p><?= nl2br(e($overview)) ?></p>
        </article>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if ($symptoms !== '' || $whenNeeded !== ''): ?>
<section class="tp-section sym-dx" aria-label="Symptoms and when treatment is needed">
  <div class="wrap sym-dx-grid">
    <?php if ($symptoms !== ''): ?>
    <div class="sym-dx-col">
      <div class="sechead reveal">
        <div class="kicker">Symptoms</div>
        <h2>Common warning signs</h2>
        <p>These symptoms do not always mean cancer - but they should be checked by a specialist, especially if they persist.</p>
      </div>
      <ul class="checklist reveal checklist-wide">
        <?php foreach ($lines($symptoms) as $item): ?>
          <li><span class="mark" aria-hidden="true">&#10003;</span><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <?php if ($whenNeeded !== ''): ?>
    <div class="sym-dx-col">
      <div class="sechead reveal">
        <div class="kicker">When is it needed</div>
        <h2>When this treatment is recommended</h2>
      </div>
      <p class="diag-note reveal"><?= nl2br(e($whenNeeded)) ?></p>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($procedure !== ''): ?>
<section class="tp-section" aria-label="Procedure overview">
  <div class="wrap">
    <div class="sechead reveal">
      <div class="kicker">Procedure</div>
      <h2>What the treatment involves</h2>
    </div>
    <p class="reveal"><?= nl2br(e($procedure)) ?></p>
  </div>
</section>
<?php endif; ?>

<?php if ($recovery !== ''): ?>
<section class="tp-section" aria-label="Recovery and hospital stay">
  <div class="wrap">
    <div class="sechead reveal">
      <div class="kicker">Recovery</div>
      <h2>Recovery &amp; hospital stay</h2>
    </div>
    <p class="reveal"><?= nl2br(e($recovery)) ?></p>
  </div>
</section>
<?php endif; ?>

<?php if ($whyChoose !== ''): ?>
<section class="tp-section" aria-label="Why choose VaidTrack">
  <div class="wrap">
    <div class="sechead reveal">
      <div class="kicker">Why VaidTrack</div>
      <h2>Why choose VaidTrack.com</h2>
    </div>
    <p class="reveal"><?= nl2br(e($whyChoose)) ?></p>
  </div>
</section>
<?php endif; ?>

<section class="tp-section" id="doctors">
  <div class="wrap">
    <div class="sechead reveal">
      <div class="kicker">Oncology specialists</div>
      <h2>The team reviewing your case</h2>
    </div>
    <div class="docgrid" data-treatment="<?= e($name) ?>" aria-live="polite"></div>
  </div>
</section>

<section class="tp-section" id="faq">
  <div class="wrap">
    <div class="sechead reveal">
      <div class="kicker">FAQ</div>
      <h2>Common questions</h2>
    </div>
    <?php foreach ($faqs as $i => $f): ?>
      <div class="faq-item<?= $i === 0 ? ' open' : '' ?>">
        <button class="faq-trigger" type="button" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"><?= e($f['q']) ?><span class="x" aria-hidden="true">+</span></button>
        <div class="faq-panel"><p><?= e($f['a']) ?></p></div>
      </div>
    <?php endforeach; ?>
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
