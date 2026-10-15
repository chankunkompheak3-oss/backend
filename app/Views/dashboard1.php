<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../../public/css/dashbord1.css">
</head>
<body>

<div class="dashboard-container">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">

        <div class="brand">
            <div class="brand-icon">
                <i class="fa-solid fa-car-side"></i>
            </div>
            <div>
                <h4>CarRental</h4>
                <small>Management System</small>
            </div>
        </div>

        <div class="menu-title">MAIN MENU</div>

        <ul class="sidebar-menu">
            <li>
                <a href="#" class="active">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-car"></i>
                    <span>Vehicles</span>
                    <i class="fa-solid fa-chevron-down arrow"></i>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Car Categories</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Reservations</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-key"></i>
                    <span>Rental Management</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>Payments</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Invoices</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>Staff Management</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-chart-column"></i>
                    <span>Reports</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>

        <!-- PROFILE -->
        <div class="sidebar-profile">
            <div class="profile-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div>
                <h6>Admin User</h6>
                <small>Administrator</small>
            </div>
            <i class="fa-solid fa-ellipsis ms-auto"></i>
        </div>

    </aside>


    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- TOPBAR -->
        <header class="topbar">

            <div class="d-flex align-items-center gap-3">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div>
                    <h2>Dashboard</h2>
                    <p>Welcome back! Here's your car rental overview.</p>
                </div>
            </div>

            <div class="topbar-right">

                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Search...">
                </div>

                <button class="notification-btn" aria-label="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    <span class="notification-dot"></span>
                </button>

                <div class="topbar-user">
                    <div class="profile-avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="user-info">
                        <strong>Admin</strong>
                        <small>Administrator</small>
                    </div>
                </div>

            </div>

        </header>


        <!-- PAGE HEADING -->
        <section class="page-heading">
            <div>
                <h3>Overview</h3>
                <p>Here's what's happening with your fleet today.</p>
            </div>

            <button class="btn btn-primary">
                <i class="fa-solid fa-download me-2"></i>
                Export Report
            </button>
        </section>


        <!-- STATISTICS CARDS -->
        <section class="row g-3 mb-4">

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon icon-blue">
                        <i class="fa-solid fa-car"></i>
                    </div>
                    <div class="stat-details">
                        <p>Total Vehicles</p>
                        <h3>30</h3>
                        <span class="positive">
                            <i class="fa-solid fa-arrow-up"></i> 12%
                        </span>
                        <small>vs. last month</small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon icon-green">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="stat-details">
                        <p>Available Cars</p>
                        <h3>18</h3>
                        <span class="positive">
                            <i class="fa-solid fa-arrow-up"></i> 8%
                        </span>
                        <small>vs. last month</small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon icon-orange">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div class="stat-details">
                        <p>Active Rentals</p>
                        <h3>10</h3>
                        <span class="positive">
                            <i class="fa-solid fa-arrow-up"></i> 5%
                        </span>
                        <small>vs. last month</small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon icon-purple">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div class="stat-details">
                        <p>Reservations</p>
                        <h3>12</h3>
                        <span class="positive">
                            <i class="fa-solid fa-arrow-up"></i> 15%
                        </span>
                        <small>vs. last month</small>
                    </div>
                </div>
            </div>

        </section>


        <!-- CHARTS -->
        <section class="row g-3 mb-4">

            <!-- REVENUE CHART -->
            <div class="col-12 col-xl-8">
                <div class="content-card">

                    <div class="card-heading">
                        <div>
                            <h5>Revenue Overview</h5>
                            <p>Monthly rental revenue</p>
                        </div>

                        <select class="form-select form-select-sm chart-filter">
                            <option>This Year</option>
                            <option>This Month</option>
                            <option>This Week</option>
                        </select>
                    </div>

                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>

                </div>
            </div>


            <!-- VEHICLE STATUS -->
            <div class="col-12 col-xl-4">
                <div class="content-card">

                    <div class="card-heading">
                        <div>
                            <h5>Vehicle Status</h5>
                            <p>Current fleet availability</p>
                        </div>

                        <button class="icon-button" aria-label="More options">
                            <i class="fa-solid fa-ellipsis"></i>
                        </button>
                    </div>

                    <div class="status-chart-container">
                        <canvas id="vehicleChart"></canvas>
                    </div>

                    <div class="vehicle-legend">
                        <div>
                            <span><i class="legend-dot available"></i>Available</span>
                            <strong>18</strong>
                        </div>

                        <div>
                            <span><i class="legend-dot rented"></i>Rented</span>
                            <strong>10</strong>
                        </div>

                        <div>
                            <span><i class="legend-dot reserved"></i>Reserved</span>
                            <strong>2</strong>
                        </div>
                    </div>

                </div>
            </div>

        </section>


        <!-- TABLES AND ALERTS -->
        <section class="row g-3 mb-4">

            <!-- RECENT BOOKINGS -->
            <div class="col-12 col-xl-8">
                <div class="content-card">

                    <div class="card-heading">
                        <div>
                            <h5>Recent Bookings</h5>
                            <p>Latest customer reservations</p>
                        </div>

                        <a href="#" class="view-all">View All</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table dashboard-table align-middle">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Pickup Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>
                                        <div class="customer-cell">
                                            <div class="customer-avatar avatar-1">CD</div>
                                            <span>Dara Chan</span>
                                        </div>
                                    </td>
                                    <td>Toyota Camry</td>
                                    <td>14 Oct 2026</td>
                                    <td>$120</td>
                                    <td><span class="status-badge status-confirmed">Confirmed</span></td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="customer-cell">
                                            <div class="customer-avatar avatar-2">SC</div>
                                            <span>Sokha Chea</span>
                                        </div>
                                    </td>
                                    <td>Honda Civic</td>
                                    <td>15 Oct 2026</td>
                                    <td>$95</td>
                                    <td><span class="status-badge status-pending">Pending</span></td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="customer-cell">
                                            <div class="customer-avatar avatar-3">JL</div>
                                            <span>John Lim</span>
                                        </div>
                                    </td>
                                    <td>Ford Ranger</td>
                                    <td>16 Oct 2026</td>
                                    <td>$180</td>
                                    <td><span class="status-badge status-confirmed">Confirmed</span></td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="customer-cell">
                                            <div class="customer-avatar avatar-4">KV</div>
                                            <span>Kanha Vann</span>
                                        </div>
                                    </td>
                                    <td>Hyundai Tucson</td>
                                    <td>17 Oct 2026</td>
                                    <td>$150</td>
                                    <td><span class="status-badge status-cancelled">Cancelled</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>


            <!-- ALERTS -->
            <div class="col-12 col-xl-4">
                <div class="content-card">

                    <div class="card-heading">
                        <div>
                            <h5>Notifications</h5>
                            <p>Things that need attention</p>
                        </div>

                        <span class="notification-count">3</span>
                    </div>

                    <div class="notification-item">
                        <div class="notification-icon notification-red">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="notification-text">
                            <h6>Pending Reservations</h6>
                            <p>3 bookings need confirmation.</p>
                            <small>10 minutes ago</small>
                        </div>
                    </div>

                    <div class="notification-item">
                        <div class="notification-icon notification-orange">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </div>
                        <div class="notification-text">
                            <h6>Maintenance Reminder</h6>
                            <p>2 vehicles need servicing.</p>
                            <small>30 minutes ago</small>
                        </div>
                    </div>

                    <div class="notification-item">
                        <div class="notification-icon notification-blue">
                            <i class="fa-solid fa-car-side"></i>
                        </div>
                        <div class="notification-text">
                            <h6>Vehicle Return</h6>
                            <p>One rental is due for return.</p>
                            <small>1 hour ago</small>
                        </div>
                    </div>

                    <a href="#" class="view-all notifications-link">View All Notifications</a>

                </div>
            </div>

        </section>


        <!-- FOOTER -->
        <footer class="dashboard-footer">
            <p>© 2026 CarRental Management System. All rights reserved.</p>
            <p>Admin Dashboard</p>
        </footer>

    </main>
