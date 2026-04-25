<?php

/**
 * Update Product Controller
 * Updates product details (admin only)
 * PUT /api/products/update?id=1
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Product.php";
require_once __DIR__ . "/../../middleware/require_admin.php";

// Require admin authentication
requireAdmin();

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
    
    // Verify product exists
    $productModel = new Product($conn);
    $product = $productModel->getById($productId);
    if (!$product) {
        errorResponse('Product not found', null, HTTP_NOT_FOUND);
    }
    
    // Get JSON input
    $data = getJsonInput();
    
    // Extract and sanitize input
    $name = isset($data['name']) ? sanitizeString($data['name']) : null;
    $description = isset($data['description']) ? sanitizeString($data['description']) : null;
    $price = isset($data['price']) ? sanitizeFloat($data['price']) : null;
    $stockQuantity = isset($data['stockQuantity']) ? sanitizeInt($data['stockQuantity']) : null;
    
    // Validate values if provided
    if ($price !== null && !isPositive($price)) {
        errorResponse('Price must be greater than 0', null, HTTP_BAD_REQUEST);
    }
    
    if ($stockQuantity !== null && $stockQuantity < 0) {
        errorResponse('Stock quantity cannot be negative', null, HTTP_BAD_REQUEST);
    }
    
    // Update product
    $success = $productModel->update(
        $productId,
        $name,
        $description,
        $price,
        $stockQuantity
    );
    
    if (!$success) {
        errorResponse('Failed to update product', null, HTTP_INTERNAL_ERROR);
    }
    
    // Get updated product
    $updatedProduct = $productModel->getById($productId);
    
    successResponse(SUCCESS_UPDATED, $updatedProduct);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
