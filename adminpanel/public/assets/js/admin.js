(() => {
  const root = document.body;
  const stored = localStorage.getItem('vaidtrack-theme');

  if (stored === 'dark' || stored === 'light') {
    root.setAttribute('data-theme', stored);
  }

  const toastStack = () => document.getElementById('toast-stack') || document.body;

  window.VaidTrackToast = function showToast(message, type = 'info') {
    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.setAttribute('role', 'status');
    el.textContent = message;
    toastStack().prepend(el);
    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transition = 'opacity 0.3s ease';
      setTimeout(() => el.remove(), 300);
    }, 4200);
  };

  document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      localStorage.setItem('vaidtrack-theme', next);
    });
  });

  document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      root.classList.toggle('sidebar-open');
    });
  });

  document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
    const trigger = dropdown.querySelector('[data-dropdown-trigger]');
    const menu = dropdown.querySelector('.dropdown-menu');
    if (!trigger || !menu) return;

    trigger.addEventListener('click', (event) => {
      event.stopPropagation();
      const open = menu.hasAttribute('hidden');
      document.querySelectorAll('.dropdown-menu').forEach((other) => other.setAttribute('hidden', ''));
      document.querySelectorAll('[data-dropdown-trigger]').forEach((t) => t.setAttribute('aria-expanded', 'false'));
      if (open) {
        menu.removeAttribute('hidden');
        trigger.setAttribute('aria-expanded', 'true');
      }
    });
  });

  document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu').forEach((menu) => menu.setAttribute('hidden', ''));
    document.querySelectorAll('[data-dropdown-trigger]').forEach((t) => t.setAttribute('aria-expanded', 'false'));
  });

  document.querySelectorAll('[data-toast-message]').forEach((el) => {
    el.addEventListener('click', (event) => {
      event.preventDefault();
      window.VaidTrackToast(el.getAttribute('data-toast-message') || '', el.getAttribute('data-toast-type') || 'info');
    });
  });

  const search = document.querySelector('[data-global-search]');
  if (search) {
    search.closest('form')?.addEventListener('submit', (event) => {
      event.preventDefault();
      const q = search.value.trim();
      if (!q) {
        window.VaidTrackToast('Enter a search term.', 'info');
        return;
      }
      window.VaidTrackToast('Global search unlocks when content modules are available.', 'info');
    });
  }

  document.querySelectorAll('.toast').forEach((toast) => {
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 4500);
  });
})();
