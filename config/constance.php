<?php

/**
 * Application Constants
 * Define application-wide constants and configuration
 */

// Application Information
define('APP_NAME', 'AutoSpares eCommerce API');
define('APP_VERSION', '1.0.0');
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'AutoSpares');

// API Configuration
define('API_BASE_URL', getenv('API_BASE_URL') ?: 'http://localhost/ecommerce_backend');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/ecommerce_backend');

// Pagination
define('DEFAULT_PAGE_SIZE', 10);
define('MAX_PAGE_SIZE', 100);

// User Roles
define('ROLE_CUSTOMER', 'Customer');
define('ROLE_ADMIN', 'Admin');

// Order Statuses
define('ORDER_STATUS_PENDING', 'Pending');
define('ORDER_STATUS_COMPLETED', 'Completed');
define('ORDER_STATUS_FAILED', 'Failed');
define('ORDER_STATUS_CANCELLED', 'Cancelled');
define('ALLOWED_ORDER_STATUSES', [
    ORDER_STATUS_PENDING,
    ORDER_STATUS_COMPLETED,
    ORDER_STATUS_FAILED,
    ORDER_STATUS_CANCELLED
]);

// Payment Methods
define('PAYMENT_METHOD_CREDIT_CARD', 'Credit Card');
define('PAYMENT_METHOD_PAYNOW', 'PayNow');
define('PAYMENT_METHOD_BANK_TRANSFER', 'Bank Transfer');
define('ALLOWED_PAYMENT_METHODS', [
    PAYMENT_METHOD_CREDIT_CARD,
    PAYMENT_METHOD_PAYNOW,
    PAYMENT_METHOD_BANK_TRANSFER
]);

// Payment Statuses
define('PAYMENT_STATUS_PENDING', 'Pending');
define('PAYMENT_STATUS_COMPLETED', 'Completed');
define('PAYMENT_STATUS_FAILED', 'Failed');
define('ALLOWED_PAYMENT_STATUSES', [
    PAYMENT_STATUS_PENDING,
    PAYMENT_STATUS_COMPLETED,
    PAYMENT_STATUS_FAILED
]);

// HTTP Status Codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_CONFLICT', 409);
define('HTTP_INTERNAL_ERROR', 500);

// Error Messages
define('ERROR_UNAUTHORIZED', 'Unauthorized access');
define('ERROR_INVALID_CREDENTIALS', 'Invalid email or password');
define('ERROR_NOT_FOUND', 'Resource not found');
define('ERROR_INVALID_DATA', 'Invalid data provided');
define('ERROR_SERVER_ERROR', 'Internal server error');

// Success Messages
define('SUCCESS_CREATED', 'Resource created successfully');
define('SUCCESS_UPDATED', 'Resource updated successfully');
define('SUCCESS_DELETED', 'Resource deleted successfully');
define('SUCCESS_LOGIN', 'Login successful');
define('SUCCESS_REGISTERED', 'User registered successfully');
