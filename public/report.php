<?php
require_once __DIR__ . '/../config/bootstrap.php';
Auth::requirePage();
$page = 'reports'; $title = 'Reports'; $subtitle = 'Revenue, reservations, utilization and customer activity.';
$needCharts = true; $scripts = ['reports.js'];
require APP_PATH . '/Views/layout/header.php';
require APP_PATH . '/Views/pages/reports.php';
require APP_PATH . '/Views/layout/footer.php';