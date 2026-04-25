<?php

/**
 * Authentication Middleware
 * Ensures user is authenticated before accessing protected routes
 */

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/response.php';

/**
 * Require authentication
 * Checks if user is authenticated, returns error if not
 * @return void Exits on authentication failure
 */
function requireAuth() {
    // Get user from token
    $user = getCurrentUser();
    
    if (!$user || empty($user['id'])) {
        unauthorizedResponse('Authentication required. Please provide a valid token.');
    }
    
    // Store user in global scope for controller access
    $GLOBALS['auth_user'] = $user;
}

/**
 * Get authenticated user
 * @return array User data
 */
function getAuthUser() {
    return $GLOBALS['auth_user'] ?? null;
}
