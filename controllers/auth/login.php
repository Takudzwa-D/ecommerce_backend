<?php

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/User.php";

$data = getJsonInput();

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (isEmptyField($email) || isEmptyField($password)) {
    jsonResponse(false, "Either email or password is missing");
}

if (!isValidEmail($email)) {
    jsonResponse(false, "Invalid email format");
}

$userModel = new User($conn);

$user = $userModel->findByEmail($email);

if (!$user) {
    jsonResponse(false, "User not found");
}

if (!password_verify($password, $user['password'])) {
    jsonResponse(false, "Invalid password");
}

unset($user['password']);

jsonResponse(true, "Login successful", $user);
?>