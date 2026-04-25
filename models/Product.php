<?php

/**
 * Product Model
 * Handles product operations in the database
 * Products link to sub-categories and car models
 */

class Product {
    private $conn;
    private $table = 'products';
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get all products with pagination
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of products
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT p.*, 
                sc.name as sub_category_name, 
                c.name as category_name,
                m.name as model_name,
                b.name as brand_name
                FROM $this->table p
                LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
                LEFT JOIN Categories c ON sc.category_id = c.id
                LEFT JOIN models m ON p.model_id = m.id
                LEFT JOIN brands b ON m.brand_id = b.id
                ORDER BY p.created_at DESC";
        
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
     * Get product by ID
     * @param int $id Product ID
     * @return array Product data or null
     */
    public function getById($id) {
        $sql = "SELECT p.*, 
                sc.name as sub_category_name, 
                c.name as category_name,
                m.name as model_name,
                b.name as brand_name
                FROM $this->table p
                LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
                LEFT JOIN Categories c ON sc.category_id = c.id
                LEFT JOIN models m ON p.model_id = m.id
                LEFT JOIN brands b ON m.brand_id = b.id
                WHERE p.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get products by sub-category
     * @param int $subCategoryId Sub-category ID
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of products
     */
    public function getBySubCategoryId($subCategoryId, $limit = null, $offset = 0) {
        $sql = "SELECT p.*, 
                sc.name as sub_category_name, 
                c.name as category_name,
                m.name as model_name,
                b.name as brand_name
                FROM $this->table p
                LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
                LEFT JOIN Categories c ON sc.category_id = c.id
                LEFT JOIN models m ON p.model_id = m.id
                LEFT JOIN brands b ON m.brand_id = b.id
                WHERE p.sub_category_id = :sub_category_id
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':sub_category_id', $subCategoryId, PDO::PARAM_INT);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get products by model
     * @param int $modelId Car model ID
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of products
     */
    public function getByModelId($modelId, $limit = null, $offset = 0) {
        $sql = "SELECT p.*, 
                sc.name as sub_category_name, 
                c.name as category_name,
                m.name as model_name,
                b.name as brand_name
                FROM $this->table p
                LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
                LEFT JOIN Categories c ON sc.category_id = c.id
                LEFT JOIN models m ON p.model_id = m.id
                LEFT JOIN brands b ON m.brand_id = b.id
                WHERE p.model_id = :model_id
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':model_id', $modelId, PDO::PARAM_INT);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Search products by name or description
     * @param string $query Search query
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of products
     */
    public function search($query, $limit = null, $offset = 0) {
        $sql = "SELECT p.*, 
                sc.name as sub_category_name, 
                c.name as category_name,
                m.name as model_name,
                b.name as brand_name
                FROM $this->table p
                LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
                LEFT JOIN Categories c ON sc.category_id = c.id
                LEFT JOIN models m ON p.model_id = m.id
                LEFT JOIN brands b ON m.brand_id = b.id
                WHERE p.name LIKE :query OR p.description LIKE :query
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($sql);
        $searchQuery = "%$query%";
        $stmt->bindParam(':query', $searchQuery);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new product
     * @param int $subCategoryId Sub-category ID
     * @param int $modelId Car model ID
     * @param string $name Product name
     * @param string $description Product description
     * @param float $price Product price
     * @param int $stockQuantity Stock quantity
     * @param string $img Product image path (optional)
     * @return bool|int False on failure, last insert ID on success
     */
    public function create($subCategoryId, $modelId, $name, $description, $price, $stockQuantity, $img = null) {
        $sql = "INSERT INTO $this->table (sub_category_id, model_id, name, description, price, stock_quantity, img)
                VALUES (:sub_category_id, :model_id, :name, :description, :price, :stock_quantity, :img)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':sub_category_id', $subCategoryId, PDO::PARAM_INT);
        $stmt->bindParam(':model_id', $modelId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock_quantity', $stockQuantity, PDO::PARAM_INT);
        $stmt->bindParam(':img', $img);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update product
     * @param int $id Product ID
     * @param string $name Product name (optional)
     * @param string $description Product description (optional)
     * @param float $price Product price (optional)
     * @param int $stockQuantity Stock quantity (optional)
     * @param string $img Product image path (optional)
     * @return bool Success status
     */
    public function update($id, $name = null, $description = null, $price = null, $stockQuantity = null, $img = null) {
        $updates = [];
        $bindings = [':id' => $id];
        
        if ($name !== null) {
            $updates[] = "name = :name";
            $bindings[':name'] = $name;
        }
        if ($description !== null) {
            $updates[] = "description = :description";
            $bindings[':description'] = $description;
        }
        if ($price !== null) {
            $updates[] = "price = :price";
            $bindings[':price'] = $price;
        }
        if ($stockQuantity !== null) {
            $updates[] = "stock_quantity = :stock_quantity";
            $bindings[':stock_quantity'] = $stockQuantity;
        }
        if ($img !== null) {
            $updates[] = "img = :img";
            $bindings[':img'] = $img;
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
     * Delete product
     * @param int $id Product ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Update product stock
     * @param int $id Product ID
     * @param int $quantity Quantity to add/subtract (can be negative)
     * @return bool Success status
     */
    public function updateStock($id, $quantity) {
        $sql = "UPDATE $this->table SET stock_quantity = stock_quantity + :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Count total products
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
