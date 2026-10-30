(async function () {
  const CARDS = [
    ['Total Vehicles', 'total_vehicles', 'fa-car', 'blue'],
    ['Available Vehicles', 'available_vehicles', 'fa-circle-check', 'green'],
    ['Rented Vehicles', 'rented_vehicles', 'fa-key', 'orange'],
    ['Under Maintenance', 'maintenance_vehicles', 'fa-screwdriver-wrench', 'red'],
    ['Total Customers', 'total_customers', 'fa-users', 'purple'],
    ['Total Reservations', 'total_reservations', 'fa-calendar-days', 'teal'],
    ['Active Reservations', 'active_reservations', 'fa-calendar-check', 'green'],
    ['Total Revenue', 'total_revenue', 'fa-dollar-sign', 'blue', true],
  ];
  try {
    const { data } = await api('dashboard.php');

    document.getElementById('statCards').innerHTML = CARDS.map(([label, key, icon, color, isMoney]) =>
      '<div class="stat-card"><div class="stat-top"><div><p>' + esc(label) + '</p><h3>' +
      (isMoney ? esc(money(data.stats[key])) : esc(data.stats[key])) +
      '</h3></div><div class="stat-icon icon-' + color + '"><i class="fa-solid ' + icon + '"></i></div></div></div>').join('');

    document.getElementById('recentRes').innerHTML = data.recent_reservations.length
      ? data.recent_reservations.map(r => '<tr><td>#R' + String(r.reservation_id).padStart(5, '0') + '</td><td><span class="customer-name">' + esc(r.customer_name) +
          '</span></td><td>' + esc(r.vehicle) + '</td><td>' + esc(fmtDate(r.pickup_datetime)) + '</td><td>' + esc(money(r.estimated_total)) + '</td><td>' + badge(r.status) + '</td></tr>').join('')
      : '<tr><td colspan="6" class="text-center text-secondary py-3">No reservations yet.</td></tr>';

    document.getElementById('recentVeh').innerHTML = data.recent_vehicles.length
      ? data.recent_vehicles.map(v => '<tr><td><img class="thumb" src="' + esc(v.image_url) + '" alt=""></td><td>' + esc(v.brand + ' ' + v.model + (v.year ? ' (' + v.year + ')' : '')) +
          '</td><td>' + esc(v.license_plate) + '</td><td>' + esc(money(v.daily_price)) + '</td><td>' + badge(v.status) + '</td></tr>').join('')
      : '<tr><td colspan="5" class="text-center text-secondary py-3">No vehicles yet.</td></tr>';

    const m = data.monthly;
    new Chart(document.getElementById('chResv'), { type: 'bar', data: { labels: m.labels, datasets: [{ label: 'Reservations', data: m.reservations, backgroundColor: '#2563eb' }] }, options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
    new Chart(document.getElementById('chRev'), { type: 'line', data: { labels: m.labels, datasets: [{ label: 'Revenue', data: m.revenue, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.15)', fill: true, tension: .3 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
    new Chart(document.getElementById('chFleet'), { type: 'doughnut', data: { labels: data.vehicle_status.map(s => s.status), datasets: [{ data: data.vehicle_status.map(s => +s.total), backgroundColor: ['#16a34a', '#f59e0b', '#2563eb', '#ea580c', '#dc2626', '#64748b'] }] }, options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } } } });
  } catch (e) {
    document.getElementById('statCards').innerHTML = '<div class="alert alert-danger w-100">' + esc(e.message) + '</div>';
  }
})();