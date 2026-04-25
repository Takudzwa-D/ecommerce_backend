<?php

/**
 * Profile Controller
 * Retrieves and updates authenticated user profile
 * GET /api/auth/profile - Get current user profile
 * PUT /api/auth/profile - Update current user profile
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../helpers/auth.php";
require_once __DIR__ . "/../../models/User.php";
require_once __DIR__ . "/../../middleware/require_auth.php";

// Require authentication
requireAuth();
$user = getAuthUser();

try {
    $method = getRequestMethod();
    $userModel = new User($conn);
    
    if ($method === 'GET') {
        // Get user profile
        $currentUser = $userModel->findById($user['id']);
        
        if (!$currentUser) {
            notFoundResponse('User not found');
        }
        
        // Prepare response (without password)
        $response = [
            'id' => $currentUser['id'],
            'firstName' => $currentUser['FirstName'],
            'lastName' => $currentUser['LastName'],
            'email' => $currentUser['Email'],
            'phoneNumber' => $currentUser['PhoneNumber'],
            'address' => $currentUser['Address'],
            'city' => $currentUser['City'],
            'country' => $currentUser['Country'],
            'role' => $currentUser['Role'],
            'createdAt' => $currentUser['CreatedAt'],
            'updatedAt' => $currentUser['UpdatedAt']
        ];
        
        successResponse('Profile retrieved successfully', $response);
        
    } elseif ($method === 'PUT') {
        // Update user profile
        $data = getJsonInput();
        
        // Extract and sanitize input
        $firstName = isset($data['firstName']) ? sanitizeString($data['firstName']) : null;
        $lastName = isset($data['lastName']) ? sanitizeString($data['lastName']) : null;
        $phoneNumber = isset($data['phoneNumber']) ? sanitizeString($data['phoneNumber']) : null;
        $address = isset($data['address']) ? sanitizeString($data['address']) : null;
        $city = isset($data['city']) ? sanitizeString($data['city']) : null;
        $country = isset($data['country']) ? sanitizeString($data['country']) : null;
        
        // Validate phone if provided
        if ($phoneNumber && !isValidPhoneNumber($phoneNumber)) {
            errorResponse('Invalid phone number format', null, HTTP_BAD_REQUEST);
        }
        
        // Update user
        $success = $userModel->update(
            $user['id'],
            $firstName,
            $lastName,
            $phoneNumber,
            $address,
            $city,
            $country
        );
        
        if (!$success) {
            errorResponse('Failed to update profile', null, HTTP_INTERNAL_ERROR);
        }
        
        // Get updated user
        $updatedUser = $userModel->findById($user['id']);
        
        // Prepare response (without password)
        $response = [
            'id' => $updatedUser['id'],
            'firstName' => $updatedUser['FirstName'],
            'lastName' => $updatedUser['LastName'],
            'email' => $updatedUser['Email'],
            'phoneNumber' => $updatedUser['PhoneNumber'],
            'address' => $updatedUser['Address'],
            'city' => $updatedUser['City'],
            'country' => $updatedUser['Country'],
            'role' => $updatedUser['Role'],
            'createdAt' => $updatedUser['CreatedAt'],
            'updatedAt' => $updatedUser['UpdatedAt']
        ];
        
        successResponse(SUCCESS_UPDATED, $response);
        
    } else {
        errorResponse('Method not allowed', null, HTTP_BAD_REQUEST);
    }
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
