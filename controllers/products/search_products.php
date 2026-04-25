<?php

/**
 * Search Products Controller
 * Searches products by name or description
 * GET /api/products/search?q=engine&page=1&limit=10
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Product.php";

try {
    // Get search query
    $query = trim(getQuery('q', ''));
    
    if (isEmptyField($query)) {
        errorResponse('Search query is required', null, HTTP_BAD_REQUEST);
    }
    
    // Validate query length
    if (strlen($query) < 2) {
        errorResponse('Search query must be at least 2 characters', null, HTTP_BAD_REQUEST);
    }
    
    if (strlen($query) > 255) {
        errorResponse('Search query must not exceed 255 characters', null, HTTP_BAD_REQUEST);
    }
    
    // Get pagination parameters
    $page = (int)(getQuery('page', 1));
    $limit = (int)(getQuery('limit', DEFAULT_PAGE_SIZE));
    
    // Validate pagination
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = DEFAULT_PAGE_SIZE;
    if ($limit > MAX_PAGE_SIZE) $limit = MAX_PAGE_SIZE;
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    // Search products
    $productModel = new Product($conn);
    
    // Get all search results first to count them
    $allResults = $productModel->search($query);
    $total = count($allResults);
    
    // Apply pagination
    $products = array_slice($allResults, $offset, $limit);
    
    // Prepare response
    $response = [
        'query' => $query,
        'products' => $products,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit)
        ]
    ];
    
    successResponse('Products found', $response);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
