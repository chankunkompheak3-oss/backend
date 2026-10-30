(function () {
  const BASE = document.querySelector('meta[name="base-url"]').content;
  const $ = id => document.getElementById(id);
  const today = new Date(), ago = new Date(Date.now() - 29 * 864e5);
  const iso = d => d.toISOString().slice(0, 10);
  $('from').value = iso(ago); $('to').value = iso(today);
  let section = 'reservations', chart = null;

  const range = () => 'from=' + encodeURIComponent($('from').value) + '&to=' + encodeURIComponent($('to').value);

  function cell(c, v) {
    if (v === null || v === undefined || v === '') return '-';
    switch (c.type) {
      case 'money': return esc(money(v));
      case 'int': return esc(Number(v).toLocaleString());
      case 'percent': return esc(v + ' %');
      case 'date': return esc(fmtDate(v));
      case 'datetime': return esc(fmtDateTime(v));
      case 'badge': return badge(v);
      default: return esc(v);
    }
  }

  async function loadSummary() {
    const { data } = await api('reports.php?section=summary&' + range());
    $('repCards').innerHTML = data.rows.map((r, i) =>
      '<div class="stat-card"><div class="stat-top"><div><p>' + esc(r.label) + '</p><h3>' + (r.type === 'money' ? esc(money(r.value)) : esc(Number(r.value).toLocaleString())) +
      '</h3></div><div class="stat-icon icon-' + ['blue', 'green', 'red', 'orange', 'teal', 'purple', 'gray'][i % 7] + '"><i class="fa-solid ' + (r.type === 'money' ? 'fa-dollar-sign' : 'fa-hashtag') + '"></i></div></div></div>').join('');
  }

  async function loadChart() {
    const { data } = await api('reports.php?section=revenue&' + range());
    if (chart) chart.destroy();
    chart = new Chart($('chRevenue'), { type: 'line', data: { labels: data.rows.map(r => fmtDate(r.day)), datasets: [{ label: 'Net revenue', data: data.rows.map(r => +r.revenue), borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.12)', fill: true, tension: .25 }] },
      options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
  }

  async function loadSection() {
    $('repBody').innerHTML = '<tr><td class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Loading...</td></tr>';
    try {
      const { data } = await api('reports.php?section=' + section + '&' + range());
      $('secTitle').textContent = data.title;
      $('repHead').innerHTML = '<tr>' + data.columns.map(c => '<th>' + esc(c.label) + '</th>').join('') + '</tr>';
      $('repBody').innerHTML = data.rows.length
        ? data.rows.map(r => '<tr>' + data.columns.map(c => '<td>' + cell(c, r[c.key]) + '</td>').join('') + '</tr>').join('')
        : '<tr><td colspan="' + data.columns.length + '" class="text-center text-secondary py-4">No data for this period.</td></tr>';
      $('csvBtn').href = BASE + '/api/reports.php?section=' + section + '&' + range() + '&format=csv';
    } catch (e) { $('repBody').innerHTML = '<tr><td class="text-danger py-3">' + esc(e.message) + '</td></tr>'; }
  }

  async function refresh() {
    if ($('from').value > $('to').value) { toast('The "from" date must be before the "to" date.', 'error'); return; }
    try { await Promise.all([loadSummary(), loadChart(), loadSection()]); } catch (e) { toast(e.message, 'error'); }
  }

  $('apply').onclick = refresh;
  $('tabs').onclick = e => {
    const b = e.target.closest('[data-sec]'); if (!b) return;
    section = b.dataset.sec;
    $('tabs').querySelectorAll('button').forEach(x => { x.classList.toggle('btn-primary', x === b); x.classList.toggle('btn-outline-primary', x !== b); });
    loadSection();
  };
  refresh();
})();