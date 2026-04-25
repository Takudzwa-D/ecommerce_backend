<?php

/**
 * Get Single Product Controller
 * Retrieves a specific product by ID
 * GET /api/products/get?id=1
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Product.php";

try {
    // Get product ID from query parameters
    $productId = getQuery('id');
    
    if (isEmptyField($productId)) {
        errorResponse('Product ID is required', null, HTTP_BAD_REQUEST);
    }
    
    $productId = sanitizeInt($productId);
    if (!isPositive($productId)) {
        errorResponse('Invalid product ID', null, HTTP_BAD_REQUEST);
    }
    
    // Get product
    $productModel = new Product($conn);
    $product = $productModel->getById($productId);
    
    if (!$product) {
        errorResponse('Product not found', null, HTTP_NOT_FOUND);
    }
    
    successResponse('Product retrieved successfully', $product);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
