<?php
require_once __DIR__ . '/../config/bootstrap.php';
Auth::requirePage();
$page = 'categories'; $title = 'Vehicle Categories'; $subtitle = 'Organise your fleet into categories.';
$needCrud = true; $scripts = ['categories.js'];
require APP_PATH . '/Views/layout/header.php';
require APP_PATH . '/Views/pages/categories.php';
require APP_PATH . '/Views/layout/footer.php';