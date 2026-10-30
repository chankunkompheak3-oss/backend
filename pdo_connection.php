<?php
// The ONE place where the database connection is created.
// It sets $con (a PDO object). It prints nothing, so JSON responses stay valid.
$db_server   = "localhost";
$db_user     = "root";
$db_password = "";
$db_name     = "car_rental_management";

try {
    $con = new PDO(
        "mysql:host=$db_server;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4, time_zone = '+07:00'",
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    $msg = (defined('APP_DEBUG') && APP_DEBUG)
        ? 'Database connection failed: ' . $e->getMessage()
        : 'Database connection failed.';
    if (defined('IS_API')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $msg, 'errors' => []]);
    } else {
        echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    }
    exit;
}