<?php

/**
 * Get Vehicle Products Controller
 * Retrieves products filtered by brand or car model
 * GET /api/brands/products?brandId=1&modelId=2&page=1&limit=10
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Product.php";
require_once __DIR__ . "/../../models/Car_Model.php";
require_once __DIR__ . "/../../models/Brand.php";

try {
    // Get parameters
    $brandId = getQuery('brandId');
    $modelId = getQuery('modelId');
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
    
    // Filter by model if provided
    if ($modelId) {
        $modelId = (int)$modelId;
        
        // Verify model exists
        $carModelModel = new Car_Model($conn);
        $model = $carModelModel->getById($modelId);
        if (!$model) {
            notFoundResponse('Car model not found');
        }
        
        $products = $productModel->getByModelId($modelId, $limit, $offset);
        
        // Calculate total manually
        $allProducts = $productModel->getByModelId($modelId);
        $total = count($allProducts);
    } 
    // Filter by brand - get all models products
    elseif ($brandId) {
        $brandId = (int)$brandId;
        
        // Verify brand exists
        $brandModel = new Brand($conn);
        $brand = $brandModel->getById($brandId);
        if (!$brand) {
            notFoundResponse('Brand not found');
        }
        
        // Get all models for this brand
        $carModelModel = new Car_Model($conn);
        $models = $carModelModel->getByBrandId($brandId);
        
        // Get products from all models
        $allProducts = [];
        foreach ($models as $model) {
            $modelProducts = $productModel->getByModelId($model['id']);
            $allProducts = array_merge($allProducts, $modelProducts);
        }
        
        $total = count($allProducts);
        $products = array_slice($allProducts, $offset, $limit);
    } 
    else {
        errorResponse('Please provide brandId or modelId parameter', null, HTTP_BAD_REQUEST);
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
