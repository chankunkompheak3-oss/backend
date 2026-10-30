<?php
$menu = [
    ['dashboard',    'dashboard.php',             'fa-chart-line',     'Dashboard',           false],
    ['vehicles',     'vehicle_management.php',    'fa-car',            'Vehicle Management',  false],
    ['categories',   'category_management.php',   'fa-layer-group',    'Vehicle Categories',  false],
    ['customers',    'customer_management.php',   'fa-users',          'Customer Management', false],
    ['reservations', 'reservation_management.php','fa-calendar-check','Reservations',        false],
    ['staff',        'staff_management.php',      'fa-user-tie',       'Staff Management',    true],
    ['reports',      'report.php',                'fa-chart-pie',      'Reports',             false],
    ['settings',     'setting.php',               'fa-gear',           'Settings',            false],
];
?>
<aside class="left-bar" id="sidebar">
    <div class="logo">
        <img src="<?= BASE_URL ?>/images/logo1.png" alt="Car Rental Logo">
        <h1>Car Rental<br>Management</h1>
    </div>
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <?php foreach ($menu as [$key, $file, $icon, $label, $adminOnly]): ?>
                <?php if ($adminOnly && $user['role'] !== 'Admin') { continue; } ?>
                <li class="nav-item">
                    <a class="nav-link <?= $page === $key ? 'active' : '' ?>" href="<?= BASE_URL ?>/<?= $file ?>">
                        <i class="fa-solid <?= $icon ?>"></i><span><?= $e($label) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <div class="sidebar_profile">
        <div class="profile-avartar"><i class="fa-solid fa-user"></i></div>
        <div>
            <h4><?= $e($user['username']) ?></h4>
            <small><?= $e($roleLabel) ?></small>
        </div>
        <button type="button" id="logoutBtn" class="logout-btn" title="Log out" aria-label="Log out">
            <i class="fa-solid fa-right-from-bracket"></i>
        </button>
    </div>
</aside>