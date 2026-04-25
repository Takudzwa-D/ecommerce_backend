<?php

/**
 * Login Controller
 * Handles user login authentication
 * POST /api/auth/login
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../helpers/auth.php";
require_once __DIR__ . "/../../models/User.php";

// Get JSON input
$data = getJsonInput();

// Validate required fields
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// Check empty fields
if (isEmptyField($email) || isEmptyField($password)) {
    errorResponse('Email and password are required', null, HTTP_BAD_REQUEST);
}

// Validate email format
if (!isValidEmail($email)) {
    errorResponse('Invalid email format', null, HTTP_BAD_REQUEST);
}

try {
    // Find user by email
    $userModel = new User($conn);
    $user = $userModel->findByEmail($email);
    
    if (!$user) {
        errorResponse('Invalid email or password', null, HTTP_UNAUTHORIZED);
    }
    
    // Verify password
    if (!password_verify($password, $user['Password'])) {
        errorResponse('Invalid email or password', null, HTTP_UNAUTHORIZED);
    }
    
    // Generate JWT token
    $token = generateToken([
        'id' => $user['id'],
        'email' => $user['Email'],
        'firstName' => $user['FirstName'],
        'lastName' => $user['LastName'],
        'role' => $user['Role']
    ]);
    
    // Prepare user response (without password)
    $userResponse = [
        'id' => $user['id'],
        'firstName' => $user['FirstName'],
        'lastName' => $user['LastName'],
        'email' => $user['Email'],
        'phoneNumber' => $user['PhoneNumber'],
        'address' => $user['Address'],
        'city' => $user['City'],
        'country' => $user['Country'],
        'role' => $user['Role'],
        'createdAt' => $user['CreatedAt']
    ];
    
    // Return success with token
    successResponse(SUCCESS_LOGIN, [
        'user' => $userResponse,
        'token' => $token
    ]);
    
} catch (Exception $e) {
    http_response_code(HTTP_INTERNAL_ERROR);
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
