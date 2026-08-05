/* ============================================
   VaidTrack.com - Treatment detail SEO sync.
   Keeps <title> / meta description / OG / canonical tags
   in sync with the admin panel without touching page body
   content or layout (kept as static, hand-authored HTML).
   ============================================ */
(function () {
  var API_BASE = '/adminpanel';

  function slugFromPath() {
    var match = window.location.pathname.match(/\/treatments\/([a-z0-9-]+)(?:\.html)?\/?$/i);
    return match ? match[1] : null;
  }

  function setMeta(selector, attr, value) {
    var el = document.querySelector(selector);
    if (el && value) el.setAttribute(attr, value);
  }

  function applySeo(t) {
    if (!t) return;
    var title = t.seo_title || t.name;
    var description = t.seo_description || '';

    if (title) document.title = title;
    setMeta('meta[name="description"]', 'content', description);
    setMeta('meta[property="og:title"]', 'content', title);
    setMeta('meta[property="og:description"]', 'content', description);
    setMeta('meta[name="twitter:title"]', 'content', title);
    setMeta('meta[name="twitter:description"]', 'content', description);
  }

  function init() {
    var slug = slugFromPath();
    if (!slug) return;
    fetch(API_BASE + '/api/treatments/' + slug + '.json', { credentials: 'omit' })
      .then(function (res) {
        if (!res.ok) throw new Error('treatment API ' + res.status);
        return res.json();
      })
      .then(applySeo)
      .catch(function () {});
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
