<?php

/**
 * Order Model
 * Handles order operations in the database
 */

class Order {
    private $conn;
    private $table = 'orders';
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get all orders with pagination
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of orders
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
     * Get order by ID
     * @param int $id Order ID
     * @return array Order data or null
     */
    public function getById($id) {
        $sql = "SELECT * FROM $this->table WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get orders by user ID
     * @param int $userId User ID
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of orders
     */
    public function getByUserId($userId, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM $this->table WHERE user_id = :user_id ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get orders by status
     * @param string $status Order status
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of orders
     */
    public function getByStatus($status, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM $this->table WHERE status = :status ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':status', $status);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new order
     * @param int $userId User ID
     * @param string $customerName Customer name
     * @param string $customerPhone Customer phone
     * @param string $customerAddress Customer address
     * @param float $totalAmount Total order amount
     * @return bool|int False on failure, order ID on success
     */
    public function create($userId, $customerName, $customerPhone, $customerAddress, $totalAmount) {
        $sql = "INSERT INTO $this->table (user_id, customer_name, customer_phone_number, customer_address, total_amount)
                VALUES (:user_id, :customer_name, :customer_phone, :customer_address, :total_amount)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':customer_name', $customerName);
        $stmt->bindParam(':customer_phone', $customerPhone);
        $stmt->bindParam(':customer_address', $customerAddress);
        $stmt->bindParam(':total_amount', $totalAmount);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update order status
     * @param int $id Order ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE $this->table SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }
    
    /**
     * Update order details
     * @param int $id Order ID
     * @param string $customerName Customer name (optional)
     * @param string $customerPhone Customer phone (optional)
     * @param string $customerAddress Customer address (optional)
     * @param float $totalAmount Total amount (optional)
     * @return bool Success status
     */
    public function update($id, $customerName = null, $customerPhone = null, $customerAddress = null, $totalAmount = null) {
        $updates = [];
        $bindings = [':id' => $id];
        
        if ($customerName !== null) {
            $updates[] = "customer_name = :customer_name";
            $bindings[':customer_name'] = $customerName;
        }
        if ($customerPhone !== null) {
            $updates[] = "customer_phone_number = :customer_phone";
            $bindings[':customer_phone'] = $customerPhone;
        }
        if ($customerAddress !== null) {
            $updates[] = "customer_address = :customer_address";
            $bindings[':customer_address'] = $customerAddress;
        }
        if ($totalAmount !== null) {
            $updates[] = "total_amount = :total_amount";
            $bindings[':total_amount'] = $totalAmount;
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
     * Delete order
     * @param int $id Order ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Count total orders
     * @return int Total count
     */
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM $this->table";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }
}
