<?php

/**
 * Sub_Category Model
 * Handles sub-category operations in the database
 */

class Sub_Category {
    private $conn;
    private $table = 'sub_categories';
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get all sub-categories
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of sub-categories
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
     * Get sub-categories by category ID
     * @param int $categoryId Category ID
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of sub-categories
     */
    public function getByCategoryId($categoryId, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM $this->table WHERE category_id = :category_id ORDER BY name ASC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get sub-category by ID
     * @param int $id Sub-category ID
     * @return array Sub-category data or null
     */
    public function getById($id) {
        $sql = "SELECT * FROM $this->table WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get sub-category by name
     * @param string $name Sub-category name
     * @return array Sub-category data or null
     */
    public function getByName($name) {
        $sql = "SELECT * FROM $this->table WHERE name = :name LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new sub-category
     * @param int $categoryId Parent category ID
     * @param string $name Sub-category name
     * @param string $description Sub-category description
     * @return bool Success status
     */
    public function create($categoryId, $name, $description = '') {
        // Check if sub-category already exists
        if ($this->getByName($name)) {
            return false;
        }
        
        $sql = "INSERT INTO $this->table (category_id, name, description) VALUES (:category_id, :name, :description)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        
        return $stmt->execute();
    }
    
    /**
     * Update sub-category
     * @param int $id Sub-category ID
     * @param string $name Sub-category name
     * @param string $description Sub-category description
     * @return bool Success status
     */
    public function update($id, $name, $description = '') {
        $sql = "UPDATE $this->table SET name = :name, description = :description WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        
        return $stmt->execute();
    }
    
    /**
     * Delete sub-category
     * @param int $id Sub-category ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Count total sub-categories
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
