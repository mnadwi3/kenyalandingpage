/* ============================================
   VaidTrack.com - Mobile hamburger menu toggle
   ============================================ */
(function () {
  var toggle = document.getElementById('nav-toggle');
  var menu = document.getElementById('mobile-nav');
  if (!toggle || !menu) return;

  function closeMenu() {
    menu.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
  }

  function openMenu() {
    menu.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
  }

  toggle.addEventListener('click', function () {
    if (menu.hidden) {
      openMenu();
    } else {
      closeMenu();
    }
  });

  menu.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeMenu();
  });

  document.addEventListener('click', function (event) {
    if (!menu.hidden && !menu.contains(event.target) && !toggle.contains(event.target)) {
      closeMenu();
    }
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth >= 768 && !menu.hidden) closeMenu();
  });
})();
