<?php

/**
 * Update Order Status Controller
 * Updates the status of an order (admin only)
 * PUT /api/orders/update-status?orderId=1
 * 
 * Request body:
 * {
 *   "status": "Completed"
 * }
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
    // Get order ID
    $orderId = getQuery('orderId');
    
    if (isEmptyField($orderId)) {
        errorResponse('Order ID is required', null, HTTP_BAD_REQUEST);
    }
    
    $orderId = sanitizeInt($orderId);
    if (!isPositive($orderId)) {
        errorResponse('Invalid order ID', null, HTTP_BAD_REQUEST);
    }
    
    // Get JSON input
    $data = getJsonInput();
    $status = sanitizeString($data['status'] ?? '');
    
    if (isEmptyField($status)) {
        errorResponse('Status is required', null, HTTP_BAD_REQUEST);
    }
    
    // Validate status
    if (!isValidEnum($status, ALLOWED_ORDER_STATUSES)) {
        errorResponse('Invalid order status. Allowed: ' . implode(', ', ALLOWED_ORDER_STATUSES), null, HTTP_BAD_REQUEST);
    }
    
    // Get order
    $orderModel = new Order($conn);
    $order = $orderModel->getById($orderId);
    
    if (!$order) {
        errorResponse('Order not found', null, HTTP_NOT_FOUND);
    }
    
    // Update order status
    $success = $orderModel->updateStatus($orderId, $status);
    
    if (!$success) {
        errorResponse('Failed to update order status', null, HTTP_INTERNAL_ERROR);
    }
    
    // Get updated order
    $updatedOrder = $orderModel->getById($orderId);
    
    successResponse(SUCCESS_UPDATED, $updatedOrder);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
