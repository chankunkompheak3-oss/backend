(function () {
  const viewModal = new bootstrap.Modal('#viewModal');
  new CrudPage({
    endpoint: 'customers.php', idKey: 'customer_id', title: 'Customer',
    tbody: '#tbl tbody', pager: '#pager', modal: '#formModal', addBtn: '#addBtn', filters: '[data-filter]',
    nameOf: c => c.full_name,
    row: c => '<tr><td>' + c.customer_id + '</td><td><strong>' + esc(c.full_name) + '</strong></td><td>' + esc(c.phone || '-') + '</td><td>' + esc(c.email || '-') +
      '</td><td>' + esc(c.license_number || '-') + (c.license_expiry ? '<br><small class="text-secondary">exp ' + esc(fmtDate(c.license_expiry)) + '</small>' : '') +
      '</td><td>' + c.reservation_count + '</td><td>' + badge(c.status) + (c.is_blacklisted == 1 ? ' ' + badge('Blacklisted') : '') +
      '</td><td class="text-end">' + actionBtns(c.customer_id, { view: true, edit: true, del: APP.isAdmin }) + '</td></tr>',
    actions: { view: async id => {
      try {
        const { data: c } = await api('customers.php?id=' + id);
        const row = (l, v) => '<div class="col-md-4"><small class="text-secondary d-block">' + l + '</small>' + esc(v || '-') + '</div>';
        const hist = c.reservations.length
          ? '<table class="table table-sm"><thead><tr><th>ID</th><th>Vehicle</th><th>Pickup</th><th>Return</th><th>Total</th><th>Status</th></tr></thead><tbody>' +
            c.reservations.map(r => '<tr><td>#R' + String(r.reservation_id).padStart(5, '0') + '</td><td>' + esc(r.vehicle) + '</td><td>' + esc(fmtDateTime(r.pickup_datetime)) + '</td><td>' + esc(fmtDateTime(r.return_datetime)) + '</td><td>' + esc(money(r.estimated_total)) + '</td><td>' + badge(r.status) + '</td></tr>').join('') + '</tbody></table>'
          : '<p class="text-secondary">No reservations yet.</p>';
        document.getElementById('viewBody').innerHTML = '<div class="row g-3 mb-3">' + row('Name', c.full_name) + row('Gender', c.gender) + row('Phone', c.phone) + row('Email', c.email) +
          row('License', c.license_number) + row('License expiry', c.license_expiry ? fmtDate(c.license_expiry) : '') + row('Address', c.address) +
          '<div class="col-md-4"><small class="text-secondary d-block">Status</small>' + badge(c.status) + (c.is_blacklisted == 1 ? ' ' + badge('Blacklisted') : '') + '</div>' +
          (c.is_blacklisted == 1 ? row('Blacklist reason', c.blacklist_reason) : '') + '</div><h6>Reservation history</h6>' + hist;
        viewModal.show();
      } catch (e) { toast(e.message, 'error'); }
    } },
  });
})();