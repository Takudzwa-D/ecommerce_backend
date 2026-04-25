<?php

/**
 * Response Helper Functions
 * Provides standardized JSON response formatting for the API
 */

/**
 * Send standardized JSON response
 * @param bool $success Request success status
 * @param string $message Response message
 * @param mixed $data Response data (optional)
 * @param int $httpCode HTTP status code (optional)
 * @return void Outputs JSON and exits
 */
function jsonResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    header("Content-Type: application/json");
    
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Send success response
 * @param string $message Success message
 * @param mixed $data Response data (optional)
 * @param int $httpCode HTTP status code (default: 200)
 * @return void Outputs JSON and exits
 */
function successResponse($message, $data = null, $httpCode = 200) {
    jsonResponse(true, $message, $data, $httpCode);
}

/**
 * Send error response
 * @param string $message Error message
 * @param mixed $data Additional error data (optional)
 * @param int $httpCode HTTP status code (default: 400)
 * @return void Outputs JSON and exits
 */
function errorResponse($message, $data = null, $httpCode = 400) {
    jsonResponse(false, $message, $data, $httpCode);
}

/**
 * Send success response for created resource
 * @param string $message Success message
 * @param mixed $data Created resource data
 * @return void Outputs JSON and exits
 */
function createdResponse($message, $data = null) {
    jsonResponse(true, $message, $data, 201);
}

/**
 * Send unauthorized response
 * @param string $message Error message (optional)
 * @return void Outputs JSON and exits
 */
function unauthorizedResponse($message = 'Unauthorized access') {
    jsonResponse(false, $message, null, 401);
}

/**
 * Send forbidden response
 * @param string $message Error message (optional)
 * @return void Outputs JSON and exits
 */
function forbiddenResponse($message = 'Access denied') {
    jsonResponse(false, $message, null, 403);
}

/**
 * Send not found response
 * @param string $message Error message (optional)
 * @return void Outputs JSON and exits
 */
function notFoundResponse($message = 'Resource not found') {
    jsonResponse(false, $message, null, 404);
}

/**
 * Send validation error response
 * @param array $errors Validation errors
 * @return void Outputs JSON and exits
 */
function validationErrorResponse($errors = []) {
    jsonResponse(false, 'Validation failed', ['errors' => $errors], 400);
}

/**
 * Send server error response
 * @param string $message Error message (optional)
 * @return void Outputs JSON and exits
 */
function serverErrorResponse($message = 'Internal server error') {
    jsonResponse(false, $message, null, 500);
}