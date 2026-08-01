/* ============================================
   tabeebway.com — Main JavaScript
   ============================================ */

/* Hero slider */
(function () {
  var slides = document.querySelectorAll('.hero-slide');
  var dots = document.querySelectorAll('.hero-dot');
  if (!slides.length) return;

  var idx = 0;
  var timer = null;
  var INTERVAL = 15000;

  function goTo(n) {
    idx = (n + slides.length) % slides.length;
    slides.forEach(function (s, i) {
      var on = i === idx;
      s.classList.toggle('active', on);
      s.setAttribute('aria-hidden', on ? 'false' : 'true');
    });
    dots.forEach(function (d, i) {
      d.classList.toggle('active', i === idx);
    });
  }

  function next() { goTo(idx + 1); }
  function prev() { goTo(idx - 1); }

  function stop() {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  function start() {
    stop();
    timer = setInterval(next, INTERVAL);
  }

  var nextBtn = document.querySelector('.hero-arrow.next');
  var prevBtn = document.querySelector('.hero-arrow.prev');
  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      next();
      start();
    });
  }
  if (prevBtn) {
    prevBtn.addEventListener('click', function () {
      prev();
      start();
    });
  }
  dots.forEach(function (d) {
    d.addEventListener('click', function () {
      goTo(Number(d.getAttribute('data-dot')));
      start();
    });
  });

  var formCard = document.querySelector('#hero .appointment-card');
  if (formCard) {
    formCard.addEventListener('mouseenter', stop);
    formCard.addEventListener('mouseleave', start);
    formCard.addEventListener('focusin', stop);
    formCard.addEventListener('focusout', function () {
      if (!formCard.contains(document.activeElement)) start();
    });
  }

  document.addEventListener('visibilitychange', function () {
    if (document.hidden) stop();
    else start();
  });

  goTo(0);
  start();
})();

/* Order sections: Treatment → Doctors → Journey (how-it-works) → Testimonials → About Us → FAQ */
(function () {
  var order = ['treatment', 'doctors', 'how-it-works', 'testimonials', 'about-us', 'faq'];
  var anchor = document.getElementById('why-india');
  if (!anchor) return;
  var cursor = anchor;
  order.forEach(function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    cursor.insertAdjacentElement('afterend', el);
    cursor = el;
  });
})();

/* Scroll reveal */
(function () {
  var nodes = document.querySelectorAll('.reveal');
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
})();

/* Sticky WhatsApp after hero */
(function () {
  var btn = document.getElementById('whatsapp-float');
  var hero = document.getElementById('hero');
  if (!btn || !hero) return;
  function update() {
    var bottom = hero.offsetTop + hero.offsetHeight;
    btn.classList.toggle('show', window.scrollY > bottom - 80);
  }
  window.addEventListener('scroll', update, { passive: true });
  update();
})();

/* FAQ */
(function () {
  var items = document.querySelectorAll('.faq-item');
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
})();

/* Mobile nav removed */

