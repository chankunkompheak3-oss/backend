<section class="content-panel mb-3">
    <div class="tools mb-0">
        <label class="small text-secondary">From</label><input type="date" class="form-control" id="from">
        <label class="small text-secondary">To</label><input type="date" class="form-control" id="to">
        <button class="btn btn-primary" id="apply"><i class="fa-solid fa-filter me-1"></i>Apply</button>
    </div>
</section>

<section class="dashboard-cards" id="repCards"></section>

<section class="content-panel mb-3"><div class="panel-header"><h4>Net revenue by day</h4></div><canvas id="chRevenue" height="90"></canvas></section>

<section class="content-panel">
    <div class="panel-header">
        <h4 id="secTitle">Report</h4>
        <a class="btn btn-sm btn-outline-success" id="csvBtn" href="#"><i class="fa-solid fa-file-csv me-1"></i>Export CSV</a>
    </div>
    <div class="tools" id="tabs">
        <button class="btn btn-sm btn-primary" data-sec="reservations">Reservations</button>
        <button class="btn btn-sm btn-outline-primary" data-sec="utilization">Vehicle utilization</button>
        <button class="btn btn-sm btn-outline-primary" data-sec="top_vehicles">Most rented</button>
        <button class="btn btn-sm btn-outline-primary" data-sec="customers">Customer activity</button>
        <button class="btn btn-sm btn-outline-primary" data-sec="payments">Payments</button>
        <button class="btn btn-sm btn-outline-primary" data-sec="revenue">Revenue by date</button>
        <button class="btn btn-sm btn-outline-primary" data-sec="fleet">Available vs rented</button>
    </div>
    <div class="table-responsive"><table class="table align-middle"><thead id="repHead"></thead><tbody id="repBody"></tbody></table></div>
    <details class="mt-3 small text-secondary"><summary>How revenue is calculated</summary>
        Revenue is money actually received, grouped by payment date: Rental, Late Fee and Damage payments minus Refunds.
        Deposits are refundable, so they are shown separately and not counted as revenue. Unpaid (pending) reservations count for nothing until a payment is recorded.
        Cancelled or rejected reservations add nothing; if money was already received for one, it stays in revenue until a Refund is recorded.
    </details>
</section>