<?php

/**
 * Delete Product Controller
 * Deletes a product (admin only)
 * DELETE /api/products/delete?id=1
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../helpers/upload.php";
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
    
    // Delete product image if it exists
    if ($product['img']) {
        deleteProductImage($product['img']);
    }
    
    // Delete product
    $success = $productModel->delete($productId);
    
    if (!$success) {
        errorResponse('Failed to delete product', null, HTTP_INTERNAL_ERROR);
    }
    
    successResponse(SUCCESS_DELETED, [
        'id' => $productId,
        'message' => 'Product deleted successfully'
    ]);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
