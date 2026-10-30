new CrudPage({
  endpoint: 'categories.php', idKey: 'category_id', title: 'Category',
  tbody: '#tbl tbody', pager: '#pager', modal: '#formModal', addBtn: '#addBtn', filters: '[data-filter]',
  nameOf: c => 'category "' + c.category_name + '"',
  row: c => '<tr><td>' + c.category_id + '</td><td><strong>' + esc(c.category_name) + '</strong></td><td>' + esc(c.description || '-') +
    '</td><td>' + c.vehicle_count + '</td><td>' + badge(c.status) + '</td><td class="text-end">' +
    (APP.isAdmin ? actionBtns(c.category_id, { edit: true, del: true }) : '<span class="text-secondary">View only</span>') + '</td></tr>',
});