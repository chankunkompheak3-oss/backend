<?php
require_once __DIR__ . '/../config/bootstrap.php';
Auth::requirePage();
$page = 'settings'; $title = 'Settings'; $subtitle = 'Your profile, password and system configuration.';
$scripts = ['settings.js'];
require APP_PATH . '/Views/layout/header.php';
require APP_PATH . '/Views/pages/settings.php';
require APP_PATH . '/Views/layout/footer.php';