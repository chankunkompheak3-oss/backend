<?php
    session_start();
    // check users login
    if(!isset($_SESSION['username'])) {
      header("Location: ../../public/index.php");
      exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashBoard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/dashbord.css">
</head>
<body>

  <!-- left bar -->
   <div class="left-bar">
    <div class="logo">
      <img src="../../public/images/logo1.png" alt="logo">
      <h1>Car-Rental-Management</h1>
    </div>
    <div class="nav">
        <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Vihecle Management</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Vehecal Category</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Customer Management</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Reservations</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Staff Management</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Reports</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Settings</a>
        </li>
      </ul>
    <!-- Profile  -->
      <div class="sidebar_profile">
        <div class="profile-avartar">
          <i class="fa-solid fa-user"></i>
        </div>
        <div>
          <h4>Admin</h4>
          <small>Adminstration</small>
        </div>
      </div>
    </div>
</div>
<!-- contain_side -->
<div class="contain">
  <div class ="dashboard-intro">
      <h2>Dashboard</h2>
      <small>Wellcome to dashboard</small>
  </div>
  <div class="4_post">
    
  </div>
</div>
</body>
</html>