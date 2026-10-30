<section class="dashboard-cards" id="statCards">
    <div class="text-secondary p-3"><span class="spinner-border spinner-border-sm"></span> Loading statistics...</div>
</section>

<div class="row g-3 mb-3">
    <div class="col-lg-5"><section class="content-panel h-100"><div class="panel-header"><h4>Reservations per month</h4></div><canvas id="chResv" height="160"></canvas></section></div>
    <div class="col-lg-5"><section class="content-panel h-100"><div class="panel-header"><h4>Revenue per month</h4></div><canvas id="chRev" height="160"></canvas></section></div>
    <div class="col-lg-2"><section class="content-panel h-100"><div class="panel-header"><h4>Fleet</h4></div><canvas id="chFleet" height="160"></canvas></section></div>
</div>

<section class="content-panel">
    <div class="panel-header">
        <h4>Recent Reservations</h4>
        <a href="<?= BASE_URL ?>/reservation_management.php" class="view-all">View All <i class="fa-solid fa-arrow-right ms-1"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Booking ID</th><th>Customer</th><th>Vehicle</th><th>Pickup Date</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody id="recentRes"></tbody>
        </table>
    </div>
</section>

<section class="content-panel mt-3">
    <div class="panel-header">
        <h4>Recently Added Vehicles</h4>
        <a href="<?= BASE_URL ?>/vehicle_management.php" class="view-all">View All <i class="fa-solid fa-arrow-right ms-1"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Photo</th><th>Vehicle</th><th>Plate</th><th>Price / day</th><th>Status</th></tr></thead>
            <tbody id="recentVeh"></tbody>
        </table>
    </div>
</section>