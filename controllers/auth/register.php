<?php

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/User.php";

$data = getJsonInput();

$firstName = $data['firstName'] ?? '';
$lastName = $data['lastName'] ?? '';
$role = $data['role'] ?? '';
$email = $data['email'] ?? '';
$phoneNumber = $data['phoneNumber'] ?? '';
$address = $data['address'] ?? '';
$city = $data['city'] ?? '';
$country = $data['country'] ?? '';
$password = $data['password'] ?? '';

if (isEmptyField($firstName) || isEmptyField($lastName) || isEmptyField($email) || isEmptyField($password) || isEmptyField($phoneNumber) || isEmptyField($address) || isEmptyField($city) || isEmptyField($country) || isEmptyField($role)) {
    jsonResponse(false, "All fields are required");
}

if (!isValidEmail($email)) {
    jsonResponse(false, "Invalid email format");
}

if (!isValidPassword($password)) {
    jsonResponse(false, "Password must be at least 6 characters");
}

if (!in_array($role, ['Admin', 'Customer'])) {
    jsonResponse(false, "Role must be either 'Admin' or 'Customer'");
}

$userModel = new User($conn);

$existingUser = $userModel->findByEmail($email);

if ($existingUser) {
    jsonResponse(false, "Email already exists");
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$created = $userModel->create($firstName, $lastName, $role, $email, $phoneNumber, $address, $city, $country, $hashedPassword);

if ($created) {
    jsonResponse(true, "Registration successful");
} else {
    jsonResponse(false, "Registration failed");
}
?>