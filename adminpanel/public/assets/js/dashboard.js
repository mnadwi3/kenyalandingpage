(() => {
  const dashboard = document.querySelector('[data-dashboard]');
  if (!dashboard) return;

  const skeleton = dashboard.querySelector('[data-dashboard-skeleton]');
  const content = dashboard.querySelector('[data-dashboard-content]');

  const reveal = () => {
    skeleton?.classList.add('is-hidden');
    content?.classList.remove('is-hidden');
    dashboard.setAttribute('data-loading', 'false');
  };

  // Brief skeleton for perceived polish; content is already server-rendered.
  window.setTimeout(reveal, 350);

  const canvas = document.getElementById('overviewChart');
  if (!canvas || !canvas.getContext) return;

  let payload = { labels: [], values: [], placeholder: true };
  try {
    payload = JSON.parse(canvas.getAttribute('data-chart') || '{}');
  } catch (e) {
    // Keep defaults.
  }

  const labels = payload.labels || [];
  const values = payload.values || [];
  const ctx = canvas.getContext('2d');

  const draw = () => {
    const dpr = window.devicePixelRatio || 1;
    const width = canvas.clientWidth || 640;
    const height = 260;
    canvas.width = width * dpr;
    canvas.height = height * dpr;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, width, height);

    const theme = document.body.getAttribute('data-theme') === 'dark';
    const grid = theme ? '#243044' : '#e2e8f0';
    const text = theme ? '#94a3b8' : '#64748b';
    const bar = payload.placeholder ? (theme ? '#334155' : '#94a3b8') : '#0f4c81';

    const pad = { top: 20, right: 16, bottom: 36, left: 36 };
    const chartW = width - pad.left - pad.right;
    const chartH = height - pad.top - pad.bottom;
    const max = Math.max(1, ...values);

    ctx.strokeStyle = grid;
    ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i += 1) {
      const y = pad.top + (chartH / 4) * i;
      ctx.beginPath();
      ctx.moveTo(pad.left, y);
      ctx.lineTo(width - pad.right, y);
      ctx.stroke();
    }

    const gap = 12;
    const barW = labels.length ? (chartW - gap * (labels.length - 1)) / labels.length : 0;

    values.forEach((value, index) => {
      const h = (value / max) * chartH;
      const x = pad.left + index * (barW + gap);
      const y = pad.top + chartH - h;
      const radius = 8;

      ctx.fillStyle = bar;
      ctx.beginPath();
      ctx.moveTo(x, y + radius);
      ctx.arcTo(x, y, x + barW, y, radius);
      ctx.arcTo(x + barW, y, x + barW, y + h, radius);
      ctx.lineTo(x + barW, y + h);
      ctx.lineTo(x, y + h);
      ctx.closePath();
      ctx.fill();

      ctx.fillStyle = text;
      ctx.font = '12px DM Sans, sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(labels[index] || '', x + barW / 2, height - 12);
    });
  };

  draw();
  window.addEventListener('resize', draw);

  document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => setTimeout(draw, 0));
  });
})();
