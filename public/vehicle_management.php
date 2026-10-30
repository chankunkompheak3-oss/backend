<?php
require_once __DIR__ . '/../config/bootstrap.php';
Auth::requirePage();
$page = 'vehicles'; $title = 'Vehicle Management'; $subtitle = 'Add, edit and track every vehicle in your fleet.';
$needCrud = true; $scripts = ['vehicles.js'];
require APP_PATH . '/Views/layout/header.php';
require APP_PATH . '/Views/pages/vehicles.php';
require APP_PATH . '/Views/layout/footer.php';