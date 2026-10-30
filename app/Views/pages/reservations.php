<?php require_once APP_PATH . '/Views/layout/form.php'; ?>
<section class="content-panel">
    <div class="tools">
        <input type="search" class="form-control grow" name="q" data-filter placeholder="Search customer, phone, plate, vehicle or ID...">
        <select class="form-select" name="status" data-filter><option value="">All statuses</option><option>Pending</option><option>Approved</option><option>Rejected</option><option>Cancelled</option><option>Completed</option></select>
        <input type="date" class="form-control" name="date_from" data-filter title="Pickup from">
        <input type="date" class="form-control" name="date_to" data-filter title="Pickup to">
        <button class="btn btn-primary ms-auto" id="addBtn"><i class="fa-solid fa-plus me-1"></i>New Reservation</button>
    </div>
    <div class="table-responsive">
        <table class="table align-middle" id="tbl">
            <thead><tr><th data-sort="id">ID</th><th data-sort="customer">Customer</th><th>Vehicle</th><th data-sort="pickup">Pickup</th><th data-sort="return">Return</th><th data-sort="total">Total</th><th>Payment</th><th data-sort="status">Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
    <div id="pager"></div>
</section>

<div class="modal fade" id="formModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><form class="modal-content" novalidate>
    <div class="modal-header"><h5 class="modal-title"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <?php
        ff('customer_id', 'Customer', 'select', 'col-md-6', ['required' => true, 'options' => ['' => 'Select customer...']]);
        ff('vehicle_id', 'Vehicle', 'select', 'col-md-6', ['required' => true, 'options' => ['' => 'Select vehicle...']]);
        ff('pickup_datetime', 'Pickup date & time', 'datetime-local', 'col-md-6', ['required' => true]);
        ff('return_datetime', 'Return date & time', 'datetime-local', 'col-md-6', ['required' => true]);
        ff('pickup_location', 'Pickup location', 'text', 'col-md-6', ['attrs' => 'maxlength="150"']);
        ff('return_location', 'Return location', 'text', 'col-md-6', ['attrs' => 'maxlength="150"']);
        ff('discount_amount', 'Discount', 'number', 'col-md-6', ['attrs' => 'min="0" step="0.01" value="0"']);
        ff('extra_fees', 'Additional fees', 'number', 'col-md-6', ['attrs' => 'min="0" step="0.01" value="0"']);
        ff('note', 'Note', 'textarea', 'col-12');
        ?>
        <div class="col-12"><div class="quote-box" id="quoteBox">Choose a vehicle and dates to see the price and availability.</div></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save reservation</button></div>
</form></div></div>

<div class="modal fade" id="viewModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Reservation details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="viewBody"></div>
</div></div></div>