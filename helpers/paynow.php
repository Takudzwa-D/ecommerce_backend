<?php

/**
 * PayNow Payment Gateway Integration
 * Handles PayNow payment initiation and verification
 */

// PayNow Configuration - Should be in environment variables for production
define('PAYNOW_INTEGRATION_KEY', getenv('PAYNOW_INTEGRATION_KEY') ?: 'your-integration-key-here');
define('PAYNOW_ENCRYPTION_KEY', getenv('PAYNOW_ENCRYPTION_KEY') ?: 'your-encryption-key-here');
define('PAYNOW_API_URL', 'https://www.paynow.co.zw/api/initiatetransaction');

/**
 * Generate hash for PayNow transaction
 * @param array $data Transaction data
 * @return string MD5 hash
 */
function generatePayNowHash($data) {
    $hashString = "IntegrationKey=" . PAYNOW_INTEGRATION_KEY;
    
    foreach ($data as $key => $value) {
        if ($key !== 'hash' && !empty($value)) {
            $hashString .= "&" . $key . "=" . $value;
        }
    }
    
    return md5($hashString);
}

/**
 * Initiate PayNow payment
 * @param array $orderData Order and customer information
 * @return array Payment result ['success' => bool, 'url' => string, 'error' => string]
 */
function initiatePayNowPayment($orderData) {
    // Validate required fields
    $required = ['order_id', 'amount', 'email', 'phone'];
    $validation = validateRequired($orderData, $required);
    
    if (!$validation['valid']) {
        return [
            'success' => false,
            'error' => 'Missing required fields: ' . implode(', ', $validation['missing'])
        ];
    }
    
    try {
        // Prepare payment data
        $paymentData = [
            'resulturl' => getenv('APP_URL') . '/api/payments/result',
            'returnurl' => getenv('APP_URL') . '/api/payments/return',
            'referencenumber' => $orderData['order_id'],
            'amount' => number_format((float)$orderData['amount'], 2, '.', ''),
            'email' => $orderData['email'],
            'phonenumber' => $orderData['phone'],
            'status' => 'Message'
        ];
        
        // Generate hash
        $paymentData['hash'] = generatePayNowHash($paymentData);
        
        // Build redirect URL
        $redirectUrl = PAYNOW_API_URL . '?' . http_build_query($paymentData);
        
        return [
            'success' => true,
            'url' => $redirectUrl
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Verify PayNow transaction response
 * @param array $response PayNow webhook or callback data
 * @return array Verification result ['valid' => bool, 'reference' => string, 'status' => string]
 */
function verifyPayNowTransaction($response) {
    // Check required fields
    $required = ['reference', 'paynowreference', 'amount', 'status', 'hash'];
    foreach ($required as $field) {
        if (empty($response[$field])) {
            return [
                'valid' => false,
                'error' => "Missing field: $field"
            ];
        }
    }
    
    // Verify hash
    $receivedHash = $response['hash'];
    unset($response['hash']);
    
    $expectedHash = md5(implode('', $response));
    
    if ($receivedHash !== $expectedHash) {
        return [
            'valid' => false,
            'error' => 'Invalid transaction hash'
        ];
    }
    
    return [
        'valid' => true,
        'reference' => $response['reference'],
        'paynow_reference' => $response['paynowreference'],
        'status' => strtolower($response['status']),
        'amount' => $response['amount']
    ];
}

/**
 * Parse PayNow status response
 * @param string $status PayNow status string
 * @return string Normalized status (pending, completed, failed)
 */
function parsePayNowStatus($status) {
    $status = strtolower($status);
    
    if (strpos($status, 'cancelled') !== false || strpos($status, 'failed') !== false) {
        return 'Failed';
    }
    
    if (strpos($status, 'delivered') !== false || strpos($status, 'complete') !== false) {
        return 'Completed';
    }
    
    return 'Pending';
}

/**
 * Format payment amount to 2 decimal places
 * @param float $amount Amount to format
 * @return string Formatted amount
 */
function formatPaymentAmount($amount) {
    return number_format((float)$amount, 2, '.', '');
}
