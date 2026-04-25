<?php

/**
 * Get Categories Controller
 * Retrieves all categories with pagination
 * GET /api/categories?page=1&limit=10
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Category.php";

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
    
    // Get categories
    $categoryModel = new Category($conn);
    $categories = $categoryModel->getAll($limit, $offset);
    $total = $categoryModel->count();
    
    // Prepare response
    $response = [
        'categories' => $categories,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit)
        ]
    ];
    
    successResponse('Categories retrieved successfully', $response);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
