<?php

/**
 * Add Product Controller
 * Creates a new product (admin only)
 * POST /api/products/add
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../helpers/upload.php";
require_once __DIR__ . "/../../models/Product.php";
require_once __DIR__ . "/../../models/Sub_Category.php";
require_once __DIR__ . "/../../models/Car_Model.php";
require_once __DIR__ . "/../../middleware/require_admin.php";

// Require admin authentication
requireAdmin();

try {
    // Get JSON input
    $data = getJsonInput();
    
    // Extract and sanitize input
    $subCategoryId = sanitizeInt($data['subCategoryId'] ?? '');
    $modelId = sanitizeInt($data['modelId'] ?? '');
    $name = sanitizeString($data['name'] ?? '');
    $description = sanitizeString($data['description'] ?? '');
    $price = sanitizeFloat($data['price'] ?? 0);
    $stockQuantity = sanitizeInt($data['stockQuantity'] ?? 0);
    
    // Validate required fields
    $validation = validateRequired([
        'subCategoryId' => $subCategoryId,
        'modelId' => $modelId,
        'name' => $name,
        'price' => $price,
        'stockQuantity' => $stockQuantity
    ], ['subCategoryId', 'modelId', 'name', 'price', 'stockQuantity']);
    
    if (!$validation['valid']) {
        errorResponse('Missing required fields: ' . implode(', ', $validation['missing']), null, HTTP_BAD_REQUEST);
    }
    
    // Validate numeric values
    if (!isPositive($subCategoryId)) {
        errorResponse('Invalid sub-category ID', null, HTTP_BAD_REQUEST);
    }
    
    if (!isPositive($modelId)) {
        errorResponse('Invalid model ID', null, HTTP_BAD_REQUEST);
    }
    
    if (!isPositive($price)) {
        errorResponse('Price must be greater than 0', null, HTTP_BAD_REQUEST);
    }
    
    if ($stockQuantity < 0) {
        errorResponse('Stock quantity cannot be negative', null, HTTP_BAD_REQUEST);
    }
    
    // Verify sub-category and model exist
    $subCategoryModel = new Sub_Category($conn);
    $subCategory = $subCategoryModel->getById($subCategoryId);
    if (!$subCategory) {
        errorResponse('Sub-category not found', null, HTTP_NOT_FOUND);
    }
    
    $carModelModel = new Car_Model($conn);
    $carModel = $carModelModel->getById($modelId);
    if (!$carModel) {
        errorResponse('Car model not found', null, HTTP_NOT_FOUND);
    }
    
    // Create product
    $productModel = new Product($conn);
    $productId = $productModel->create(
        $subCategoryId,
        $modelId,
        $name,
        $description,
        $price,
        $stockQuantity,
        null // Image will be added separately
    );
    
    if (!$productId) {
        errorResponse('Failed to create product', null, HTTP_INTERNAL_ERROR);
    }
    
    // Get created product
    $product = $productModel->getById($productId);
    
    createdResponse(SUCCESS_CREATED, $product);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
