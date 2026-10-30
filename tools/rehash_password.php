<?php
// Run once:  C:\xampp\php\php.exe C:\xampp\htdocs\backend\tools\rehash_passwords.php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}
require_once __DIR__ . '/../config/bootstrap.php';

$pdo    = Database::connect();
$rows   = $pdo->query('SELECT user_id, password FROM users')->fetchAll();
$update = $pdo->prepare('UPDATE users SET password = :p WHERE user_id = :id');
$count  = 0;

foreach ($rows as $row) {
    // algo is null when the value is not already a password hash
    if (password_get_info($row['password'])['algo'] === null) {
        $update->execute([
            ':p'  => password_hash($row['password'], PASSWORD_DEFAULT),
            ':id' => $row['user_id'],
        ]);
        $count++;
    }
}
echo "Hashed $count password(s).\n";