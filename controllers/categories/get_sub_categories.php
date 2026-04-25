<?php

/**
 * Get Sub-Categories Controller
 * Retrieves sub-categories, optionally filtered by category
 * GET /api/sub-categories?categoryId=1&page=1&limit=10
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Sub_Category.php";
require_once __DIR__ . "/../../models/Category.php";

try {
    // Get parameters
    $categoryId = getQuery('categoryId');
    $page = (int)(getQuery('page', 1));
    $limit = (int)(getQuery('limit', DEFAULT_PAGE_SIZE));
    
    // Validate pagination
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = DEFAULT_PAGE_SIZE;
    if ($limit > MAX_PAGE_SIZE) $limit = MAX_PAGE_SIZE;
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    $subCategoryModel = new Sub_Category($conn);
    
    // If categoryId provided, get sub-categories for that category
    if ($categoryId) {
        $categoryId = (int)$categoryId;
        
        // Verify category exists
        $categoryModel = new Category($conn);
        $category = $categoryModel->getById($categoryId);
        if (!$category) {
            notFoundResponse('Category not found');
        }
        
        $subCategories = $subCategoryModel->getByCategoryId($categoryId, $limit, $offset);
        $total = count($subCategoryModel->getByCategoryId($categoryId));
    } else {
        // Get all sub-categories
        $subCategories = $subCategoryModel->getAll($limit, $offset);
        $total = $subCategoryModel->count();
    }
    
    // Prepare response
    $response = [
        'subCategories' => $subCategories,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit)
        ]
    ];
    
    successResponse('Sub-categories retrieved successfully', $response);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
