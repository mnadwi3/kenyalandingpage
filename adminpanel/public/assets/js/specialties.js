(() => {
  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('click', (event) => {
      const message = el.getAttribute('data-confirm');
      if (message && !window.confirm(message)) event.preventDefault();
    });
  });

  const selectAll = document.querySelector('[data-select-all]');
  if (selectAll) {
    selectAll.addEventListener('change', () => {
      document.querySelectorAll('[data-row-check]').forEach((box) => { box.checked = selectAll.checked; });
    });
  }

  const form = document.querySelector('[data-specialty-form]');
  if (!form) return;
  const source = form.querySelector('[data-slug-source]');
  const target = form.querySelector('[data-slug-target]');
  let slugTouched = !!(target && target.value);
  target?.addEventListener('input', () => { slugTouched = true; });
  const slugify = (value) => value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  source?.addEventListener('input', () => {
    if (!slugTouched && target) target.value = slugify(source.value);
  });
})();
