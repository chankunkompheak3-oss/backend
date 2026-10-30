<?php require_once APP_PATH . '/Views/layout/form.php'; ?>
<section class="content-panel">
    <div class="tools">
        <input type="search" class="form-control grow" name="q" data-filter placeholder="Search name, phone, email, license or ID...">
        <select class="form-select" name="status" data-filter><option value="">All statuses</option><option>Active</option><option>Inactive</option></select>
        <select class="form-select" name="blacklisted" data-filter><option value="">Blacklist: any</option><option value="1">Blacklisted</option><option value="0">Not blacklisted</option></select>
        <button class="btn btn-primary ms-auto" id="addBtn"><i class="fa-solid fa-user-plus me-1"></i>Register Customer</button>
    </div>
    <div class="table-responsive">
        <table class="table align-middle" id="tbl">
            <thead><tr><th data-sort="id">ID</th><th data-sort="name">Name</th><th data-sort="phone">Phone</th><th>Email</th><th>License</th><th>Reservations</th><th data-sort="status">Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
    <div id="pager"></div>
</section>

<div class="modal fade" id="formModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><form class="modal-content" novalidate>
    <div class="modal-header"><h5 class="modal-title"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <?php
        ff('full_name', 'Full name', 'text', 'col-md-6', ['required' => true, 'attrs' => 'maxlength="100"']);
        ff('gender', 'Gender', 'select', 'col-md-3', ['options' => ['' => 'Select...', 'Male' => 'Male', 'Female' => 'Female']]);
        ff('status', 'Status', 'select', 'col-md-3', ['options' => ['Active' => 'Active', 'Inactive' => 'Inactive']]);
        ff('phone', 'Phone', 'tel', 'col-md-6', ['required' => true, 'attrs' => 'maxlength="20"']);
        ff('email', 'Email', 'email', 'col-md-6', ['attrs' => 'maxlength="100"']);
        ff('address', 'Address', 'textarea', 'col-12');
        ff('license_number', 'Driving license no.', 'text', 'col-md-6', ['attrs' => 'maxlength="50"']);
        ff('license_expiry', 'License expiry', 'date', 'col-md-6');
        ?>
        <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" id="f_is_blacklisted" name="is_blacklisted" value="1"><label class="form-check-label" for="f_is_blacklisted">Blacklisted</label></div></div>
        <?php ff('blacklist_reason', 'Blacklist reason', 'text', 'col-md-8', ['attrs' => 'maxlength="255"']); ?>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
</form></div></div>

<div class="modal fade" id="viewModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Customer details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="viewBody"></div>
</div></div></div>