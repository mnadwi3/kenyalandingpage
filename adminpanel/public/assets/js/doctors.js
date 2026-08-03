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

  document.querySelectorAll('[data-tabs]').forEach((tabs) => {
    const buttons = tabs.querySelectorAll('[data-tab]');
    const panels = tabs.querySelectorAll('[data-tab-panel]');
    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        const target = button.getAttribute('data-tab');
        buttons.forEach((b) => b.classList.toggle('is-active', b === button));
        panels.forEach((panel) => panel.classList.toggle('is-active', panel.getAttribute('data-tab-panel') === target));
      });
    });
  });

  document.querySelectorAll('[data-multi-select]').forEach((wrap) => {
    const search = wrap.querySelector('[data-multi-search]');
    if (!search) return;
    search.addEventListener('input', () => {
      const q = search.value.trim().toLowerCase();
      wrap.querySelectorAll('.multi-select-option').forEach((option) => {
        const label = option.getAttribute('data-label') || '';
        option.classList.toggle('is-hidden', q !== '' && !label.includes(q));
      });
    });
  });

  const form = document.querySelector('[data-doctor-form]');
  if (!form) return;
  const source = form.querySelector('[data-slug-source]');
  const target = form.querySelector('[data-slug-target]');
  let slugTouched = !!(target && target.value);
  target?.addEventListener('input', () => { slugTouched = true; });
  const slugify = (value) => value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  source?.addEventListener('input', () => {
    if (!slugTouched && target) target.value = slugify(source.value);
    const previewName = document.querySelector('[data-preview-name]');
    if (previewName) previewName.textContent = source.value || 'Doctor name';
  });
  form.querySelector('[name="qualification"]')?.addEventListener('input', (event) => {
    const preview = document.querySelector('[data-preview-qualification]');
    if (preview) preview.textContent = event.target.value || 'Qualification';
  });
  form.querySelector('[name="expertise"]')?.addEventListener('input', (event) => {
    const preview = document.querySelector('[data-preview-expertise]');
    if (preview) preview.textContent = event.target.value || 'Expertise preview will appear here.';
  });
  target?.addEventListener('input', () => {
    const preview = document.querySelector('[data-preview-slug]');
    if (preview) preview.textContent = target.value || 'slug';
  });
})();
