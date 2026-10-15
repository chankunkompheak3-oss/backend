<?php
$db_server = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "car_rental_management";
try {
    $con = new PDO(
        "mysql:host=$db_server;dbname=$db_name",
        $db_user,
        $db_password
    );
    $con->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );
    echo "Database connected successfully!";
}
catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>