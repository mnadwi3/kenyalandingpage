/* ============================================
   VaidTrack.com - Mobile hamburger menu toggle
   ============================================ */
(function () {
  var toggle = document.getElementById('nav-toggle');
  var menu = document.getElementById('mobile-nav');
  if (!toggle || !menu) return;

  function isOpen() {
    return menu.classList.contains('is-open');
  }

  function closeMenu() {
    menu.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
  }

  function openMenu() {
    menu.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
  }

  toggle.addEventListener('click', function () {
    if (isOpen()) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  menu.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeMenu();
  });

  document.addEventListener('click', function (event) {
    if (isOpen() && !menu.contains(event.target) && !toggle.contains(event.target)) {
      closeMenu();
    }
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth >= 768 && isOpen()) closeMenu();
  });
})();
