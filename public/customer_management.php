<?php
require_once __DIR__ . '/../config/bootstrap.php';
Auth::requirePage();
$page = 'customers'; $title = 'Customer Management'; $subtitle = 'Register customers and review their rental history.';
$needCrud = true; $scripts = ['customers.js'];
require APP_PATH . '/Views/layout/header.php';
require APP_PATH . '/Views/pages/customers.php';
require APP_PATH . '/Views/layout/footer.php';