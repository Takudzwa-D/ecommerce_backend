<?php

/**
 * Create Order Controller
 * Creates a new order with order items
 * POST /api/orders/create
 * 
 * Request body:
 * {
 *   "items": [
 *     {
 *       "productId": 1,
 *       "quantity": 2
 *     }
 *   ],
 *   "customerName": "John Doe",
 *   "customerPhone": "0712345678",
 *   "customerAddress": "123 Main St",
 *   "paymentMethod": "PayNow"
 * }
 */

require_once __DIR__ . "/../../config/cors.php";
require_once __DIR__ . "/../../config/constance.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/request.php";
require_once __DIR__ . "/../../helpers/response.php";
require_once __DIR__ . "/../../helpers/validator.php";
require_once __DIR__ . "/../../models/Order.php";
require_once __DIR__ . "/../../models/Order_item.php";
require_once __DIR__ . "/../../models/Product.php";
require_once __DIR__ . "/../../models/Payment.php";
require_once __DIR__ . "/../../middleware/require_auth.php";

// Require authentication
requireAuth();
$user = getAuthUser();

try {
    // Get JSON input
    $data = getJsonInput();
    
    // Extract and sanitize input
    $items = $data['items'] ?? [];
    $customerName = sanitizeString($data['customerName'] ?? '');
    $customerPhone = sanitizeString($data['customerPhone'] ?? '');
    $customerAddress = sanitizeString($data['customerAddress'] ?? '');
    $paymentMethod = sanitizeString($data['paymentMethod'] ?? '');
    
    // Validate required fields
    if (empty($items) || !is_array($items)) {
        errorResponse('At least one item is required', null, HTTP_BAD_REQUEST);
    }
    
    $validation = validateRequired([
        'customerName' => $customerName,
        'customerPhone' => $customerPhone,
        'customerAddress' => $customerAddress,
        'paymentMethod' => $paymentMethod
    ], ['customerName', 'customerPhone', 'customerAddress', 'paymentMethod']);
    
    if (!$validation['valid']) {
        errorResponse('Missing required fields: ' . implode(', ', $validation['missing']), null, HTTP_BAD_REQUEST);
    }
    
    // Validate phone
    if (!isValidPhoneNumber($customerPhone)) {
        errorResponse('Invalid phone number format', null, HTTP_BAD_REQUEST);
    }
    
    // Validate payment method
    if (!isValidEnum($paymentMethod, ALLOWED_PAYMENT_METHODS)) {
        errorResponse('Invalid payment method', null, HTTP_BAD_REQUEST);
    }
    
    $productModel = new Product($conn);
    $totalAmount = 0;
    $validatedItems = [];
    
    // Validate and calculate total from items
    foreach ($items as $item) {
        $productId = sanitizeInt($item['productId'] ?? 0);
        $quantity = sanitizeInt($item['quantity'] ?? 0);
        
        if (!isPositive($productId) || !isPositive($quantity)) {
            errorResponse('Invalid product ID or quantity', null, HTTP_BAD_REQUEST);
        }
        
        // Get product
        $product = $productModel->getById($productId);
        if (!$product) {
            errorResponse("Product with ID $productId not found", null, HTTP_NOT_FOUND);
        }
        
        // Check stock
        if ($product['stock_quantity'] < $quantity) {
            errorResponse("Insufficient stock for product {$product['name']}", null, HTTP_CONFLICT);
        }
        
        // Calculate total
        $itemTotal = $product['price'] * $quantity;
        $totalAmount += $itemTotal;
        
        $validatedItems[] = [
            'productId' => $productId,
            'quantity' => $quantity,
            'price' => $product['price']
        ];
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Create order
        $orderModel = new Order($conn);
        $orderId = $orderModel->create(
            $user['id'],
            $customerName,
            $customerPhone,
            $customerAddress,
            $totalAmount
        );
        
        if (!$orderId) {
            throw new Exception('Failed to create order');
        }
        
        // Create order items and update stock
        $orderItemModel = new Order_item($conn);
        
        foreach ($validatedItems as $item) {
            // Create order item
            $itemId = $orderItemModel->create(
                $orderId,
                $item['productId'],
                $item['quantity'],
                $item['price']
            );
            
            if (!$itemId) {
                throw new Exception('Failed to create order item');
            }
            
            // Update product stock
            $productModel->updateStock($item['productId'], -$item['quantity']);
        }
        
        // Create payment record
        $paymentModel = new Payment($conn);
        $paymentId = $paymentModel->create(
            $orderId,
            $paymentMethod,
            PAYMENT_STATUS_PENDING
        );
        
        if (!$paymentId) {
            throw new Exception('Failed to create payment record');
        }
        
        // Commit transaction
        $conn->commit();
        
        // Get created order details
        $order = $orderModel->getById($orderId);
        $orderItems = $orderItemModel->getByOrderId($orderId);
        
        $response = [
            'order' => $order,
            'items' => $orderItems,
            'totalAmount' => $totalAmount,
            'itemsCount' => count($validatedItems)
        ];
        
        createdResponse('Order created successfully', $response);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollBack();
        errorResponse('Failed to create order: ' . $e->getMessage(), null, HTTP_INTERNAL_ERROR);
    }
    
} catch (Exception $e) {
    errorResponse(ERROR_SERVER_ERROR, null, HTTP_INTERNAL_ERROR);
}
