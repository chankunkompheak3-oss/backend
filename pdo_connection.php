<?php
    $db_server = "localhost";
    $db_user = "root";
    $db_password = "";
    $db_name = "car_rental_management";

    $con = new PDO("mysql:host=localhost;dbname=$db_name", $db_user, $db_password);
    try
    {
    $con = new PDO(
        "mysql:host=$db_server;dbname=$db_name",
        $db_user,
        $db_password
        );
        echo "Database connection successfully";
    }
    catch(PDOException $e)
    {
    echo "Database connection failed:" .  $e->getMessage();
    }
?>