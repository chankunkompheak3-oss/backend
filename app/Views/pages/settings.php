<?php require_once APP_PATH . '/Views/layout/form.php'; ?>
<?php if (!empty($_GET['force']) || $user['must_change_password']): ?>
<div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>For security, you must change your temporary password before using the system.</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-6">
        <section class="content-panel h-100">
            <div class="panel-header"><h4>My profile</h4></div>
            <form id="profileForm" novalidate><div class="row g-3">
                <div class="col-md-6"><label class="form-label">Username</label><input class="form-control" name="username" disabled></div>
                <div class="col-md-6"><label class="form-label">Role</label><input class="form-control" name="role" disabled></div>
                <?php ff('email', 'Email', 'email', 'col-md-6', ['attrs' => 'maxlength="100"']); ?>
                <?php ff('phone', 'Phone', 'tel', 'col-md-6', ['attrs' => 'maxlength="20"']); ?>
                <div class="col-12"><button class="btn btn-primary" type="submit">Save profile</button></div>
            </div></form>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="content-panel h-100">
            <div class="panel-header"><h4>Change password</h4></div>
            <form id="passwordForm" novalidate autocomplete="off"><div class="row g-3">
                <?php
                ff('current_password', 'Current password', 'password', 'col-12', ['required' => true, 'attrs' => 'autocomplete="current-password"']);
                ff('new_password', 'New password', 'password', 'col-md-6', ['required' => true, 'attrs' => 'autocomplete="new-password"']);
                ff('confirm_password', 'Confirm new password', 'password', 'col-md-6', ['required' => true, 'attrs' => 'autocomplete="new-password"']);
                ?>
                <div class="col-12"><small class="text-secondary">At least 8 characters with a letter and a number.</small></div>
                <div class="col-12"><button class="btn btn-primary" type="submit">Change password</button></div>
            </div></form>
        </section>
    </div>
    <div class="col-12">
        <section class="content-panel">
            <div class="panel-header"><h4>System &amp; rental settings</h4>
                <?php if ($user['role'] === 'Admin'): ?><a class="view-all" href="<?= BASE_URL ?>/staff_management.php">Manage users &amp; roles <i class="fa-solid fa-arrow-right ms-1"></i></a>
                <?php else: ?><small class="text-secondary">Only administrators can change these.</small><?php endif; ?>
            </div>
            <form id="systemForm" novalidate><div class="row g-3">
                <?php
                ff('store_name', 'Store name', 'text', 'col-md-6', ['required' => true, 'attrs' => 'maxlength="100"']);
                ff('contact_email', 'Contact email', 'email', 'col-md-3');
                ff('phone', 'Phone', 'tel', 'col-md-3');
                ff('address', 'Address', 'textarea', 'col-12');
                ff('currency', 'Currency (3 letters)', 'text', 'col-md-3', ['required' => true, 'attrs' => 'maxlength="3"']);
                ff('tax_rate_percent', 'Tax rate (%)', 'number', 'col-md-3', ['attrs' => 'min="0" max="100" step="0.01"']);
                ff('late_fee_multiplier', 'Late fee (x daily rate)', 'number', 'col-md-3', ['attrs' => 'min="0" max="10" step="0.01"']);
                ff('default_deposit', 'Default deposit', 'number', 'col-md-3', ['attrs' => 'min="0" step="0.01"']);
                ?>
                <?php if ($user['role'] === 'Admin'): ?><div class="col-12"><button class="btn btn-primary" type="submit">Save settings</button></div><?php endif; ?>
            </div></form>
        </section>
    </div>
</div>