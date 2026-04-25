<?php

/**
 * Upload Product Image Controller
 * Uploads an image for a product (admin only)
 * POST /api/products/upload-image?productId=1
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
    $productId = getQuery('productId');
    
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
    
    // Check if file was uploaded
    if (!isset($_FILES['image'])) {
        errorResponse('No image file provided', null, HTTP_BAD_REQUEST);
    }
    
    // Upload product image
    $uploadResult = uploadProductImage($_FILES['image']);
    
    if (!$uploadResult['success']) {
        errorResponse($uploadResult['error'], null, HTTP_BAD_REQUEST);
    }
    
    // Delete old image if it exists
    if ($product['img']) {
        deleteProductImage($product['img']);
    }
    
    // Update product with new image
    $success = $productModel->update(
        $productId,
        null,
        null,
        null,
        null,
        $uploadResult['filename']
    );
    
    if (!$success) {
        // Delete uploaded file if database update fails
        deleteProductImage($uploadResult['filename']);
        errorResponse('Failed to update product image', null, HTTP_INTERNAL_ERROR);
    }
    
    // Get updated product
    $updatedProduct = $productModel->getById($productId);
    
    successResponse('Image uploaded successfully', [
        'filename' => $uploadResult['filename'],
        'product' => $updatedProduct
    ]);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
