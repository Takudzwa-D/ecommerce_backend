<?php

/**
 * Get All Orders Controller
 * Retrieves all orders (admin only)
 * GET /api/orders?page=1&limit=10&status=Pending
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Order.php";
require_once __DIR__ . "/../../middleware/require_admin.php";

// Require admin authentication
requireAdmin();

try {
    // Get parameters
    $status = getQuery('status');
    $page = (int)(getQuery('page', 1));
    $limit = (int)(getQuery('limit', DEFAULT_PAGE_SIZE));
    
    // Validate pagination
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = DEFAULT_PAGE_SIZE;
    if ($limit > MAX_PAGE_SIZE) $limit = MAX_PAGE_SIZE;
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    $orderModel = new Order($conn);
    
    // If status filter provided, validate it
    if ($status) {
        if (!isValidEnum($status, ALLOWED_ORDER_STATUSES)) {
            errorResponse('Invalid order status', null, HTTP_BAD_REQUEST);
        }
        
        $orders = $orderModel->getByStatus($status, $limit, $offset);
        
        // Get total count for status
        $allOrders = $orderModel->getByStatus($status);
        $total = count($allOrders);
    } else {
        $orders = $orderModel->getAll($limit, $offset);
        $total = $orderModel->count();
    }
    
    // Prepare response
    $response = [
        'orders' => $orders,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit)
        ]
    ];
    
    successResponse('Orders retrieved successfully', $response);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
