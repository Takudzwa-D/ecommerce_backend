<?php

/**
 * Brand Model
 * Handles brand (vehicle manufacturer) operations in the database
 */

class Brand {
    private $conn;
    private $table = 'brands';
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get all brands
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of brands
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM $this->table ORDER BY name ASC";
        
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
     * Get brand by ID
     * @param int $id Brand ID
     * @return array Brand data or null
     */
    public function getById($id) {
        $sql = "SELECT * FROM $this->table WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get brand by name
     * @param string $name Brand name
     * @return array Brand data or null
     */
    public function getByName($name) {
        $sql = "SELECT * FROM $this->table WHERE name = :name LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new brand
     * @param string $name Brand name
     * @return bool Success status
     */
    public function create($name) {
        // Check if brand already exists
        if ($this->getByName($name)) {
            return false;
        }
        
        $sql = "INSERT INTO $this->table (name) VALUES (:name)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        
        return $stmt->execute();
    }
    
    /**
     * Update brand
     * @param int $id Brand ID
     * @param string $name Brand name
     * @return bool Success status
     */
    public function update($id, $name) {
        $sql = "UPDATE $this->table SET name = :name WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        
        return $stmt->execute();
    }
    
    /**
     * Delete brand
     * @param int $id Brand ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Count total brands
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
