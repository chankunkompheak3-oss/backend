/* Shared helpers: api(), toast(), confirmDialog(), esc(), pager, formatting. */
(function () {
  const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  const BASE = (document.querySelector('meta[name="base-url"]') || {}).content || '';

  class ApiError extends Error {
    constructor(message, status, errors) { super(message); this.status = status; this.errors = errors || {}; }
  }

  // api('vehicles.php?id=3', {method:'PUT', body:{...} or FormData})
  // Every non-GET request is sent as POST + ?_method=... (works with multipart too) plus the CSRF header.
  async function api(url, { method = 'GET', body = null } = {}) {
    const opts = { method: 'GET', credentials: 'same-origin', headers: { Accept: 'application/json' } };
    let full = BASE + '/api/' + url;
    if (method !== 'GET') {
      opts.method = 'POST';
      if (method !== 'POST') full += (full.includes('?') ? '&' : '?') + '_method=' + method;
      opts.headers['X-CSRF-Token'] = csrf;
      if (body instanceof FormData) opts.body = body;
      else if (body !== null) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    }
    let res;
    try { res = await fetch(full, opts); }
    catch { throw new ApiError('Network error. Is Apache running?', 0); }
    let json;
    try { json = await res.json(); }
    catch { throw new ApiError('The server returned an invalid response.', res.status); }
    if (res.status === 401) {                       // session expired
      location.href = BASE + '/login.php?next=' + encodeURIComponent(location.pathname);
      throw new ApiError(json.message || 'Please log in.', 401);
    }
    if (!res.ok || !json.success) throw new ApiError(json.message || 'Request failed.', res.status, json.errors);
    return json;
  }

  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  function toast(message, type = 'success') {
    const el = document.createElement('div');
    el.className = 'toast align-items-center border-0 text-bg-' + (type === 'error' ? 'danger' : type);
    el.setAttribute('role', 'alert');
    el.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    el.querySelector('.toast-body').textContent = message;
    document.getElementById('toastBox').appendChild(el);
    el.addEventListener('hidden.bs.toast', () => el.remove());
    new bootstrap.Toast(el, { delay: 4500 }).show();
  }

  function confirmDialog(message, okText = 'Delete', okClass = 'danger') {
    return new Promise(resolve => {
      const wrap = document.createElement('div');
      wrap.className = 'modal fade'; wrap.tabIndex = -1;
      wrap.innerHTML = '<div class="modal-dialog modal-dialog-centered"><div class="modal-content">' +
        '<div class="modal-header"><h5 class="modal-title">Please confirm</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>' +
        '<div class="modal-body"></div><div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>' +
        '<button class="btn btn-' + okClass + '" data-ok></button></div></div></div>';
      wrap.querySelector('.modal-body').textContent = message;
      wrap.querySelector('[data-ok]').textContent = okText;
      document.body.appendChild(wrap);
      const modal = new bootstrap.Modal(wrap);
      let answer = false;
      wrap.querySelector('[data-ok]').onclick = () => { answer = true; modal.hide(); };
      wrap.addEventListener('hidden.bs.modal', () => { wrap.remove(); resolve(answer); });
      modal.show();
    });
  }

  function renderPager(el, m, onPage) {
    if (!el) return;
    if (!m || !m.total) { el.innerHTML = ''; return; }
    let h = '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"><small class="text-secondary">Page ' +
      m.page + ' of ' + m.pages + ' &middot; ' + m.total + ' record(s)</small>';
    if (m.pages > 1) {
      const from = Math.max(1, m.page - 2), to = Math.min(m.pages, m.page + 2);
      h += '<ul class="pagination pagination-sm mb-0">';
      h += '<li class="page-item ' + (m.page === 1 ? 'disabled' : '') + '"><a class="page-link" href="#" data-p="' + (m.page - 1) + '">&laquo;</a></li>';
      for (let i = from; i <= to; i++) h += '<li class="page-item ' + (i === m.page ? 'active' : '') + '"><a class="page-link" href="#" data-p="' + i + '">' + i + '</a></li>';
      h += '<li class="page-item ' + (m.page === m.pages ? 'disabled' : '') + '"><a class="page-link" href="#" data-p="' + (m.page + 1) + '">&raquo;</a></li></ul>';
    }
    el.innerHTML = h + '</div>';
    el.onclick = e => {
      const a = e.target.closest('[data-p]'); if (!a) return; e.preventDefault();
      const p = +a.dataset.p; if (p >= 1 && p <= m.pages && p !== m.page) onPage(p);
    };
  }

  function money(n) {
    n = Number(n || 0);
    try { return new Intl.NumberFormat(undefined, { style: 'currency', currency: APP.currency }).format(n); }
    catch { return APP.currency + ' ' + n.toFixed(2); }
  }
  const parseDT = s => { if (!s) return null; s = String(s); return new Date(s.length === 10 ? s + 'T00:00:00' : s.replace(' ', 'T')); };
  const fmtDate = s => { const d = parseDT(s); return d && !isNaN(d) ? d.toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' }) : '-'; };
  const fmtDateTime = s => { const d = parseDT(s); return d && !isNaN(d) ? d.toLocaleString(undefined, { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-'; };
  const badge = s => '<span class="status status-' + esc(String(s || '').toLowerCase().replace(/\s+/g, '')) + '">' + esc(s || '-') + '</span>';

  function actionBtns(id, o = {}) {
    const b = (act, icon, cls, t) => '<button type="button" class="btn btn-sm btn-outline-' + cls + ' me-1" data-act="' + act + '" data-id="' + id + '" title="' + t + '"><i class="fa-solid ' + icon + '"></i></button>';
    return (o.view ? b('view', 'fa-eye', 'secondary', 'View') : '') + (o.edit !== false ? b('edit', 'fa-pen-to-square', 'primary', 'Edit') : '') + (o.del ? b('delete', 'fa-trash', 'danger', 'Delete') : '');
  }

  function clearErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
  }
  function showErrors(form, errors) {
    Object.entries(errors || {}).forEach(([name, msg]) => {
      const el = form.elements[name]; if (!el) return;
      el.classList.add('is-invalid');
      let fb = el.parentElement.querySelector('.invalid-feedback');
      if (!fb) { fb = document.createElement('div'); fb.className = 'invalid-feedback'; el.after(fb); }
      fb.textContent = msg;
    });
  }
  function setBusy(btn, busy) { if (btn) { btn.disabled = busy; btn.classList.toggle('opacity-75', busy); } }
  function debounce(fn, ms = 300) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

  Object.assign(window, { api, ApiError, esc, toast, confirmDialog, renderPager, money, fmtDate, fmtDateTime, badge, actionBtns, clearErrors, showErrors, setBusy, debounce });

  // sidebar toggle (mobile) and logout
  document.getElementById('menuToggle')?.addEventListener('click', () => document.getElementById('sidebar').classList.toggle('open'));
  document.getElementById('logoutBtn')?.addEventListener('click', async () => {
    try { await api('auth.php?action=logout', { method: 'POST' }); } catch (e) { /* ignore */ }
    location.href = BASE + '/login.php';
  });
})();