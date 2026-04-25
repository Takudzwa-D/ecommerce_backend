<?php

/**
 * Admin Authorization Middleware
 * Ensures user is authenticated and has admin role
 */

require_once __DIR__ . '/require_auth.php';
require_once __DIR__ . '/../config/constance.php';

/**
 * Require admin role
 * Checks if user is authenticated and has admin role, returns error if not
 * @return void Exits on authorization failure
 */
function requireAdmin() {
    // First check authentication
    requireAuth();
    
    $user = getAuthUser();
    
    if (!$user || $user['Role'] !== ROLE_ADMIN) {
        forbiddenResponse('Admin privileges required to access this resource.');
    }
}
