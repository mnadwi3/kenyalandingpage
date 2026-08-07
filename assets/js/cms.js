/* ============================================
   VaidTrack.com - CMS sync (hospitals, testimonials,
   FAQs, hero) from the admin panel API.
   Treatment cards are handled by treatments.js.

   Same pattern as doctors.js: fetch JSON, render into
   existing mount points, keep hardcoded markup as a
   fallback if the API is unreachable.
   ============================================ */
(function () {
  // Same server as the site; adjust if the admin panel is mounted elsewhere.
  var API_BASE = '/adminpanel';

  function escapeHtml(str) {
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function fetchJson(path) {
    return fetch(API_BASE + path, { credentials: 'omit' }).then(function (res) {
      if (!res.ok) throw new Error(path + ' failed (' + res.status + ')');
      return res.json();
    });
  }

  function observeReveal(nodes) {
    if (!nodes || !nodes.length) return;
    if (!('IntersectionObserver' in window)) {
      nodes.forEach(function (n) { n.classList.add('visible'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    nodes.forEach(function (n) { io.observe(n); });
  }

  /* ---------- Hero ---------- */
  function renderHero(hero) {
    if (!hero) return;
    var sub = document.getElementById('hero-subheadline');
    if (sub && hero.subheadline) sub.textContent = hero.subheadline;

    var list = document.getElementById('hero-trust-list');
    if (list && Array.isArray(hero.trust_points) && hero.trust_points.length) {
      var items = list.querySelectorAll('li span:last-child');
      hero.trust_points.forEach(function (text, i) {
        if (items[i]) items[i].textContent = text;
      });
    }
  }

  /* ---------- Hospitals ---------- */
  function renderHospitalCard(h) {
    var img = h.cover_image || h.logo || '';
    var imgHtml = img
      ? '<img src="' + escapeHtml(img) + '" alt="' + escapeHtml(h.name) + '" class="w-full h-full object-cover" width="500" height="250" loading="lazy" decoding="async">'
      : '';

    var description = (h.description || '').trim();
    if (description.length > 220) description = description.slice(0, 217).trim() + '…';
    var descriptionHtml = description
      ? '<p class="text-slate-600 text-sm leading-relaxed mb-3 hospital-card-about">' + escapeHtml(description) + '</p>'
      : '';

    var addressLines = [h.address_line1, h.address_line2].filter(Boolean).map(escapeHtml).join('<br>');
    var addressHtml = addressLines
      ? '<p class="text-sm font-semibold text-secondary mb-1">Address</p>' +
        '<p class="text-slate-600 text-sm leading-relaxed mb-2">' + addressLines + '</p>'
      : '';

    var cityLine = [h.city, h.pincode, h.country].filter(Boolean).map(escapeHtml).join(', ');
    var cityLineHtml = cityLine
      ? '<p class="text-slate-600 text-sm leading-relaxed mb-3">' + cityLine + '</p>'
      : '';

    var accreditationHtml = '';
    if (Array.isArray(h.accreditation_logos) && h.accreditation_logos.length) {
      accreditationHtml = '<div class="hospital-card-accreditations flex items-center gap-1">' +
        h.accreditation_logos.map(function (a) {
          return '<img src="' + escapeHtml(a.logo) + '" alt="' + escapeHtml(a.label) + '" title="' + escapeHtml(a.label) + '" loading="lazy" decoding="async">';
        }).join('') +
        '</div>';
    }

    var href = h.url || '#';

    return (
      '<a class="hospital-card bg-white rounded-2xl shadow-card overflow-hidden border border-slate-100" href="' + escapeHtml(href) + '">' +
        '<div class="hospital-card-img">' + imgHtml + accreditationHtml + '</div>' +
        '<div class="hospital-card-info bg-[var(--section-bg)] p-6 flex flex-col justify-center">' +
          '<h3 class="font-display text-xl sm:text-2xl font-bold text-primary mb-2">' + escapeHtml(h.name) + '</h3>' +
          '<div class="w-12 h-1 bg-gold rounded-full mb-4" aria-hidden="true"></div>' +
          descriptionHtml +
          addressHtml +
          cityLineHtml +
        '</div>' +
      '</a>'
    );
  }

  function renderHospitals(list) {
    var track = document.getElementById('hospitals-track');
    if (!track || !Array.isArray(list) || !list.length) return;
    track.innerHTML = list.map(renderHospitalCard).join('');
    // The inline carousel script clones/animates this track at parse time
    // using the original card count; restart it so it re-measures the
    // live card list instead of animating against stale geometry.
    if (typeof window.__startHospitalsCarousel === 'function') {
      window.__startHospitalsCarousel();
    }
  }

  /* ---------- Testimonials ---------- */
  function renderTestimonialCard(t, i) {
    var ytId = t.youtube_id || '';
    var thumb = t.thumbnail || (ytId ? 'https://i.ytimg.com/vi_webp/' + ytId + '/hqdefault.webp' : '');

    return (
      '<div class="reveal aspect-video rounded-2xl overflow-hidden bg-secondary border border-slate-200 shadow-soft">' +
        '<button type="button" class="yt-facade" data-youtube-id="' + escapeHtml(ytId) + '" aria-label="Play ' + escapeHtml(t.patient_name || 'patient story video ' + (i + 1)) + '">' +
          (thumb ? '<img src="' + escapeHtml(thumb) + '" alt="" width="480" height="360" loading="lazy" decoding="async">' : '') +
          '<span class="yt-facade-play" aria-hidden="true"></span>' +
        '</button>' +
      '</div>'
    );
  }

  function renderTestimonials(list) {
    var grid = document.getElementById('testimonials-grid');
    if (!grid || !Array.isArray(list) || !list.length) return;
    grid.innerHTML = list.map(renderTestimonialCard).join('');
    observeReveal(grid.querySelectorAll('.reveal'));
  }

  /* ---------- FAQs ---------- */
  function renderFaqItem(f) {
    return (
      '<div class="faq-item group border border-slate-200 rounded-2xl bg-white overflow-hidden transition-all duration-300 hover:border-primary/30 hover:shadow-xl hover:shadow-primary/5">' +
        '<button type="button" class="faq-trigger w-full flex items-center justify-between gap-4 text-left px-5 sm:px-7 py-5 sm:py-6 font-semibold text-secondary transition-colors duration-200 group-hover:text-primary" aria-expanded="false">' +
          '<span>' + escapeHtml(f.question) + '</span>' +
          '<span class="faq-chevron-wrap shrink-0 flex items-center justify-center w-9 h-9 rounded-full bg-slate-50 text-primary transition-colors duration-300 group-hover:bg-primary/10">' +
            '<svg class="faq-chevron w-4 h-4 transition-transform duration-300 ease-out" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24">' +
              '<path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>' +
          '</span>' +
        '</button>' +
        '<div class="faq-answer"><div class="faq-answer-inner px-5 sm:px-7 pb-5 sm:pb-6 text-sm sm:text-base text-slate-600 leading-relaxed border-t border-slate-100 pt-4">' +
          '<p>' + escapeHtml(f.answer || '') + '</p>' +
        '</div></div>' +
      '</div>'
    );
  }

  function bindFaqAccordion(container) {
    var items = container.querySelectorAll('.faq-item');
    items.forEach(function (item) {
      var trigger = item.querySelector('.faq-trigger');
      if (!trigger) return;
      trigger.addEventListener('click', function () {
        var isOpen = item.classList.contains('open');
        items.forEach(function (other) {
          other.classList.remove('open');
          var t = other.querySelector('.faq-trigger');
          if (t) t.setAttribute('aria-expanded', 'false');
        });
        if (!isOpen) {
          item.classList.add('open');
          trigger.setAttribute('aria-expanded', 'true');
        }
      });
    });
  }

  function renderFaqs(list) {
    var container = document.getElementById('faq-list');
    if (!container || !Array.isArray(list) || !list.length) return;
    container.innerHTML = list.map(renderFaqItem).join('');
    bindFaqAccordion(container);
  }

  function init() {
    fetchJson('/api/hero.json').then(renderHero).catch(function () {});
    fetchJson('/api/hospitals.json').then(function (data) {
      renderHospitals(Array.isArray(data) ? data : (data.items || []));
    }).catch(function () {});
    fetchJson('/api/testimonials.json').then(renderTestimonials).catch(function () {});
    fetchJson('/api/faqs.json').then(renderFaqs).catch(function () {});
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
