<?php
// Database settings live ONLY in backend/pdo_connection.php.

// ---- Paths and URLs ----
define('APP_PATH',   dirname(__DIR__) . '/app');
define('BASE_URL',   '/Car-Rental-System/backend/public');
define('UPLOAD_DIR', dirname(__DIR__) . '/public/images/vehicles/');
define('UPLOAD_URL', BASE_URL . '/images/vehicles/');

// ---- Security ----
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCK_MINUTES', 15);

// Set to false when the site is shown to anyone else.
define('APP_DEBUG', true);

date_default_timezone_set('Asia/Phnom_Penh');