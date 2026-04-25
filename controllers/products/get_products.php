<?php

/**
 * Get Products Controller
 * Retrieves all products with pagination and optional filtering
 * GET /api/products?page=1&limit=10
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Product.php";

try {
    // Get pagination parameters
    $page = (int)(getQuery('page', 1));
    $limit = (int)(getQuery('limit', DEFAULT_PAGE_SIZE));
    
    // Validate pagination
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = DEFAULT_PAGE_SIZE;
    if ($limit > MAX_PAGE_SIZE) $limit = MAX_PAGE_SIZE;
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    // Get products
    $productModel = new Product($conn);
    $products = $productModel->getAll($limit, $offset);
    $total = $productModel->count();
    
    // Prepare response
    $response = [
        'products' => $products,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit)
        ]
    ];
    
    successResponse('Products retrieved successfully', $response);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
