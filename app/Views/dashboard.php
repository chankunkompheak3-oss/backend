<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../../public/index.php");
    exit;
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- link to dashboard.css -->
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!-- =========================
         Left Sidebar
    ========================= -->
    <aside class="left-bar">
        <div class="logo">
            <img src="../../public/images/logo1.png" alt="Car Rental Logo">
            <h1>Car Rental<br>Management</h1>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vehicle_management.php">
                        <i class="fa-solid fa-car"></i>
                        <span>Vehicle Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vehicle_management.php">
                        <i class="fa-solid fa-layer-group"></i>
                        <span>Vehicle Categories</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="customer_management.php">
                        <i class="fa-solid fa-users"></i>
                        <span>Customer Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservation_management.php">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Reservations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="staff_management.php">
                        <i class="fa-solid fa-user-tie"></i>
                        <span>Staff Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="report.php">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="setting.php">
                        <i class="fa-solid fa-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- Admin Profile -->
        <div class="sidebar_profile">
            <div class="profile-avartar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div>
                <h4>
                    <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>
                </h4>
                <small>Administrator</small>
            </div>
        </div>
    </aside>
    <!--
     main dashboard
      -->
    <main class="contain">

        <!-- Dashboard Header -->
        <header class="top-header">
            <div class="dashboard-intro">
                <h2>Dashboard</h2>
                <small>Welcome back! Here's your rental business overview.</small>
            </div>
            <div class="header-right">
                <span class="header-date">
                    <i class="fa-regular fa-calendar me-1"></i>
                    <?php echo date('d M Y'); ?>
                </span>
                <button type="button" class="notification-btn"
                        aria-label="Notifications" title="Notifications">
                    <i class="fa-regular fa-bell"></i>
                </button>
            </div>
        </header>
        <!-- =========================
             Statistics Cards
        ========================= -->
        <section class="dashboard-cards">
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p>Total Vehicles</p>
                        <h3>128</h3>
                    </div>
                    <div class="stat-icon icon-blue">
                        <i class="fa-solid fa-car"></i>
                    </div>
                </div>
                <span class="stat-change">
                    <i class="fa-solid fa-car-side me-1"></i>
                    Fleet overview
                </span>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p>Active Reservations</p>
                        <h3>42</h3>
                    </div>
                    <div class="stat-icon icon-green">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
                <span class="stat-change">
                    <i class="fa-solid fa-check me-1"></i>
                    Current bookings
                </span>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p>Total Customers</p>
                        <h3>356</h3>
                    </div>
                    <div class="stat-icon icon-orange">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <span class="stat-change">
                    <i class="fa-solid fa-user-plus me-1"></i>
                    Registered customers
                </span>
            </div>
            <div class="stat-card">
                <div class="stat-top">
                    <div>
                        <p>Total Revenue</p>
                        <h3>$8,450</h3>
                    </div>
                    <div class="stat-icon icon-purple">
                        <i class="fa-solid fa-dollar-sign"></i>
                    </div>
                </div>
                <span class="stat-change">
                    <i class="fa-solid fa-wallet me-1"></i>
                    Sample revenue
                </span>
            </div>
        </section>
        <!-- =========================
             Recent Reservations
        ========================= -->
        <section class="content-panel">
            <div class="panel-header">
                <h4>Recent Reservations</h4>
                <a href="#" class="view-all">
                    View All <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Pickup Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#BK1001</td>
                            <td>
                                <span class="customer-name">John Smith</span>
                            </td>
                            <td>Toyota Camry</td>
                            <td>20 Oct 2026</td>
                            <td>$120.00</td>
                            <td>
                                <span class="status status-confirmed">
                                    Confirmed
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>#BK1002</td>
                            <td>
                                <span class="customer-name">Sarah Lee</span>
                            </td>
                            <td>Honda Civic</td>
                            <td>21 Oct 2026</td>
                            <td>$95.00</td>
                            <td>
                                <span class="status status-pending">
                                    Pending
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>#BK1003</td>
                            <td>
                                <span class="customer-name">David Chen</span>
                            </td>
                            <td>BMW 5 Series</td>
                            <td>22 Oct 2026</td>
                            <td>$250.00</td>
                            <td>
                                <span class="status status-confirmed">
                                    Confirmed
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>#BK1004</td>
                            <td>
                                <span class="customer-name">Emily Wilson</span>
                            </td>
                            <td>Toyota Fortuner</td>
                            <td>23 Oct 2026</td>
                            <td>$180.00</td>
                            <td>
                                <span class="status status-pending">
                                    Pending
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>#BK1005</td>
                            <td>
                                <span class="customer-name">Michael Brown</span>
                            </td>
                            <td>Hyundai Tucson</td>
                            <td>24 Oct 2026</td>
                            <td>$150.00</td>
                            <td>
                                <span class="status status-cancelled">
                                    Cancelled
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        <!-- Footer -->
        <footer class="text-center text-secondary py-3">
            <small>
                &copy; <?php echo date('Y'); ?> Car Rental Management System.
                All rights reserved.
            </small>
        </footer>

    </main>
</body>
</html>
