<?php

/**
 * Request Helper Functions
 * Handles JSON parsing, query parameters, and request utilities
 */

/**
 * Get JSON input from request body
 * @return array Decoded JSON data or empty array
 */
function getJsonInput() {
    $data = json_decode(file_get_contents("php://input"), true);
    return $data ? $data : [];
}

/**
 * Get query parameter from URL
 * @param string $key Parameter name
 * @param mixed $default Default value if not found
 * @return mixed Parameter value or default
 */
function getQuery($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Get all query parameters
 * @return array Query parameters
 */
function getAllQuery() {
    return $_GET;
}

/**
 * Get HTTP header value
 * @param string $key Header name
 * @param mixed $default Default value if not found
 * @return mixed Header value or default
 */
function getHeader($key, $default = null) {
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
    return $_SERVER[$key] ?? $default;
}

/**
 * Get Authorization Bearer token from headers
 * @return string|null Token or null if not found
 */
function getBearerToken() {
    $header = getHeader('Authorization');
    if (!$header) {
        return null;
    }
    if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Get current HTTP request method
 * @return string Request method (GET, POST, PUT, DELETE, etc.)
 */
function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'];
}




