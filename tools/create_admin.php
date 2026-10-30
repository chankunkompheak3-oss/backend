<?php
// Usage (XAMPP Shell or CMD):
//   C:\xampp\php\php.exe C:\xampp\htdocs\backend\tools\create_admin.php admin "YourStrongPass#1"
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}
require_once __DIR__ . '/../config/bootstrap.php';

$username = $argv[1] ?? '';
$password = $argv[2] ?? '';
if ($username === '' || strlen($password) < 8) {
    exit("Usage: php create_admin.php <username> <password (min 8 chars)>\n");
}

$pdo  = Database::connect();
$stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = :u');
$stmt->execute([':u' => $username]);
if ($stmt->fetch()) {
    exit("User '$username' already exists.\n");
}

$pdo->prepare("INSERT INTO users (username, password, role, status)
               VALUES (:u, :p, 'Admin', 'Active')")
    ->execute([':u' => $username, ':p' => password_hash($password, PASSWORD_DEFAULT)]);

echo "Admin '$username' created.\n";