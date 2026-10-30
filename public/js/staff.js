(function () {
  const crud = new CrudPage({
    endpoint: 'staff.php', idKey: 'user_id', title: 'Staff Member',
    tbody: '#tbl tbody', pager: '#pager', modal: '#formModal', addBtn: '#addBtn', filters: '[data-filter]',
    nameOf: s => s.username + ' (account will be deactivated)',
    onOpen: s => { document.getElementById('f_password').required = !s; document.getElementById('f_password').placeholder = s ? 'Leave empty to keep the current password' : 'Min 8 chars, letter + number'; },
    row: s => '<tr><td><strong>' + esc(s.full_name || '-') + '</strong><br><small class="text-secondary">' + esc(s.employee_code || '') + '</small></td><td>' + esc(s.username) +
      '</td><td>' + badge(s.role) + '</td><td>' + esc(s.position || '-') + '</td><td>' + esc(s.email || '-') + '<br><small class="text-secondary">' + esc(s.phone || '') +
      '</small></td><td>' + badge(s.status) + '</td><td>' + esc(s.last_login_at ? fmtDateTime(s.last_login_at) : 'Never') + '</td><td class="text-end">' +
      actionBtns(s.user_id, { edit: true, del: false }) +
      '<button class="btn btn-sm btn-outline-' + (s.status === 'Active' ? 'warning' : 'success') + '" data-act="toggle" data-id="' + s.user_id + '" title="' + (s.status === 'Active' ? 'Deactivate' : 'Activate') + '"><i class="fa-solid ' + (s.status === 'Active' ? 'fa-user-slash' : 'fa-user-check') + '"></i></button></td></tr>',
    actions: { toggle: async (id, s) => {
      const next = s.status === 'Active' ? 'Inactive' : 'Active';
      if (!await confirmDialog((next === 'Inactive' ? 'Deactivate ' : 'Activate ') + s.username + '?', next === 'Inactive' ? 'Deactivate' : 'Activate', next === 'Inactive' ? 'warning' : 'success')) return;
      try { const j = await api('staff.php?id=' + id + '&action=status', { method: 'PUT', body: { status: next } }); toast(j.message); crud.load(); }
      catch (e) { toast(e.message, 'error'); }
    } },
  });
})();