/* ============================================
   Apollo Kenya — Main JavaScript
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

/* Move Cancer Specialists section after Treatments */
(function () {
  var doctors = document.getElementById('doctors');
  var cancers = document.getElementById('cancers');
  if (!doctors || !cancers) return;
  cancers.insertAdjacentElement('afterend', doctors);
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

/* Mobile nav */
(function () {
  var toggle = document.getElementById('nav-toggle');
  var menu = document.getElementById('mobile-nav');
  if (!toggle || !menu) return;
  var iconOpen = toggle.querySelector('.nav-icon-open');
  var iconClose = toggle.querySelector('.nav-icon-close');

  function setOpen(open) {
    menu.classList.toggle('is-open', open);
    document.body.classList.toggle('nav-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    if (iconOpen) iconOpen.classList.toggle('hidden', open);
    if (iconClose) iconClose.classList.toggle('hidden', !open);
  }

  toggle.addEventListener('click', function () {
    setOpen(!menu.classList.contains('is-open'));
  });
  menu.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () { setOpen(false); });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') setOpen(false);
  });
  window.addEventListener('resize', function () {
    if (window.innerWidth >= 768) setOpen(false);
  });
})();

/* Forms → Google Sheet + WhatsApp with prefilled details */
(function () {
  var WA_NUMBER = '918979983149';
  var GAS_URL = 'https://script.google.com/macros/s/AKfycbzTo3v4gxY1FmMrSFndbYSDaWRhjL58uUzkicjfDW7xS1xdoL_Rxsq69juo2PcT3g2q_Q/exec';

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
      whatsapp: field(form, ['whatsapp', 'phone', 'email']),
      country: field(form, ['country']),
      condition: field(form, ['condition', 'treatment'])
    };
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

  function pushGenerateLead() {
    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ event: 'generate_lead' });
    } catch (err) {
      console.error('generate_lead push failed', err);
    }
  }

  function openWhatsAppFromForm(form, payload) {
    var lines = [
      'Hi, I want to book an appointment for cancer treatment in India.',
      '',
      'Name: ' + (payload.name || '-'),
      'Country: ' + (payload.country || '-'),
      'WhatsApp/Email: ' + (payload.whatsapp || '-'),
      'Condition/Treatment: ' + (payload.condition || '-')
    ];
    var url = 'https://wa.me/' + WA_NUMBER + '?text=' + encodeURIComponent(lines.join('\n'));
    window.open(url, '_blank', 'noopener,noreferrer');
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

  function wireWhatsAppForm(formId, onSuccess) {
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
      setSubmitting(form, true);

      submitLeadToSheet(payload)
        .then(function () {
          pushGenerateLead();
          openWhatsAppFromForm(form, payload);
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

  wireWhatsAppForm('hero-form', function (form) {
    form.classList.add('hidden');
    var success = document.getElementById('hero-form-success');
    if (success) success.classList.remove('hidden');
  });

  wireWhatsAppForm('hero-form-mobile', function (form) {
    form.classList.add('hidden');
    var success = document.getElementById('hero-form-mobile-success');
    if (success) success.classList.remove('hidden');
  });

  wireWhatsAppForm('enquiry-form', function () {
    var panel = document.getElementById('form-panel');
    var success = document.getElementById('form-success');
    if (panel) panel.classList.add('hidden');
    if (success) success.classList.remove('hidden');
  });
})();
