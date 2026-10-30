<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// Simple autoloader: new User() loads app/Models/User.php, etc.
spl_autoload_register(function (string $class): void {
    foreach (['Core', 'Models', 'Controllers'] as $dir) {
        $file = APP_PATH . "/$dir/$class.php";
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

// Never print PHP errors into the page (they would break JSON). Log them instead.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Any uncaught error: log details, show a safe message.
set_exception_handler(function (Throwable $e): void {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    $message = APP_DEBUG ? $e->getMessage() : 'Server error. Please try again.';
    if (defined('IS_API')) {
        Response::error($message, 500);
    }
    http_response_code(500);
    echo 'Server error.';
    exit;
});

// Keep session files inside the project (outside public/, and blocked from the web).
$sessionDir = dirname(__DIR__) . '/storage/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0777, true);
    @file_put_contents(dirname(__DIR__) . '/storage/.htaccess', "Require all denied\n");
}
if (is_dir($sessionDir) && is_writable($sessionDir)) {
    session_save_path($sessionDir);
}

// Safe session settings
if (session_status() === PHP_SESSION_NONE) {
    session_name('CARRENTALSESS');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => !empty($_SERVER['HTTPS']),
        'samesite' => 'Lax',
    ]);
    session_start();
}