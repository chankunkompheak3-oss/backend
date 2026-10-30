<?php
require_once __DIR__ . '/bootstrap.php';

$con = Database::connect();
// ---- Database (the ONLY place credentials live) ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'car_rental_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_TZ',   '+07:00');          // Cambodia, keeps NOW() equal to PHP time

// ---- Paths and URLs ----
define('APP_PATH',   dirname(__DIR__) . '/app');
define('BASE_URL',   '/backend/public');   // change if your folder path differs
define('UPLOAD_DIR', dirname(__DIR__) . '/public/images/vehicles/');
define('UPLOAD_URL', BASE_URL . '/images/vehicles/');

// ---- Security ----
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCK_MINUTES', 15);

// Set to false when the site is shown to anyone else.
define('APP_DEBUG', true);

date_default_timezone_set('Asia/Phnom_Penh');