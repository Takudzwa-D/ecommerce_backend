<?php

/**
 * Validation Helper Functions
 * Provides validation utilities for user input and data validation
 */

/**
 * Check if field is empty
 * @param mixed $value Value to check
 * @return bool True if empty, false otherwise
 */
function isEmptyField($value){
    return !isset($value) || trim($value) === '';
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool True if valid email format
 */
function isValidEmail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number format
 * @param string $phone Phone number to validate
 * @return bool True if valid phone format
 */
function isValidPhoneNumber($phone){
    // Allow numbers, spaces, dashes, parentheses, and plus sign
    return preg_match('/^[+]?[(]?[0-9]{1,4}[)]?[-\s.]?[(]?[0-9]{1,4}[)]?[-\s.]?[0-9]{1,9}$/', $phone);
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return bool True if password meets minimum requirements (6+ chars)
 */
function isValidPassword($password){
    return strlen($password) >= 6;
}

/**
 * Validate numeric value
 * @param mixed $value Value to check
 * @return bool True if numeric
 */
function isNumeric($value) {
    return is_numeric($value);
}

/**
 * Validate positive number
 * @param mixed $value Value to check
 * @return bool True if positive number
 */
function isPositive($value) {
    return is_numeric($value) && $value > 0;
}

/**
 * Validate string length
 * @param string $value String to check
 * @param int $min Minimum length
 * @param int $max Maximum length
 * @return bool True if within range
 */
function isValidLength($value, $min = 0, $max = PHP_INT_MAX) {
    $length = strlen($value);
    return $length >= $min && $length <= $max;
}

/**
 * Validate enum value
 * @param mixed $value Value to check
 * @param array $allowed Allowed values
 * @return bool True if value is in allowed array
 */
function isValidEnum($value, $allowed) {
    return in_array($value, $allowed, true);
}

/**
 * Sanitize string input
 * @param string $value String to sanitize
 * @return string Sanitized string
 */
function sanitizeString($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize integer input
 * @param mixed $value Value to sanitize
 * @return int Sanitized integer
 */
function sanitizeInt($value) {
    return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Sanitize float input
 * @param mixed $value Value to sanitize
 * @return float Sanitized float
 */
function sanitizeFloat($value) {
    return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/**
 * Validate required fields exist in array
 * @param array $data Data array
 * @param array $required Required field names
 * @return array Validation result ['valid' => bool, 'missing' => array]
 */
function validateRequired($data, $required = []) {
    $missing = [];
    foreach ($required as $field) {
        if (isEmptyField($data[$field] ?? '')) {
            $missing[] = $field;
        }
    }
    return [
        'valid' => count($missing) === 0,
        'missing' => $missing
    ];
}