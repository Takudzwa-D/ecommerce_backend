<?php

/**
 * Register Controller
 * Handles user registration
 * POST /api/auth/register
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

// Extract and sanitize input
$firstName = sanitizeString($data['firstName'] ?? '');
$lastName = sanitizeString($data['lastName'] ?? '');
$email = sanitizeString($data['email'] ?? '');
$password = $data['password'] ?? '';
$phoneNumber = sanitizeString($data['phoneNumber'] ?? '');
$address = sanitizeString($data['address'] ?? '');
$city = sanitizeString($data['city'] ?? '');
$country = sanitizeString($data['country'] ?? '');

// Validate required fields
$validation = validateRequired([
    'firstName' => $firstName,
    'lastName' => $lastName,
    'email' => $email,
    'password' => $password
], ['firstName', 'lastName', 'email', 'password']);

if (!$validation['valid']) {
    errorResponse('Missing required fields: ' . implode(', ', $validation['missing']), null, HTTP_BAD_REQUEST);
}

// Validate email format
if (!isValidEmail($email)) {
    errorResponse('Invalid email format', null, HTTP_BAD_REQUEST);
}

// Validate password strength
if (!isValidPassword($password)) {
    errorResponse('Password must be at least 6 characters', null, HTTP_BAD_REQUEST);
}

// Validate phone if provided
if (!isEmptyField($phoneNumber) && !isValidPhoneNumber($phoneNumber)) {
    errorResponse('Invalid phone number format', null, HTTP_BAD_REQUEST);
}

try {
    $userModel = new User($conn);
    
    // Check if email already exists
    $existingUser = $userModel->findByEmail($email);
    if ($existingUser) {
        errorResponse('Email already registered', null, HTTP_CONFLICT);
    }
    
    // Create new user (default role is Customer)
    $userId = $userModel->create(
        $firstName,
        $lastName,
        $email,
        $password,
        $phoneNumber,
        $address,
        $city,
        $country,
        ROLE_CUSTOMER // Default role
    );
    
    if (!$userId) {
        errorResponse('Failed to create user account', null, HTTP_INTERNAL_ERROR);
    }
    
    // Get created user
    $newUser = $userModel->findById($userId);
    
    // Generate JWT token
    $token = generateToken([
        'id' => $newUser['id'],
        'email' => $newUser['Email'],
        'firstName' => $newUser['FirstName'],
        'lastName' => $newUser['LastName'],
        'role' => $newUser['Role']
    ]);
    
    // Prepare user response (without password)
    $userResponse = [
        'id' => $newUser['id'],
        'firstName' => $newUser['FirstName'],
        'lastName' => $newUser['LastName'],
        'email' => $newUser['Email'],
        'phoneNumber' => $newUser['PhoneNumber'],
        'address' => $newUser['Address'],
        'city' => $newUser['City'],
        'country' => $newUser['Country'],
        'role' => $newUser['Role'],
        'createdAt' => $newUser['CreatedAt']
    ];
    
    // Return success with token
    createdResponse(SUCCESS_REGISTERED, [
        'user' => $userResponse,
        'token' => $token
    ]);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
