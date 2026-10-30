<?php
require_once __DIR__ . '/../config/bootstrap.php';
Auth::requirePage();
$page = 'reservations'; $title = 'Reservations'; $subtitle = 'Book vehicles, approve requests and record payments.';
$needCrud = true; $scripts = ['reservations.js'];
require APP_PATH . '/Views/layout/header.php';
require APP_PATH . '/Views/pages/reservations.php';
require APP_PATH . '/Views/layout/footer.php';