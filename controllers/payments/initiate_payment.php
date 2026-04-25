<?php

/**
 * Initiate Payment Controller
 * Initiates payment for an order (PayNow integration)
 * POST /api/payments/initiate
 * 
 * Request body:
 * {
 *   "orderId": 1
 * }
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
require_once __DIR__ . "/../../middleware/require_auth.php";

// Require authentication
requireAuth();
$user = getAuthUser();

try {
    // Get JSON input
    $data = getJsonInput();
    $orderId = sanitizeInt($data['orderId'] ?? 0);
    
    if (!isPositive($orderId)) {
        errorResponse('Invalid order ID', null, HTTP_BAD_REQUEST);
    }
    
    // Get order
    $orderModel = new Order($conn);
    $order = $orderModel->getById($orderId);
    
    if (!$order) {
        errorResponse('Order not found', null, HTTP_NOT_FOUND);
    }
    
    // Verify user owns order
    if ($order['user_id'] !== $user['id']) {
        forbiddenResponse('You do not have access to this order');
    }
    
    // Get payment record
    $paymentModel = new Payment($conn);
    $payment = $paymentModel->getByOrderId($orderId);
    
    if (!$payment) {
        errorResponse('Payment record not found for this order', null, HTTP_NOT_FOUND);
    }
    
    // Check payment status
    if ($payment['payment_status'] === PAYMENT_STATUS_COMPLETED) {
        errorResponse('This order has already been paid', null, HTTP_CONFLICT);
    }
    
    // Prepare payment data
    $paymentData = [
        'order_id' => $orderId,
        'amount' => $order['total_amount'],
        'email' => $user['Email'],
        'phone' => $user['PhoneNumber']
    ];
    
    // Initiate payment based on payment method
    $result = [];
    
    if ($payment['payment_method'] === PAYMENT_METHOD_PAYNOW) {
        // Initiate PayNow payment
        $result = initiatePayNowPayment($paymentData);
        
        if (!$result['success']) {
            errorResponse($result['error'], null, HTTP_INTERNAL_ERROR);
        }
    } else {
        // For other methods, return success
        $result = [
            'success' => true,
            'method' => $payment['payment_method'],
            'message' => 'Payment initiated. Please complete payment through your bank or preferred method.'
        ];
    }
    
    successResponse('Payment initiated successfully', [
        'orderId' => $orderId,
        'amount' => $order['total_amount'],
        'method' => $payment['payment_method'],
        'paymentUrl' => $result['url'] ?? null,
        'reference' => $result['reference'] ?? null
    ]);
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
