<?php

/**
 * Payment Return Controller
 * Handles user return from payment gateway
 * This is where users are redirected after completing payment
 * GET /api/payments/return
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Order.php";
require_once __DIR__ . "/../../models/Payment.php";

try {
    // Get order reference from query parameters
    $reference = getQuery('reference');
    $status = getQuery('status', 'pending');
    
    if (isEmptyField($reference)) {
        // Redirect to frontend with error
        header('Location: ' . getenv('FRONTEND_URL') . '/payment-failed?error=invalid_reference');
        exit;
    }
    
    $orderId = sanitizeInt($reference);
    
    // Get order and payment records
    $orderModel = new Order($conn);
    $order = $orderModel->getById($orderId);
    
    if (!$order) {
        // Redirect to frontend with error
        header('Location: ' . getenv('FRONTEND_URL') . '/payment-failed?error=order_not_found');
        exit;
    }
    
    $paymentModel = new Payment($conn);
    $payment = $paymentModel->getByOrderId($orderId);
    
    if (!$payment) {
        // Redirect to frontend with error
        header('Location: ' . getenv('FRONTEND_URL') . '/payment-failed?error=payment_not_found');
        exit;
    }
    
    // Map status to readable message
    $statusMessage = match(strtolower($status)) {
        'completed' => 'Payment Completed',
        'pending' => 'Payment Pending',
        'failed' => 'Payment Failed',
        'cancelled' => 'Payment Cancelled',
        default => 'Payment Status Unknown'
    };
    
    // Prepare response for frontend
    $response = [
        'orderId' => $orderId,
        'status' => $status,
        'message' => $statusMessage,
        'orderStatus' => $order['status'],
        'paymentStatus' => $payment['payment_status'],
        'amount' => $order['total_amount']
    ];
    
    // Return JSON response
    successResponse('Payment return processed', $response);
    
} catch (Exception $e) {
    // Redirect to frontend with error
    header('Location: ' . getenv('FRONTEND_URL') . '/payment-failed?error=server_error');
    exit;
}
