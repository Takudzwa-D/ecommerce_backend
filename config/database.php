<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "AutoSpares";

// Create connection

try{

    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to the database";

}catch(PDOException $e){


    echo "Connection failed: " . $e->getMessage();
}

