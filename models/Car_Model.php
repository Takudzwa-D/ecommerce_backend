<?php

/**
 * Car_Model Model
 * Handles car model operations in the database
 * Car models belong to a brand (e.g., Corolla belongs to Toyota)
 */

class Car_Model {
    private $conn;
    private $table = 'models';
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get all models
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of models
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
     * Get models by brand ID
     * @param int $brandId Brand ID
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of models
     */
    public function getByBrandId($brandId, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM $this->table WHERE brand_id = :brand_id ORDER BY name ASC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':brand_id', $brandId, PDO::PARAM_INT);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get model by ID
     * @param int $id Model ID
     * @return array Model data or null
     */
    public function getById($id) {
        $sql = "SELECT * FROM $this->table WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get model by name
     * @param string $name Model name
     * @return array Model data or null
     */
    public function getByName($name) {
        $sql = "SELECT * FROM $this->table WHERE name = :name LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new model
     * @param int $brandId Brand ID
     * @param string $name Model name
     * @return bool Success status
     */
    public function create($brandId, $name) {
        // Check if model already exists
        if ($this->getByName($name)) {
            return false;
        }
        
        $sql = "INSERT INTO $this->table (brand_id, name) VALUES (:brand_id, :name)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':brand_id', $brandId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        
        return $stmt->execute();
    }
    
    /**
     * Update model
     * @param int $id Model ID
     * @param string $name Model name
     * @param int $brandId Brand ID (optional)
     * @return bool Success status
     */
    public function update($id, $name, $brandId = null) {
        $sql = "UPDATE $this->table SET name = :name";
        if ($brandId) {
            $sql .= ", brand_id = :brand_id";
        }
        $sql .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        if ($brandId) {
            $stmt->bindParam(':brand_id', $brandId, PDO::PARAM_INT);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Delete model
     * @param int $id Model ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Count total models
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
