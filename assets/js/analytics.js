/**
 * Delayed third-party analytics loader.
 * Queues dataLayer/gtag calls until GTM/GA are ready.
 * Preserves tracking; avoids render-blocking.
 */
(function () {
  var GTM_ID = 'GTM-KZ86XPT5';
  var GA_ID = 'G-5TBH8QQ2EQ';
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

    var ga = document.createElement('script');
    ga.async = true;
    ga.src = 'https://www.googletagmanager.com/gtag/js?id=' + GA_ID;
    ga.onload = function () {
      window.gtag('js', new Date());
      window.gtag('config', GA_ID, { send_page_view: false });
    };
    document.head.appendChild(ga);
  }

  function schedule() {
    if ('requestIdleCallback' in window) {
      requestIdleCallback(loadAnalytics, { timeout: 3500 });
    } else {
      setTimeout(loadAnalytics, 2500);
    }
  }

  if (document.readyState === 'complete') {
    schedule();
  } else {
    window.addEventListener('load', schedule, { once: true });
  }

  // Warm start on first meaningful interaction
  ['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach(function (evt) {
    window.addEventListener(evt, loadAnalytics, { once: true, passive: true });
  });
})();
