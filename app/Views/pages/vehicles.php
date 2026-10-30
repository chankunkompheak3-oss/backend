<?php
require_once APP_PATH . '/Views/layout/form.php';
$fuel  = ['' => 'Select...', 'Petrol' => 'Petrol', 'Diesel' => 'Diesel', 'Hybrid' => 'Hybrid', 'Electric' => 'Electric', 'LPG' => 'LPG'];
$trans = ['' => 'Select...', 'Automatic' => 'Automatic', 'Manual' => 'Manual'];
$stat  = ['Available' => 'Available', 'Reserved' => 'Reserved', 'Rented' => 'Rented', 'Maintenance' => 'Maintenance', 'Out Of Service' => 'Out Of Service'];
?>
<section class="content-panel">
    <div class="tools">
        <input type="search" class="form-control grow" name="q" data-filter placeholder="Search brand, model, plate, color...">
        <select class="form-select" name="category_id" data-filter id="catFilter"><option value="">All categories</option></select>
        <select class="form-select" name="fuel_type" data-filter><?php foreach ($fuel as $k => $t) echo '<option value="' . $e($k) . '">' . ($k === '' ? 'All fuel types' : $e($t)) . '</option>'; ?></select>
        <select class="form-select" name="transmission" data-filter><?php foreach ($trans as $k => $t) echo '<option value="' . $e($k) . '">' . ($k === '' ? 'All transmissions' : $e($t)) . '</option>'; ?></select>
        <select class="form-select" name="status" data-filter><option value="">All statuses</option><?php foreach ($stat as $k => $t) echo '<option>' . $e($t) . '</option>'; ?></select>
        <button class="btn btn-primary ms-auto" id="addBtn"><i class="fa-solid fa-plus me-1"></i>Add Vehicle</button>
    </div>
    <div class="table-responsive">
        <table class="table align-middle" id="tbl">
            <thead><tr>
                <th>Photo</th><th data-sort="brand">Vehicle</th><th data-sort="plate">Plate</th><th>Category</th><th>Fuel / Gear</th>
                <th>Seats</th><th data-sort="price">Price / day</th><th data-sort="mileage">Mileage</th><th data-sort="status">Status</th><th class="text-end">Actions</th>
            </tr></thead>
            <tbody></tbody>
        </table>
    </div>
    <div id="pager"></div>
</section>

<!-- Add / Edit -->
<div class="modal fade" id="formModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><form class="modal-content" novalidate enctype="multipart/form-data">
    <div class="modal-header"><h5 class="modal-title"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row g-3">
        <?php
        ff('brand', 'Brand', 'text', 'col-md-4', ['required' => true, 'attrs' => 'maxlength="50"']);
        ff('model', 'Model', 'text', 'col-md-4', ['required' => true, 'attrs' => 'maxlength="50"']);
        ff('year', 'Year', 'number', 'col-md-4', ['attrs' => 'min="1980" max="' . ((int)date('Y') + 1) . '"']);
        ff('color', 'Color', 'text', 'col-md-4', ['attrs' => 'maxlength="30"']);
        ff('fuel_type', 'Fuel type', 'select', 'col-md-4', ['options' => $fuel]);
        ff('transmission', 'Transmission', 'select', 'col-md-4', ['options' => $trans]);
        ff('seat_capacity', 'Seat capacity', 'number', 'col-md-4', ['attrs' => 'min="1" max="60"']);
        ff('license_plate', 'License plate', 'text', 'col-md-4', ['required' => true, 'attrs' => 'maxlength="50"']);
        ff('daily_price', 'Daily price', 'number', 'col-md-4', ['required' => true, 'attrs' => 'min="0" step="0.01"']);
        ff('mileage_km', 'Mileage (km)', 'number', 'col-md-4', ['attrs' => 'min="0"']);
        ff('next_service_date', 'Next service date', 'date', 'col-md-4');
        ff('next_service_km', 'Next service (km)', 'number', 'col-md-4', ['attrs' => 'min="0"']);
        ff('category_id', 'Category', 'select', 'col-md-4', ['options' => ['' => 'None']]);
        ff('insurance_expiry', 'Insurance expiry', 'date', 'col-md-4');
        ff('registration_expiry', 'Registration expiry', 'date', 'col-md-4');
        ff('status', 'Availability status', 'select', 'col-md-4', ['options' => $stat]);
        ?>
        <div class="col-12">
            <label class="form-label" for="f_images">Images (JPEG, PNG or WebP, max 5 MB each)</label>
            <input class="form-control" type="file" id="f_images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
            <div class="invalid-feedback"></div>
            <div class="gallery mt-2" id="newPreview"></div>
        </div>
        <div class="col-12" id="existingWrap" style="display:none">
            <label class="form-label">Current images <small class="text-secondary">(choose the main one, or tick to remove)</small></label>
            <div class="gallery" id="existingImages"></div>
        </div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save vehicle</button></div>
</form></div></div>

<!-- Details -->
<div class="modal fade" id="viewModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Vehicle details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="viewBody"></div>
</div></div></div>