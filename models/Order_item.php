<?php

/**
 * Order_item Model
 * Handles order items (line items in orders) operations in the database
 */

class Order_item {
    private $conn;
    private $table = 'order_items';
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get all order items
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of order items
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM $this->table ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get order item by ID
     * @param int $id Order item ID
     * @return array Order item data or null
     */
    public function getById($id) {
        $sql = "SELECT * FROM $this->table WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get items by order ID
     * @param int $orderId Order ID
     * @return array Array of order items
     */
    public function getByOrderId($orderId) {
        $sql = "SELECT oi.*, p.name as product_name, p.img as product_image
                FROM $this->table oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :order_id
                ORDER BY oi.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get items by product ID
     * @param int $productId Product ID
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of order items
     */
    public function getByProductId($productId, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM $this->table WHERE product_id = :product_id ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new order item
     * @param int $orderId Order ID
     * @param int $productId Product ID
     * @param int $quantity Quantity
     * @param float $price Price per unit
     * @return bool|int False on failure, item ID on success
     */
    public function create($orderId, $productId, $quantity, $price) {
        $sql = "INSERT INTO $this->table (order_id, product_id, quantity, price)
                VALUES (:order_id, :product_id, :quantity, :price)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':price', $price);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update order item
     * @param int $id Order item ID
     * @param int $quantity Quantity (optional)
     * @param float $price Price per unit (optional)
     * @return bool Success status
     */
    public function update($id, $quantity = null, $price = null) {
        $updates = [];
        $bindings = [':id' => $id];
        
        if ($quantity !== null) {
            $updates[] = "quantity = :quantity";
            $bindings[':quantity'] = $quantity;
        }
        if ($price !== null) {
            $updates[] = "price = :price";
            $bindings[':price'] = $price;
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql = "UPDATE $this->table SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Delete order item
     * @param int $id Order item ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Delete all items in an order
     * @param int $orderId Order ID
     * @return bool Success status
     */
    public function deleteByOrderId($orderId) {
        $sql = "DELETE FROM $this->table WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Get order total (sum of all items)
     * @param int $orderId Order ID
     * @return float Total amount
     */
    public function getOrderTotal($orderId) {
        $sql = "SELECT SUM(quantity * price) as total FROM $this->table WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }
}
