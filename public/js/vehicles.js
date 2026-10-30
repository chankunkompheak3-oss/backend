(async function () {
  // fill category dropdowns (filter + form)
  try {
    const { data } = await api('categories.php?all=1');
    const opts = data.map(c => '<option value="' + c.category_id + '">' + esc(c.category_name) + '</option>').join('');
    document.getElementById('catFilter').insertAdjacentHTML('beforeend', opts);
    document.getElementById('f_category_id').insertAdjacentHTML('beforeend', opts);
  } catch (e) { toast(e.message, 'error'); }

  const viewModal = new bootstrap.Modal('#viewModal');

  const page = new CrudPage({
    endpoint: 'vehicles.php', idKey: 'vehicle_id', title: 'Vehicle', multipart: true,
    tbody: '#tbl tbody', pager: '#pager', modal: '#formModal', addBtn: '#addBtn', filters: '[data-filter]',
    nameOf: v => v.brand + ' ' + v.model + ' (' + v.license_plate + ')',
    row: v => '<tr><td><img class="thumb" src="' + esc(v.image_url) + '" alt=""></td>' +
      '<td><strong>' + esc(v.brand + ' ' + v.model) + '</strong><br><small class="text-secondary">' + esc(v.year || '') + ' ' + esc(v.color || '') + '</small></td>' +
      '<td>' + esc(v.license_plate) + '</td><td>' + esc(v.category_name || '-') + '</td>' +
      '<td>' + esc(v.fuel_type || '-') + '<br><small class="text-secondary">' + esc(v.transmission || '') + '</small></td>' +
      '<td>' + esc(v.seat_capacity || '-') + '</td><td>' + esc(money(v.daily_price)) + '</td><td>' + esc(Number(v.mileage_km).toLocaleString()) + ' km</td>' +
      '<td>' + badge(v.status) + '</td><td class="text-end">' + actionBtns(v.vehicle_id, { view: true, edit: true, del: APP.isAdmin }) + '</td></tr>',
    actions: { view: async id => {
      try {
        const { data: v } = await api('vehicles.php?id=' + id);
        const imgs = v.images.length ? v.images.map(i => '<div class="g-item"><img src="' + esc(i.url) + '" alt="">' + (i.is_primary == 1 ? '<span class="status status-active">Main</span>' : '') + '</div>').join('')
                                      : '<img src="' + esc(BASEURL + '/images/no-car.svg') + '" alt="" style="width:110px">';
        const row = (l, val) => '<div class="col-md-4"><small class="text-secondary d-block">' + l + '</small>' + esc(val ?? '-') + '</div>';
        document.getElementById('viewBody').innerHTML =
          '<div class="gallery mb-3">' + imgs + '</div><div class="row g-3">' +
          row('Vehicle', v.brand + ' ' + v.model) + row('Year', v.year) + row('Color', v.color) + row('Fuel', v.fuel_type) + row('Transmission', v.transmission) + row('Seats', v.seat_capacity) +
          row('License plate', v.license_plate) + row('Category', v.category_name) + row('Daily price', money(v.daily_price)) + row('Mileage', v.mileage_km + ' km') +
          row('Next service', v.next_service_date ? fmtDate(v.next_service_date) : '-') + row('Next service km', v.next_service_km) +
          row('Insurance expiry', v.insurance_expiry ? fmtDate(v.insurance_expiry) : '-') + row('Registration expiry', v.registration_expiry ? fmtDate(v.registration_expiry) : '-') +
          '<div class="col-md-4"><small class="text-secondary d-block">Status</small>' + badge(v.status) + '</div></div>';
        viewModal.show();
      } catch (e) { toast(e.message, 'error'); }
    } },
    // show existing images with a "main" radio and a "remove" checkbox when editing
    onOpen: (v) => {
      document.getElementById('newPreview').innerHTML = '';
      const wrap = document.getElementById('existingWrap'), box = document.getElementById('existingImages');
      if (v && v.images && v.images.length) {
        box.innerHTML = v.images.map(i => '<div class="g-item"><img src="' + esc(i.url) + '" alt="">' +
          '<label class="d-block"><input type="radio" name="primary_image_id" value="' + i.image_id + '"' + (i.is_primary == 1 ? ' checked' : '') + '> Main</label>' +
          '<label class="d-block text-danger"><input type="checkbox" name="remove_images[]" value="' + i.image_id + '"> Remove</label></div>').join('');
        wrap.style.display = '';
      } else { box.innerHTML = ''; wrap.style.display = 'none'; }
    },
  });

  const BASEURL = document.querySelector('meta[name="base-url"]').content;

  // live preview of newly chosen files
  document.getElementById('f_images').addEventListener('change', e => {
    const box = document.getElementById('newPreview'); box.innerHTML = '';
    [...e.target.files].forEach(f => {
      const d = document.createElement('div'); d.className = 'g-item';
      const img = document.createElement('img'); img.src = URL.createObjectURL(f); img.alt = '';
      d.appendChild(img); box.appendChild(d);
    });
  });
})();