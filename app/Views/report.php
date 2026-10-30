<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../../public/index.php");
    exit;
}
$username = $_SESSION['username'];

require_once __DIR__ . "/../Controllers/ReportController.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- link to dashboard.css -->
    <link rel="stylesheet" href="../../public/css/reservation_management.css">
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</head>
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
                    <a class="nav-link" href="dashboard.php">
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
                    <a class="nav-link" href="vehicle_categories.php">
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
                    <a class="nav-link active" href="reservation_management.php">
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
                    <a class="nav-link active" href="report.php">
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
                    <?php
                        echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
                    ?>
                </h4>
                <small>Administrator</small>
            </div>
        </div>
    </aside>

</body>
</html>