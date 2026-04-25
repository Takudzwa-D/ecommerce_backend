<?php

/**
 * Payment Status Controller
 * Retrieves payment status for an order
 * GET /api/payments/status?orderId=1
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Order.php";
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
    
    // Verify user owns order (unless admin)
    if ($user['role'] !== ROLE_ADMIN && $order['user_id'] !== $user['id']) {
        forbiddenResponse('You do not have access to this order');
    }
    
    // Get payment record
    $paymentModel = new Payment($conn);
    $payment = $paymentModel->getByOrderId($orderId);
    
    if (!$payment) {
        errorResponse('Payment record not found for this order', null, HTTP_NOT_FOUND);
    }
    
    // Prepare response
    $response = [
        'orderId' => $orderId,
        'paymentId' => $payment['id'],
        'amount' => $order['total_amount'],
        'method' => $payment['payment_method'],
        'status' => $payment['payment_status'],
        'orderStatus' => $order['status'],
        'createdAt' => $payment['created_at'],
        'updatedAt' => $payment['updated_at']
    ];
    
    successResponse('Payment status retrieved successfully', $response);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
