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

/* Forms → WhatsApp with prefilled details */
(function () {
  var WA_NUMBER = '918979983149';

  function field(form, names) {
    for (var i = 0; i < names.length; i++) {
      var el = form.elements.namedItem(names[i]);
      if (el && el.value) return String(el.value).trim();
    }
    return '';
  }

  function openWhatsAppFromForm(form) {
    var name = field(form, ['name']);
    var country = field(form, ['country']);
    var email = field(form, ['email']);
    var treatment = field(form, ['treatment']);

    var lines = [
      'Hi, I want to book an appointment for cancer treatment in India.',
      '',
      'Name: ' + (name || '-'),
      'Country: ' + (country || '-'),
      'Email: ' + (email || '-'),
      'Treatment: ' + (treatment || '-')
    ];

    var url = 'https://wa.me/' + WA_NUMBER + '?text=' + encodeURIComponent(lines.join('\n'));

    /* GTM: fire generate_lead once per successful form submit, before WhatsApp opens */
    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: 'generate_lead',
        form_id: (form && form.id) ? form.id : '',
        lead_source: 'enquiry_form'
      });
      console.log('generate_lead pushed');
      console.log(window.dataLayer);
    } catch (err) {
      console.error('generate_lead push failed', err);
    }

    window.open(url, '_blank', 'noopener,noreferrer');
  }

  function wireWhatsAppForm(formId, onSuccess) {
    var form = document.getElementById(formId);
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }
      openWhatsAppFromForm(form);
      if (typeof onSuccess === 'function') onSuccess(form);
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
