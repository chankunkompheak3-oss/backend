<?php
require_once __DIR__ . '/../config/bootstrap.php';
Auth::requirePage();
$page = 'dashboard'; $title = 'Dashboard'; $subtitle = "Welcome back! Here's your rental business overview.";
$needCharts = true; $scripts = ['dashboard.js'];
require APP_PATH . '/Views/layout/header.php';
require APP_PATH . '/Views/pages/dashboard.php';
require APP_PATH . '/Views/layout/footer.php';