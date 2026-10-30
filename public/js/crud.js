/* CrudPage: wires a table, filters, pager and an add/edit modal to one API endpoint. */
class CrudPage {
  constructor(o) {
    this.o = Object.assign({ idKey: 'id', pageSize: 10, multipart: false, title: 'Record' }, o);
    this.state = { page: 1, sort: null, dir: 'asc' };
    this.tbody = document.querySelector(o.tbody);
    this.pager = document.querySelector(o.pager);
    this.modalEl = document.querySelector(o.modal);
    this.modal = new bootstrap.Modal(this.modalEl);
    this.form = this.modalEl.querySelector('form');
    this.editId = null;
    this.items = [];
    this.bind();
    this.load(1);
  }

  bind() {
    const reload = debounce(() => this.load(1), 300);
    if (this.o.filters) document.querySelectorAll(this.o.filters).forEach(el => { el.addEventListener('input', reload); el.addEventListener('change', reload); });
    if (this.o.addBtn) document.querySelector(this.o.addBtn)?.addEventListener('click', () => this.openCreate());
    this.form.addEventListener('submit', e => this.save(e));
    this.tbody.addEventListener('click', e => {
      const btn = e.target.closest('[data-act]'); if (!btn) return;
      const id = btn.dataset.id, item = this.items.find(i => String(i[this.o.idKey]) === String(id));
      if (btn.dataset.act === 'edit') this.openEdit(id);
      else if (btn.dataset.act === 'delete') this.remove(id, item);
      else this.o.actions?.[btn.dataset.act]?.(id, item);
    });
    this.tbody.closest('table').querySelectorAll('th[data-sort]').forEach(th => th.addEventListener('click', () => {
      this.state.dir = this.state.sort === th.dataset.sort && this.state.dir === 'asc' ? 'desc' : 'asc';
      this.state.sort = th.dataset.sort;
      this.load(1);
    }));
  }

  async load(page = this.state.page) {
    this.state.page = page;
    const p = new URLSearchParams({ page, per_page: this.o.pageSize });
    if (this.o.filters) document.querySelectorAll(this.o.filters).forEach(el => { if (el.name && el.value !== '') p.set(el.name, el.value); });
    if (this.state.sort) { p.set('sort', this.state.sort); p.set('dir', this.state.dir); }
    const cols = this.tbody.closest('table').querySelectorAll('thead th').length;
    this.tbody.innerHTML = '<tr><td colspan="' + cols + '" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Loading...</td></tr>';
    try {
      const { data } = await api(this.o.endpoint + '?' + p);
      this.items = data.items;
      this.tbody.innerHTML = data.items.length
        ? data.items.map(i => this.o.row(i)).join('')
        : '<tr><td colspan="' + cols + '" class="text-center text-secondary py-4">No records found.</td></tr>';
      renderPager(this.pager, data.meta, pg => this.load(pg));
      this.o.onLoad?.(data);
    } catch (e) {
      this.tbody.innerHTML = '<tr><td colspan="' + cols + '" class="text-center text-danger py-4">' + esc(e.message) + '</td></tr>';
    }
  }

  setTitle() { this.modalEl.querySelector('.modal-title').textContent = (this.editId ? 'Edit ' : 'Add ') + this.o.title; }

  async openCreate() {
    this.editId = null; this.form.reset(); clearErrors(this.form); this.setTitle();
    try { await this.o.beforeOpen?.(); } catch (e) { toast(e.message, 'error'); return; }
    this.o.onOpen?.(null, this.form);
    this.modal.show();
  }

  async openEdit(id) {
    try {
      await this.o.beforeOpen?.();
      const { data } = await api(this.o.endpoint + '?id=' + encodeURIComponent(id));
      this.editId = id; this.form.reset(); clearErrors(this.form); this.setTitle();
      this.fill(data);
      this.o.onOpen?.(data, this.form);
      this.modal.show();
    } catch (e) { toast(e.message, 'error'); }
  }

  fill(item) {
    for (const el of this.form.elements) {
      if (!el.name || !(el.name in item) || el.type === 'file') continue;
      if (el.type === 'checkbox') el.checked = String(item[el.name]) === '1';
      else if (el.type === 'datetime-local') el.value = String(item[el.name] ?? '').replace(' ', 'T').slice(0, 16);
      else el.value = item[el.name] ?? '';
    }
  }

  formBody() {
    if (this.o.multipart) {
      const fd = new FormData(this.form);
      for (const el of this.form.elements) if (el.type === 'checkbox' && el.name && !el.name.endsWith('[]')) fd.set(el.name, el.checked ? '1' : '0');
      return fd;
    }
    const body = {};
    for (const el of this.form.elements) {
      if (!el.name || el.type === 'file' || el.type === 'submit' || el.type === 'button') continue;
      body[el.name] = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
    }
    return body;
  }

  async save(e) {
    e.preventDefault(); clearErrors(this.form);
    const btn = this.form.querySelector('[type="submit"]'); setBusy(btn, true);
    try {
      const body = this.o.buildBody ? this.o.buildBody(this.form, this.editId) : this.formBody();
      const url = this.o.endpoint + (this.editId ? '?id=' + encodeURIComponent(this.editId) : '');
      const json = await api(url, { method: this.editId ? 'PUT' : 'POST', body });
      toast(json.message || 'Saved.');
      this.modal.hide(); this.load(); this.o.onSaved?.(json);
    } catch (err) {
      showErrors(this.form, err.errors); toast(err.message, 'error');
    } finally { setBusy(btn, false); }
  }

  async remove(id, item) {
    const name = this.o.nameOf && item ? this.o.nameOf(item) : 'this record';
    if (!await confirmDialog('Delete ' + name + '? This cannot be undone.')) return;
    try {
      const json = await api(this.o.endpoint + '?id=' + encodeURIComponent(id), { method: 'DELETE' });
      toast(json.message || 'Deleted.');
      this.load(this.items.length === 1 && this.state.page > 1 ? this.state.page - 1 : this.state.page);
    } catch (e) { toast(e.message, 'error'); }
  }
}