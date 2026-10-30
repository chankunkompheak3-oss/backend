<?php require_once APP_PATH . '/Views/layout/form.php'; ?>
<section class="content-panel">
    <div class="tools">
        <input type="search" class="form-control grow" name="q" data-filter placeholder="Search categories...">
        <select class="form-select" name="status" data-filter>
            <option value="">All statuses</option><option>Active</option><option>Inactive</option>
        </select>
        <?php if ($user['role'] === 'Admin'): ?>
        <button class="btn btn-primary ms-auto" id="addBtn"><i class="fa-solid fa-plus me-1"></i>Add Category</button>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table align-middle" id="tbl">
            <thead><tr><th>ID</th><th data-sort="name">Name</th><th>Description</th><th>Vehicles</th><th data-sort="status">Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
    <div id="pager"></div>
</section>

<div class="modal fade" id="formModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" novalidate>
    <div class="modal-header"><h5 class="modal-title"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <?php ff('category_name', 'Category name', 'text', 'col-12', ['required' => true, 'attrs' => 'maxlength="50"']); ?>
        <?php ff('description', 'Description', 'textarea', 'col-12'); ?>
        <?php ff('status', 'Status', 'select', 'col-12', ['options' => ['Active' => 'Active', 'Inactive' => 'Inactive']]); ?>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
</form></div></div>