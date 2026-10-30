<?php
require_once __DIR__ . '/../config/bootstrap.php';
Auth::requirePage();
if (Auth::user()['role'] !== 'Admin') { http_response_code(403); exit('403 - Administrators only.'); }
$page = 'staff'; $title = 'Staff Management'; $subtitle = 'Create accounts, assign roles and activate or deactivate staff.';
$needCrud = true; $scripts = ['staff.js'];
require APP_PATH . '/Views/layout/header.php';
require APP_PATH . '/Views/pages/staff.php';
require APP_PATH . '/Views/layout/footer.php';