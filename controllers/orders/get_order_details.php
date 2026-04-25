<?php

/**
 * Get Order Details Controller
 * Retrieves detailed information about a specific order including items
 * GET /api/orders/details?orderId=1
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Order.php";
require_once __DIR__ . "/../../models/Order_item.php";
require_once __DIR__ . "/../../models/Payment.php";
require_once __DIR__ . "/../../middleware/require_auth.php";

// Require authentication
requireAuth();
$user = getAuthUser();

try {
    // Get order ID
    $orderId = getQuery('orderId');
    
    if (isEmptyField($orderId)) {
        errorResponse('Order ID is required', null, HTTP_BAD_REQUEST);
    }
    
    $orderId = sanitizeInt($orderId);
    if (!isPositive($orderId)) {
        errorResponse('Invalid order ID', null, HTTP_BAD_REQUEST);
    }
    
    // Get order
    $orderModel = new Order($conn);
    $order = $orderModel->getById($orderId);
    
    if (!$order) {
        errorResponse('Order not found', null, HTTP_NOT_FOUND);
    }
    
    // Check authorization - user can only see their own orders (unless admin)
    if ($user['role'] !== ROLE_ADMIN && $order['user_id'] !== $user['id']) {
        forbiddenResponse('You do not have access to this order');
    }
    
    // Get order items
    $orderItemModel = new Order_item($conn);
    $items = $orderItemModel->getByOrderId($orderId);
    
    // Get payment info
    $paymentModel = new Payment($conn);
    $payment = $paymentModel->getByOrderId($orderId);
    
    // Prepare response
    $response = [
        'order' => $order,
        'items' => $items,
        'payment' => $payment,
        'itemsTotal' => count($items),
        'calculatedTotal' => $orderItemModel->getOrderTotal($orderId)
    ];
    
    successResponse('Order details retrieved successfully', $response);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
