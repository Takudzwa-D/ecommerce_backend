<?php

/**
 * Logout Controller
 * Handles user logout
 * POST /api/auth/logout
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/auth.php";
require_once __DIR__ . "/../../middleware/require_auth.php";

// Require authentication
requireAuth();

// Logout is handled client-side by removing the token
// Server-side logout can be implemented with token blacklisting if needed
// For now, we'll just return a success response

successResponse('Logout successful. Please remove the token from your client.');