</div>


<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Mobile sidebar
    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.getElementById("sidebar");

    menuToggle.addEventListener("click", function () {
        sidebar.classList.toggle("show");
    });

    // Revenue chart
    const revenueCanvas = document.getElementById("revenueChart");

    new Chart(revenueCanvas, {
        type: "line",

        data: {
            labels: [
                "Jan", "Feb", "Mar", "Apr", "May", "Jun",
                "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
            ],

            datasets: [{
                label: "Revenue",
                data: [
                    1800, 2400, 2100, 3200, 2800, 3900,
                    3500, 4200, 3800, 4600, 4300, 5200
                ],

                borderColor: "#3b82f6",
                backgroundColor: "rgba(59, 130, 246, 0.12)",
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 3,
                pointBackgroundColor: "#3b82f6"
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: false
                }
            },

            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "#edf0f5"
                    },
                    ticks: {
                        callback: function (value) {
                            return "$" + value.toLocaleString();
                        }
                    }
                },

                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });


    // Vehicle status chart
    const vehicleCanvas = document.getElementById("vehicleChart");

    new Chart(vehicleCanvas, {
        type: "doughnut",

        data: {
            labels: ["Available", "Rented", "Reserved"],

            datasets: [{
                data: [18, 10, 2],

                backgroundColor: [
                    "#10b981",
                    "#3b82f6",
                    "#f59e0b"
                ],

                borderWidth: 3,
                borderColor: "#ffffff",
                hoverOffset: 6
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "72%",

            plugins: {
                legend: {
                    display: false
                }
            }
        },

        plugins: [{
            id: "centerText",

            afterDraw(chart) {
                const { ctx, chartArea } = chart;

                if (!chartArea) return;

                const centerX = (chartArea.left + chartArea.right) / 2;
                const centerY = (chartArea.top + chartArea.bottom) / 2;

                ctx.save();
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";

                ctx.font = "bold 27px Arial";
                ctx.fillStyle = "#1e293b";
                ctx.fillText("30", centerX, centerY - 7);

                ctx.font = "12px Arial";
                ctx.fillStyle = "#64748b";
                ctx.fillText("Vehicles", centerX, centerY + 16);

                ctx.restore();
            }
        }]
    });
</script>

</body>
</html>