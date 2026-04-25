<?php

/**
 * PayNow Webhook/Hook Controller
 * Receives payment notifications from PayNow payment gateway
 * POST /api/payments/hook
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
    // Get JSON input or POST data
    $data = getJsonInput();
    
    // If no JSON, try POST parameters
    if (empty($data)) {
        $data = $_POST ?? [];
    }
    
    if (empty($data)) {
        // Log webhook data for debugging
        error_log('PayNow Hook: No data received');
        errorResponse('No webhook data provided', null, HTTP_BAD_REQUEST);
    }
    
    // Log webhook for audit
    error_log('PayNow Webhook received: ' . json_encode($data));
    
    // Verify PayNow transaction
    $verification = verifyPayNowTransaction($data);
    
    if (!$verification['valid']) {
        error_log('PayNow Webhook verification failed: ' . $verification['error']);
        errorResponse($verification['error'], null, HTTP_BAD_REQUEST);
    }
    
    // Get order ID from reference
    $orderId = $verification['reference'];
    $paymentStatus = parsePayNowStatus($verification['status']);
    
    // Get order and payment records
    $orderModel = new Order($conn);
    $order = $orderModel->getById($orderId);
    
    if (!$order) {
        error_log('PayNow Webhook: Order not found - ' . $orderId);
        errorResponse('Order not found', null, HTTP_NOT_FOUND);
    }
    
    $paymentModel = new Payment($conn);
    $payment = $paymentModel->getByOrderId($orderId);
    
    if (!$payment) {
        error_log('PayNow Webhook: Payment record not found - ' . $orderId);
        errorResponse('Payment record not found', null, HTTP_NOT_FOUND);
    }
    
    // Update payment status
    $success = $paymentModel->updateStatus($payment['id'], $paymentStatus);
    
    if (!$success) {
        error_log('PayNow Webhook: Failed to update payment status - ' . $orderId);
        errorResponse('Failed to update payment', null, HTTP_INTERNAL_ERROR);
    }
    
    // If payment completed, update order status
    if ($paymentStatus === PAYMENT_STATUS_COMPLETED) {
        $orderModel->updateStatus($orderId, ORDER_STATUS_COMPLETED);
        error_log('PayNow Webhook: Payment completed and order updated - ' . $orderId);
    } elseif ($paymentStatus === PAYMENT_STATUS_FAILED) {
        $orderModel->updateStatus($orderId, ORDER_STATUS_FAILED);
        error_log('PayNow Webhook: Payment failed - ' . $orderId);
    }
    
    // Log success
    error_log('PayNow Webhook: Successfully processed - ' . $orderId . ' - Status: ' . $paymentStatus);
    
    // Return success response
    successResponse('Webhook processed successfully', [
        'orderId' => $orderId,
        'paymentStatus' => $paymentStatus,
        'reference' => $verification['paynow_reference']
    ]);
    
} catch (Exception $e) {
    error_log('PayNow Webhook Exception: ' . $e->getMessage());
    errorResponse('Webhook processing error', null, HTTP_INTERNAL_ERROR);
}
