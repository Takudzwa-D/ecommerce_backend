<?php

/**
 * Get My Orders Controller
 * Retrieves orders for the authenticated user
 * GET /api/orders/my?page=1&limit=10&status=Pending
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Order.php";
require_once __DIR__ . "/../../middleware/require_auth.php";

// Require authentication
requireAuth();
$user = getAuthUser();

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
    
    // Get user's orders
    if ($status) {
        // Validate status if provided
        if (!isValidEnum($status, ALLOWED_ORDER_STATUSES)) {
            errorResponse('Invalid order status', null, HTTP_BAD_REQUEST);
        }
        
        // Get all user orders and filter by status
        $allUserOrders = $orderModel->getByUserId($user['id']);
        $filteredOrders = array_filter($allUserOrders, function($order) use ($status) {
            return $order['status'] === $status;
        });
        
        $total = count($filteredOrders);
        $orders = array_slice($filteredOrders, $offset, $limit);
    } else {
        $orders = $orderModel->getByUserId($user['id'], $limit, $offset);
        
        // Get total count
        $allUserOrders = $orderModel->getByUserId($user['id']);
        $total = count($allUserOrders);
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
    
    successResponse('Your orders retrieved successfully', $response);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
