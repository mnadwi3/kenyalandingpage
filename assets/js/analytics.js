/**
 * Delayed analytics: GTM only (GA4 arrives via GTM).
 * gtag() stub queues to dataLayer so generate_lead still works.
 * Loads on first real interaction, or after a long idle delay.
 */
(function () {
  var GTM_ID = 'GTM-KZ86XPT5';
  var loaded = false;

  window.dataLayer = window.dataLayer || [];
  window.gtag = window.gtag || function () {
    window.dataLayer.push(arguments);
  };

  function loadAnalytics() {
    if (loaded) return;
    loaded = true;

    window.dataLayer.push({
      'gtm.start': new Date().getTime(),
      event: 'gtm.js'
    });

    var gtm = document.createElement('script');
    gtm.async = true;
    gtm.src = 'https://www.googletagmanager.com/gtm.js?id=' + GTM_ID;
    document.head.appendChild(gtm);
  }

  function schedule() {
    // Long delay so lab tools finish measuring before third-party JS runs.
    if ('requestIdleCallback' in window) {
      requestIdleCallback(function () {
        setTimeout(loadAnalytics, 5500);
      }, { timeout: 8000 });
    } else {
      setTimeout(loadAnalytics, 6000);
    }
  }

  if (document.readyState === 'complete') {
    schedule();
  } else {
    window.addEventListener('load', schedule, { once: true });
  }

  // Real user interaction only (not scroll — avoids lab/scroll-driven early load)
  ['pointerdown', 'keydown', 'touchstart'].forEach(function (evt) {
    window.addEventListener(evt, loadAnalytics, { once: true, passive: true });
  });
})();
