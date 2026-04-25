<?php

/**
 * Payment Model
 * Handles payment operations in the database
 */

class Payment {
    private $conn;
    private $table = 'payments';
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get all payments
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of payments
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
     * Get payment by ID
     * @param int $id Payment ID
     * @return array Payment data or null
     */
    public function getById($id) {
        $sql = "SELECT * FROM $this->table WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payment by order ID
     * @param int $orderId Order ID
     * @return array Payment data or null
     */
    public function getByOrderId($orderId) {
        $sql = "SELECT * FROM $this->table WHERE order_id = :order_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payments by status
     * @param string $status Payment status
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of payments
     */
    public function getByStatus($status, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM $this->table WHERE payment_status = :status ORDER BY created_at DESC";
        
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
     * Get payments by method
     * @param string $method Payment method
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of payments
     */
    public function getByMethod($method, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM $this->table WHERE payment_method = :method ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':method', $method);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new payment
     * @param int $orderId Order ID
     * @param string $method Payment method
     * @param string $status Payment status (default: Pending)
     * @return bool|int False on failure, payment ID on success
     */
    public function create($orderId, $method, $status = 'Pending') {
        $sql = "INSERT INTO $this->table (order_id, payment_method, payment_status)
                VALUES (:order_id, :method, :status)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindParam(':method', $method);
        $stmt->bindParam(':status', $status);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update payment status
     * @param int $id Payment ID
     * @param string $status Payment status
     * @return bool Success status
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE $this->table SET payment_status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }
    
    /**
     * Update payment
     * @param int $id Payment ID
     * @param string $method Payment method (optional)
     * @param string $status Payment status (optional)
     * @return bool Success status
     */
    public function update($id, $method = null, $status = null) {
        $updates = [];
        $bindings = [':id' => $id];
        
        if ($method !== null) {
            $updates[] = "payment_method = :method";
            $bindings[':method'] = $method;
        }
        if ($status !== null) {
            $updates[] = "payment_status = :status";
            $bindings[':status'] = $status;
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
     * Delete payment
     * @param int $id Payment ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Count total payments
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
