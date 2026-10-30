<?php require_once APP_PATH . '/Views/layout/form.php'; ?>
<section class="content-panel">
    <div class="tools">
        <input type="search" class="form-control grow" name="q" data-filter placeholder="Search name, username, email, phone, code...">
        <select class="form-select" name="role" data-filter><option value="">All roles</option><option value="Admin">Admin</option><option value="Staff">Staff</option></select>
        <select class="form-select" name="status" data-filter><option value="">All statuses</option><option>Active</option><option>Inactive</option></select>
        <button class="btn btn-primary ms-auto" id="addBtn"><i class="fa-solid fa-user-plus me-1"></i>Add Staff</button>
    </div>
    <div class="table-responsive">
        <table class="table align-middle" id="tbl">
            <thead><tr><th data-sort="name">Name</th><th data-sort="username">Username</th><th data-sort="role">Role</th><th>Position</th><th>Contact</th><th data-sort="status">Status</th><th data-sort="last_login">Last login</th><th class="text-end">Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
    <div id="pager"></div>
    <p class="text-secondary small mt-2 mb-0"><strong>Permissions:</strong> Admin = everything, including staff, categories, deletions and system settings. Staff = vehicles, customers, reservations and payments, reports, and their own profile.</p>
</section>

<div class="modal fade" id="formModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><form class="modal-content" novalidate autocomplete="off">
    <div class="modal-header"><h5 class="modal-title"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <?php
        ff('full_name', 'Full name', 'text', 'col-md-6', ['required' => true, 'attrs' => 'maxlength="100"']);
        ff('employee_code', 'Employee code', 'text', 'col-md-3', ['attrs' => 'maxlength="20"']);
        ff('gender', 'Gender', 'select', 'col-md-3', ['options' => ['' => 'Select...', 'Male' => 'Male', 'Female' => 'Female']]);
        ff('username', 'Username', 'text', 'col-md-4', ['required' => true, 'attrs' => 'maxlength="50"']);
        ff('password', 'Password (temporary)', 'password', 'col-md-4', ['attrs' => 'autocomplete="new-password" placeholder="Leave empty to keep"']);
        ff('role', 'Role', 'select', 'col-md-2', ['required' => true, 'options' => ['Staff' => 'Staff', 'Admin' => 'Admin']]);
        ff('status', 'Status', 'select', 'col-md-2', ['options' => ['Active' => 'Active', 'Inactive' => 'Inactive']]);
        ff('email', 'Email', 'email', 'col-md-4');
        ff('phone', 'Phone', 'tel', 'col-md-4');
        ff('position', 'Position', 'text', 'col-md-4', ['attrs' => 'maxlength="50"']);
        ff('salary', 'Salary', 'number', 'col-md-4', ['attrs' => 'min="0" step="0.01"']);
        ff('hire_date', 'Hire date', 'date', 'col-md-4');
        ?>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
</form></div></div>