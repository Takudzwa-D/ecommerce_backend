<?php

/**
 * Get Car Models Controller
 * Retrieves car models, optionally filtered by brand
 * GET /api/brands/models?brandId=1&page=1&limit=10
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Car_Model.php";
require_once __DIR__ . "/../../models/Brand.php";

try {
    // Get parameters
    $brandId = getQuery('brandId');
    $page = (int)(getQuery('page', 1));
    $limit = (int)(getQuery('limit', DEFAULT_PAGE_SIZE));
    
    // Validate pagination
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = DEFAULT_PAGE_SIZE;
    if ($limit > MAX_PAGE_SIZE) $limit = MAX_PAGE_SIZE;
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    $carModelModel = new Car_Model($conn);
    
    // If brandId provided, get models for that brand
    if ($brandId) {
        $brandId = (int)$brandId;
        
        // Verify brand exists
        $brandModel = new Brand($conn);
        $brand = $brandModel->getById($brandId);
        if (!$brand) {
            notFoundResponse('Brand not found');
        }
        
        $models = $carModelModel->getByBrandId($brandId, $limit, $offset);
        
        // Calculate total manually
        $allModels = $carModelModel->getByBrandId($brandId);
        $total = count($allModels);
    } else {
        // Get all models
        $models = $carModelModel->getAll($limit, $offset);
        $total = $carModelModel->count();
    }
    
    // Prepare response
    $response = [
        'models' => $models,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit)
        ]
    ];
    
    successResponse('Models retrieved successfully', $response);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