/* Forms → Google Sheet, then generate_lead, then WhatsApp */
(function () {
  var GAS_URL = 'https://script.google.com/macros/s/AKfycbzTo3v4gxY1FmMrSFndbYSDaWRhjL58uUzkicjfDW7xS1xdoL_Rxsq69juo2PcT3g2q_Q/exec';
  var WA_NUMBER = '918979983149';

  function field(form, names) {
    for (var i = 0; i < names.length; i++) {
      var el = form.elements.namedItem(names[i]);
      if (el && el.value) return String(el.value).trim();
    }
    return '';
  }

  function getLeadPayload(form) {
    return {
      name: field(form, ['name']),
      whatsapp: field(form, ['whatsapp', 'phone']),
      country: field(form, ['country']),
      condition: field(form, ['condition', 'treatment'])
    };
  }

  function buildWhatsAppUrl(payload) {
    var lines = [
      'New enquiry from website:',
      'Name: ' + (payload.name || ''),
      'WhatsApp: ' + (payload.whatsapp || ''),
      'Country: ' + (payload.country || ''),
      'Condition: ' + (payload.condition || '')
    ];
    return 'https://wa.me/' + WA_NUMBER + '?text=' + encodeURIComponent(lines.join('\n'));
  }

  function showFormError(form, message) {
    var el = form.querySelector('.form-submit-error');
    if (!el) {
      el = document.createElement('p');
      el.className = 'form-submit-error text-center text-sm font-semibold mt-3';
      el.style.color = '#DC2626';
      form.appendChild(el);
    }
    el.textContent = message;
    el.hidden = false;
  }

  function clearFormError(form) {
    var el = form.querySelector('.form-submit-error');
    if (el) {
      el.textContent = '';
      el.hidden = true;
    }
  }

  function setSubmitting(form, isSubmitting) {
    var btn = form.querySelector('[type="submit"]');
    if (btn) btn.disabled = !!isSubmitting;
  }

  function submitLeadToSheet(payload) {
    var body = JSON.stringify(payload);
    var qs =
      'name=' + encodeURIComponent(payload.name || '') +
      '&whatsapp=' + encodeURIComponent(payload.whatsapp || '') +
      '&country=' + encodeURIComponent(payload.country || '') +
      '&condition=' + encodeURIComponent(payload.condition || '');

    return new Promise(function (resolve, reject) {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', GAS_URL + '?' + qs);
      xhr.setRequestHeader('Content-Type', 'text/plain;charset=utf-8');
      xhr.onload = function () {
        if (xhr.status < 200 || xhr.status >= 300) {
          reject(new Error('Sheet HTTP ' + xhr.status));
          return;
        }
        var data;
        try {
          data = JSON.parse(xhr.responseText);
        } catch (err) {
          reject(new Error('Invalid sheet response'));
          return;
        }
        if (!data || data.success !== true) {
          reject(new Error('Sheet did not confirm save'));
          return;
        }
        resolve(data);
      };
      xhr.onerror = function () {
        reject(new Error('Sheet network error'));
      };
      xhr.send(body);
    });
  }

  /* After Sheets success, before WhatsApp: fire generate_lead for GTM + GA4 → Google Ads */
  function fireGenerateLeadThenOpenWhatsApp(form, whatsappUrl) {
    var formId = (form && form.id) || '';
    var payload = {
      form_id: formId,
      lead_source: 'website_form'
    };

    window.dataLayer = window.dataLayer || [];
    /* Custom event for GTM triggers */
    window.dataLayer.push({
      event: 'generate_lead',
      form_id: formId,
      lead_source: 'website_form'
    });

    /* Ensure gtag exists — GTM can load gtag.js without exposing window.gtag */
    if (typeof window.gtag !== 'function') {
      window.gtag = function () {
        window.dataLayer.push(arguments);
      };
    }

    /* GA4 event (imported in Google Ads as Primary: tabeebway.com generate_lead) */
    window.gtag('event', 'generate_lead', Object.assign({
      send_to: 'G-5TBH8QQ2EQ'
    }, payload));

    return new Promise(function (resolve) {
      setTimeout(resolve, 500);
    }).then(function () {
      window.open(whatsappUrl, '_blank');
    });
  }

  function wireLeadForm(formId, onSuccess) {
    var form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearFormError(form);

      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      var payload = getLeadPayload(form);
      var whatsappUrl = buildWhatsAppUrl(payload);
      setSubmitting(form, true);

      submitLeadToSheet(payload)
        .then(function () {
          return fireGenerateLeadThenOpenWhatsApp(form, whatsappUrl);
        })
        .then(function () {
          if (typeof onSuccess === 'function') onSuccess(form);
        })
        .catch(function () {
          showFormError(form, 'Something went wrong. Please try again.');
        })
        .then(function () {
          setSubmitting(form, false);
        });
    });
  }

  wireLeadForm('hero-form', function (form) {
    form.classList.add('hidden');
    var success = document.getElementById('hero-form-success');
    if (success) success.classList.remove('hidden');
  });

  wireLeadForm('hero-form-mobile', function (form) {
    form.classList.add('hidden');
    var success = document.getElementById('hero-form-mobile-success');
    if (success) success.classList.remove('hidden');
  });

  wireLeadForm('enquiry-form', function () {
    var panel = document.getElementById('form-panel');
    var success = document.getElementById('form-success');
    if (panel) panel.classList.add('hidden');
    if (success) success.classList.remove('hidden');
  });
})();

/* Clean section URLs: /treatment → scroll to #treatment (no hash in address bar) */
(function () {
  var SECTION_PATHS = {
    treatment: 'treatment',
    doctors: 'doctors',
    testimonials: 'testimonials',
    'about-us': 'about-us',
    faq: 'faq',
    contact: 'contact',
    'book-appointment': 'book-appointment',
    'visa-travel': 'how-it-works',
    'how-it-works': 'how-it-works'
  };

  function pathToSection(pathname) {
    var seg = (pathname || '').replace(/\/+$/, '').split('/').pop() || '';
    return SECTION_PATHS[seg] || null;
  }

  function scrollToId(id, behavior) {
    var el = document.getElementById(id);
    if (!el) return false;
    el.scrollIntoView({ behavior: behavior || 'smooth', block: 'start' });
    return true;
  }

  function goSection(id, push) {
    if (!scrollToId(id, 'smooth')) return;
    if (push && window.history && history.pushState) {
      history.pushState({ section: id }, '', '/' + id);
    }
  }

  /* Legacy /#treatment → /treatment */
  if (location.hash && location.hash.length > 1) {
    var hashId = location.hash.slice(1);
    if (SECTION_PATHS[hashId] && document.getElementById(hashId)) {
      if (history.replaceState) {
        history.replaceState({ section: hashId }, '', '/' + hashId);
      }
      setTimeout(function () { scrollToId(hashId, 'auto'); }, 0);
    }
  } else {
    var fromPath = pathToSection(location.pathname);
    if (fromPath) {
      setTimeout(function () { scrollToId(fromPath, 'auto'); }, 50);
    }
  }

  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href');
    if (!href) return;

    var id = null;
    if (href.charAt(0) === '/' && href.indexOf('#') === -1 && href.indexOf('?') === -1) {
      id = pathToSection(href);
    }
    if (!id) return;
    if (!document.getElementById(id)) return;

    e.preventDefault();
    goSection(id, true);
  });

  window.addEventListener('popstate', function () {
    var id = pathToSection(location.pathname);
    if (id) scrollToId(id, 'smooth');
    else if (location.pathname === '/' || location.pathname === '') {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  });
})();
