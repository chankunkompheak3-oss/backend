<?php
session_start();

require_once __DIR__ . "/../app/Controllers/UsersController.php";
$err = "";
$user = new UserController($con);
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $userLogin = $_POST['username'] ?? '';
    $passwordLogin = $_POST['password'] ?? '';
    $result = $user->getUser($userLogin);
    if($result){
        $row = $result[0];
        if($userLogin === $row["username"] && $passwordLogin === $row["password"]){
            $_SESSION['username'] = $userLogin;
            header("Location: ../app/Views/dashboard.php");
            exit;
        }else{
            $err = '<div class = "alert alert-danger">Invalid username or password</div>';
        }
    }else{
        $err = '<div class "alert alert-danger role = "alert">User not Found</div>';
    }

}
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