(function () {
  const ref = id => '#R' + String(id).padStart(5, '0');
  let options = null;           // customers + vehicles for the form selects
  let crud;
  const viewModal = new bootstrap.Modal('#viewModal');
  const form = document.querySelector('#formModal form');

  async function loadOptions() {
    if (options) return;
    options = (await api('reservations.php?action=options')).data;
    document.getElementById('f_customer_id').insertAdjacentHTML('beforeend', options.customers.map(c => '<option value="' + c.id + '">' + esc(c.label) + '</option>').join(''));
    document.getElementById('f_vehicle_id').insertAdjacentHTML('beforeend', options.vehicles.map(v => '<option value="' + v.id + '">' + esc(v.label) + ' - ' + esc(money(v.daily_price)) + '/day</option>').join(''));
  }

  // live price + availability preview
  const quote = debounce(async () => {
    const box = document.getElementById('quoteBox');
    const p = new URLSearchParams({ action: 'quote', exclude_id: crud.editId || '' });
    ['vehicle_id', 'pickup_datetime', 'return_datetime', 'discount_amount', 'extra_fees'].forEach(n => p.set(n, form.elements[n].value));
    try {
      const { data: q } = await api('reservations.php?' + p);
      if (!q.ready) { box.className = 'quote-box'; box.textContent = 'Choose a vehicle and dates to see the price and availability.'; return; }
      box.className = 'quote-box ' + (q.conflict ? 'text-danger' : '');
      box.innerHTML = (q.conflict ? '<strong>Not available:</strong> ' + esc(q.conflict) + '<br>' : '<strong class="text-success">Available.</strong><br>') +
        esc(q.days + ' day(s) x ' + money(q.daily_rate) + ' = ' + money(q.subtotal)) +
        (q.discount ? ' &minus; discount ' + esc(money(q.discount)) : '') + (q.tax ? ' + tax ' + esc(money(q.tax)) + ' (' + q.tax_rate + '%)' : '') +
        (q.extra_fees ? ' + fees ' + esc(money(q.extra_fees)) : '') + '<br><strong>Estimated total: ' + esc(money(q.total)) + '</strong>';
    } catch (e) { box.textContent = e.message; }
  }, 400);
  ['vehicle_id', 'pickup_datetime', 'return_datetime', 'discount_amount', 'extra_fees'].forEach(n => { form.elements[n].addEventListener('input', quote); form.elements[n].addEventListener('change', quote); });

  crud = new CrudPage({
    endpoint: 'reservations.php', idKey: 'reservation_id', title: 'Reservation',
    tbody: '#tbl tbody', pager: '#pager', modal: '#formModal', addBtn: '#addBtn', filters: '[data-filter]',
    nameOf: r => 'reservation ' + ref(r.reservation_id),
    beforeOpen: loadOptions,
    onOpen: r => {
      if (r) {   // make sure an inactive customer / hidden vehicle still shows in the select
        const addIfMissing = (name, id, label) => { const s = form.elements[name]; if (![...s.options].some(o => o.value == id)) s.insertAdjacentHTML('beforeend', '<option value="' + id + '">' + esc(label) + '</option>'); s.value = id; };
        addIfMissing('customer_id', r.customer_id, r.customer_name);
        addIfMissing('vehicle_id', r.vehicle_id, r.brand + ' ' + r.model + ' - ' + r.license_plate);
      }
      quote();
    },
    row: r => '<tr><td>' + ref(r.reservation_id) + '</td><td><strong>' + esc(r.customer_name) + '</strong><br><small class="text-secondary">' + esc(r.customer_phone || '') + '</small></td>' +
      '<td>' + esc(r.brand + ' ' + r.model) + '<br><small class="text-secondary">' + esc(r.license_plate) + '</small></td><td>' + esc(fmtDateTime(r.pickup_datetime)) + '</td><td>' + esc(fmtDateTime(r.return_datetime)) +
      '</td><td>' + esc(money(r.estimated_total)) + '</td><td>' + badge(r.payment_status) + '</td><td>' + badge(r.status) + '</td><td class="text-end">' +
      actionBtns(r.reservation_id, { view: true, edit: ['Pending', 'Approved'].includes(r.status), del: APP.isAdmin && ['Cancelled', 'Rejected'].includes(r.status) }) + '</td></tr>',
    actions: { view: id => showDetails(id) },
  });

  // ---------- details modal ----------
  async function showDetails(id) {
    try {
      const { data: r } = await api('reservations.php?id=' + id);
      const row = (l, v) => '<div class="col-md-4"><small class="text-secondary d-block">' + l + '</small>' + esc(v || '-') + '</div>';
      const canPay = !['Rejected'].includes(r.status);
      const acts = { Pending: [['Approved', 'success', 'Approve'], ['Rejected', 'warning', 'Reject'], ['Cancelled', 'danger', 'Cancel']], Approved: [['Completed', 'primary', 'Mark completed'], ['Cancelled', 'danger', 'Cancel']] }[r.status] || [];
      document.getElementById('viewBody').innerHTML =
        '<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">' + ref(r.reservation_id) + '</h5><div>' + badge(r.status) + ' ' + badge(r.payment_status) + '</div></div>' +
        '<div class="row g-3 mb-3">' + row('Customer', r.customer_name + ' (' + (r.customer_phone || '-') + ')') + row('Vehicle', r.brand + ' ' + r.model + ' - ' + r.license_plate) + row('Daily rate', money(r.daily_rate)) +
        row('Pickup', fmtDateTime(r.pickup_datetime)) + row('Return', fmtDateTime(r.return_datetime)) + row('Locations', (r.pickup_location || '-') + ' -> ' + (r.return_location || '-')) +
        row('Discount', money(r.discount_amount)) + row('Additional fees', money(r.extra_fees)) + row('Estimated total', money(r.estimated_total)) +
        row('Paid', money(r.paid_amount)) + row('Balance', money(r.balance)) + row('Deposit held', money(r.deposit_paid)) +
        (r.rejection_reason ? row('Rejection reason', r.rejection_reason) : '') + (r.note ? row('Note', r.note) : '') + '</div>' +
        (acts.length ? '<div class="border rounded p-3 mb-3"><div class="row g-2 align-items-center"><div class="col-md-5"><input class="form-control form-control-sm" id="reasonInput" maxlength="255" placeholder="Reason (required to reject)"></div><div class="col-md-7">' +
          acts.map(a => '<button class="btn btn-sm btn-' + a[1] + ' me-1" data-status="' + a[0] + '">' + a[2] + '</button>').join('') + '</div></div></div>' : '') +
        '<h6>Payments</h6>' + (r.payments.length
          ? '<table class="table table-sm"><thead><tr><th>Invoice</th><th>Type</th><th>Amount</th><th>Method</th><th>Date</th><th>By</th></tr></thead><tbody>' +
            r.payments.map(p => '<tr><td>' + esc(p.invoice_number) + '</td><td>' + esc(p.payment_type) + '</td><td>' + esc(money(p.amount)) + '</td><td>' + esc(p.payment_method || '-') + '</td><td>' + esc(fmtDateTime(p.payment_date)) + '</td><td>' + esc(p.received_by_name || '-') + '</td></tr>').join('') + '</tbody></table>'
          : '<p class="text-secondary">No payments recorded.</p>') +
        (canPay ? '<form id="payForm" class="row g-2 align-items-end" novalidate><div class="col-md-3"><label class="form-label small">Type</label><select class="form-select form-select-sm" name="payment_type"><option>Rental</option><option>Deposit</option><option>Refund</option></select></div>' +
          '<div class="col-md-2"><label class="form-label small">Amount</label><input class="form-control form-control-sm" name="amount" type="number" min="0.01" step="0.01" value="' + (r.balance || '') + '" required></div>' +
          '<div class="col-md-3"><label class="form-label small">Method</label><select class="form-select form-select-sm" name="payment_method"><option>Cash</option><option>Card</option><option>Bank Transfer</option></select></div>' +
          '<div class="col-md-2"><label class="form-label small">Note</label><input class="form-control form-control-sm" name="note" maxlength="255"></div>' +
          '<div class="col-md-2"><button class="btn btn-sm btn-primary w-100" type="submit">Add payment</button></div></form>' : '');

      const body = document.getElementById('viewBody');
      body.querySelectorAll('[data-status]').forEach(b => b.onclick = async () => {
        const status = b.dataset.status, reason = document.getElementById('reasonInput')?.value.trim();
        if (['Cancelled', 'Completed'].includes(status) && !await confirmDialog('Mark reservation ' + ref(id) + ' as ' + status + '?', 'Yes, continue', status === 'Cancelled' ? 'danger' : 'primary')) return;
        try { const j = await api('reservations.php?id=' + id + '&action=status', { method: 'PUT', body: { status, reason } }); toast(j.message); showDetails(id); crud.load(); }
        catch (e) { toast(e.message, 'error'); }
      });
      const pf = document.getElementById('payForm');
      if (pf) pf.onsubmit = async e => {
        e.preventDefault(); clearErrors(pf);
        const bodyObj = Object.fromEntries(new FormData(pf));
        try { const j = await api('reservations.php?id=' + id + '&action=payment', { method: 'POST', body: bodyObj }); toast(j.message); showDetails(id); crud.load(); }
        catch (err) { showErrors(pf, err.errors); toast(err.message, 'error'); }
      };
      viewModal.show();
    } catch (e) { toast(e.message, 'error'); }
  }
})();