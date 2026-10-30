<?php
require_once __DIR__ . '/../config/bootstrap.php';
header('Location: ' . BASE_URL . (Auth::check() ? '/dashboard.php' : '/login.php'));
exit;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="main">
        <h1>Login</h1>
        <hr>
        <form action="" method="post">
            <input type="text" name="username" class="form-control" placeholder="Username...">
            <input type="password" name="password" class="form-control" placeholder="Password...">
            <input type="submit" value="Login" class="btn btn-primary">
        </form>
    </div>
</body>

</html>