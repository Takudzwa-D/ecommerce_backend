<?php

/**
 * Authentication Helper Functions
 * Handles JWT token generation, validation, and user authentication
 */

// JWT Secret Key - Should be stored in environment variable in production
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production-12345');

/**
 * Generate JWT Token
 * @param array $payload Data to encode in token
 * @param int $expiresIn Token expiration time in seconds (default: 24 hours)
 * @return string JWT token
 */
function generateToken($payload, $expiresIn = 86400) {
    // Header
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    
    // Payload with expiration
    $payload['iat'] = time();
    $payload['exp'] = time() + $expiresIn;
    $payload = json_encode($payload);
    
    // Encode to base64url
    $header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
    $payload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    
    // Create signature
    $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    
    return "$header.$payload.$signature";
}

/**
 * Verify and decode JWT token
 * @param string $token JWT token to verify
 * @return array|null Decoded payload or null if invalid
 */
function verifyToken($token) {
    if (empty($token)) {
        return null;
    }
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    
    list($header, $payload, $signature) = $parts;
    
    // Verify signature
    $expectedSignature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $expectedSignature = rtrim(strtr(base64_encode($expectedSignature), '+/', '-_'), '=');
    
    if ($signature !== $expectedSignature) {
        return null;
    }
    
    // Decode payload
    $payloadDecoded = json_decode(
        base64_decode(strtr($payload, '-_', '+/')),
        true
    );
    
    if (!$payloadDecoded) {
        return null;
    }
    
    // Check expiration
    if (isset($payloadDecoded['exp']) && $payloadDecoded['exp'] < time()) {
        return null; // Token expired
    }
    
    return $payloadDecoded;
}

/**
 * Get current authenticated user from token
 * @return array|null User data or null if not authenticated
 */
function getCurrentUser() {
    $token = getBearerToken();
    if (!$token) {
        return null;
    }
    
    $payload = verifyToken($token);
    return $payload;
}

/**
 * Check if user is authenticated
 * @return bool True if authenticated
 */
function isAuthenticated() {
    return getCurrentUser() !== null;
}

/**
 * Hash password for storage
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password against hash
 * @param string $password Plain text password to check
 * @param string $hash Password hash to verify against
 * @return bool True if password matches hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
