<?php

/**
 * Get Category Products Controller
 * Retrieves products filtered by category or sub-category
 * GET /api/categories/products?categoryId=1&subCategoryId=2&page=1&limit=10
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Product.php";
require_once __DIR__ . "/../../models/Sub_Category.php";
require_once __DIR__ . "/../../models/Category.php";

try {
    // Get parameters
    $categoryId = getQuery('categoryId');
    $subCategoryId = getQuery('subCategoryId');
    $page = (int)(getQuery('page', 1));
    $limit = (int)(getQuery('limit', DEFAULT_PAGE_SIZE));
    
    // Validate pagination
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = DEFAULT_PAGE_SIZE;
    if ($limit > MAX_PAGE_SIZE) $limit = MAX_PAGE_SIZE;
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    $productModel = new Product($conn);
    $products = [];
    $total = 0;
    
    // Filter by sub-category if provided
    if ($subCategoryId) {
        $subCategoryId = (int)$subCategoryId;
        
        // Verify sub-category exists
        $subCategoryModel = new Sub_Category($conn);
        $subCategory = $subCategoryModel->getById($subCategoryId);
        if (!$subCategory) {
            notFoundResponse('Sub-category not found');
        }
        
        $products = $productModel->getBySubCategoryId($subCategoryId, $limit, $offset);
        
        // Calculate total manually since count isn't directly available
        $allProducts = $productModel->getBySubCategoryId($subCategoryId);
        $total = count($allProducts);
    } 
    // Filter by category - get all sub-categories products
    elseif ($categoryId) {
        $categoryId = (int)$categoryId;
        
        // Verify category exists
        $categoryModel = new Category($conn);
        $category = $categoryModel->getById($categoryId);
        if (!$category) {
            notFoundResponse('Category not found');
        }
        
        // Get all sub-categories for this category
        $subCategoryModel = new Sub_Category($conn);
        $subCategories = $subCategoryModel->getByCategoryId($categoryId);
        
        // Get products from all sub-categories
        $allProducts = [];
        foreach ($subCategories as $subCat) {
            $catProducts = $productModel->getBySubCategoryId($subCat['id']);
            $allProducts = array_merge($allProducts, $catProducts);
        }
        
        $total = count($allProducts);
        $products = array_slice($allProducts, $offset, $limit);
    } 
    else {
        errorResponse('Please provide categoryId or subCategoryId parameter', null, HTTP_BAD_REQUEST);
    }
    
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
