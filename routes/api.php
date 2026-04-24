<?php

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validator.php';

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

// Remove base path (ecommerce_backend) if present
$basePath = 'ecommerce_backend';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
    $uri = trim($uri, '/');
}

// Remove 'routes' from path if present
$uri = str_replace('routes/', '', $uri);

// Simple routing
$routes = [
    // Auth routes
    'api/auth/register' => ['controllers/auth/register.php', 'POST'],
    'api/auth/login' => ['controllers/auth/login.php', 'POST'],
    'api/auth/logout' => ['controllers/auth/logout.php', 'POST'],
    'api/auth/profile' => ['controllers/auth/profile.php', 'GET'],
    
    // Categories
    'api/categories' => ['controllers/categories/get_categories.php', 'GET'],
    'api/categories/products' => ['controllers/categories/get_category_product.php', 'GET'],
    'api/sub-categories' => ['controllers/categories/get_sub_categories.php', 'GET'],
    
    // Brands
    'api/brands' => ['controllers/brands/get_brands.php', 'GET'],
    'api/brands/models' => ['controllers/brands/get_models.php', 'GET'],
    'api/brands/products' => ['controllers/brands/get_vehicle_products.php', 'GET'],
    
    // Products
    'api/products' => ['controllers/products/get_products.php', 'GET'],
    'api/products/search' => ['controllers/products/search_products.php', 'GET'],
    'api/products/add' => ['controllers/products/add_product.php', 'POST'],
    'api/products/update' => ['controllers/products/upadate_product.php', 'PUT'],
    'api/products/delete' => ['controllers/products/delete_product.php', 'DELETE'],
    'api/products/upload-image' => ['controllers/products/upload_product_image.php', 'POST'],
    
    // Orders
    'api/orders' => ['controllers/orders/get_all_orders.php', 'GET'],
    'api/orders/my' => ['controllers/orders/get_my_orders.php', 'GET'],
    'api/orders/create' => ['controllers/orders/create_orders.php', 'POST'],
    'api/orders/details' => ['controllers/orders/get_order_details.php', 'GET'],
    'api/orders/update-status' => ['controllers/orders/update_order_status.php', 'PUT'],
    
    // Payments
    'api/payments/initiate' => ['controllers/payments/initiate_payment.php', 'POST'],
    'api/payments/result' => ['controllers/payments/payment_result.php', 'GET'],
    'api/payments/return' => ['controllers/payments/payment_return.php', 'GET'],
    'api/payments/status' => ['controllers/payments/payment_status.php', 'GET'],
    'api/payments/hook' => ['controllers/payments/paynow_hook.php', 'POST'],
];

// Match route
$matched = false;
foreach ($routes as $path => $config) {
    if ($uri === $path && $method === $config[1]) {
        require_once __DIR__ . '/../' . $config[0];
        $matched = true;
        break;
    }
}

if (!$matched) {
    jsonResponse(false, "Endpoint not found. URI: $uri, Method: $method");
}