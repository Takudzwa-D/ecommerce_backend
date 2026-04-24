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

}catch(PDOException $e){

    http_response_code(500);
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

