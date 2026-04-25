<?php

/**
 * Payment Result Controller
 * Handles payment gateway callback/result notification
 * GET /api/payments/result
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../helpers/paynow.php";
require_once __DIR__ . "/../../models/Order.php";
require_once __DIR__ . "/../../models/Payment.php";

try {
    // Get all query parameters (from PayNow callback)
    $params = getAllQuery();
    
    if (empty($params)) {
        errorResponse('No payment result data provided', null, HTTP_BAD_REQUEST);
    }
    
    // Verify PayNow transaction
    $verification = verifyPayNowTransaction($params);
    
    if (!$verification['valid']) {
        errorResponse($verification['error'], null, HTTP_BAD_REQUEST);
    }
    
    // Get order ID from reference
    $orderId = $verification['reference'];
    $paymentStatus = parsePayNowStatus($verification['status']);
    
    // Get order and payment records
    $orderModel = new Order($conn);
    $order = $orderModel->getById($orderId);
    
    if (!$order) {
        errorResponse('Order not found for reference: ' . $orderId, null, HTTP_NOT_FOUND);
    }
    
    $paymentModel = new Payment($conn);
    $payment = $paymentModel->getByOrderId($orderId);
    
    if (!$payment) {
        errorResponse('Payment record not found', null, HTTP_NOT_FOUND);
    }
    
    // Update payment status
    $success = $paymentModel->updateStatus($payment['id'], $paymentStatus);
    
    if (!$success) {
        errorResponse('Failed to update payment status', null, HTTP_INTERNAL_ERROR);
    }
    
    // If payment completed, update order status
    if ($paymentStatus === PAYMENT_STATUS_COMPLETED) {
        $orderModel->updateStatus($orderId, ORDER_STATUS_COMPLETED);
    } elseif ($paymentStatus === PAYMENT_STATUS_FAILED) {
        $orderModel->updateStatus($orderId, ORDER_STATUS_FAILED);
    }
    
    // Prepare response
    $response = [
        'orderId' => $orderId,
        'paymentStatus' => $paymentStatus,
        'orderStatus' => $paymentStatus === PAYMENT_STATUS_COMPLETED ? ORDER_STATUS_COMPLETED : $order['status'],
        'reference' => $verification['paynow_reference'],
        'amount' => $verification['amount'],
        'message' => 'Payment result processed successfully'
    ];
    
    successResponse('Payment result processed', $response);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
