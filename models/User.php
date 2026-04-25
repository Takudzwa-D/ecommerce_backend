<?php

/**
 * User Model
 * Handles user authentication and account operations in the database
 */

class User {
    private $conn;
    private $table = 'Users';
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get user by email
     * @param string $email User email
     * @return array User data or null
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM $this->table WHERE Email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user by ID
     * @param int $id User ID
     * @return array User data or null
     */
    public function findById($id) {
        $sql = "SELECT * FROM $this->table WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all users with pagination
     * @param int $limit Limit results (optional)
     * @param int $offset Offset results (optional)
     * @return array Array of users
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT id, FirstName, LastName, Email, PhoneNumber, Address, City, Country, Role, CreatedAt, UpdatedAt 
                FROM $this->table ORDER BY CreatedAt DESC";
        
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
     * Register new user
     * @param string $firstName First name
     * @param string $lastName Last name
     * @param string $email Email address (must be unique)
     * @param string $password Password (will be hashed)
     * @param string $phoneNumber Phone number
     * @param string $address Address
     * @param string $city City
     * @param string $country Country
     * @param string $role User role (Customer or Admin)
     * @return bool|int False on failure, user ID on success
     */
    public function create($firstName, $lastName, $email, $password, $phoneNumber = '', $address = '', $city = '', $country = '', $role = 'Customer') {
        // Check if email already exists
        if ($this->findByEmail($email)) {
            return false;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO $this->table (FirstName, LastName, Role, Email, PhoneNumber, Address, City, Country, Password)
                VALUES (:firstName, :lastName, :role, :email, :phoneNumber, :address, :city, :country, :password)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':firstName', $firstName);
        $stmt->bindParam(':lastName', $lastName);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phoneNumber', $phoneNumber);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':password', $hashedPassword);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update user profile
     * @param int $id User ID
     * @param string $firstName First name (optional)
     * @param string $lastName Last name (optional)
     * @param string $phoneNumber Phone number (optional)
     * @param string $address Address (optional)
     * @param string $city City (optional)
     * @param string $country Country (optional)
     * @return bool Success status
     */
    public function update($id, $firstName = null, $lastName = null, $phoneNumber = null, $address = null, $city = null, $country = null) {
        $updates = [];
        $bindings = [':id' => $id];
        
        if ($firstName !== null) {
            $updates[] = "FirstName = :firstName";
            $bindings[':firstName'] = $firstName;
        }
        if ($lastName !== null) {
            $updates[] = "LastName = :lastName";
            $bindings[':lastName'] = $lastName;
        }
        if ($phoneNumber !== null) {
            $updates[] = "PhoneNumber = :phoneNumber";
            $bindings[':phoneNumber'] = $phoneNumber;
        }
        if ($address !== null) {
            $updates[] = "Address = :address";
            $bindings[':address'] = $address;
        }
        if ($city !== null) {
            $updates[] = "City = :city";
            $bindings[':city'] = $city;
        }
        if ($country !== null) {
            $updates[] = "Country = :country";
            $bindings[':country'] = $country;
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
     * Change user password
     * @param int $id User ID
     * @param string $newPassword New password (will be hashed)
     * @return bool Success status
     */
    public function changePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $sql = "UPDATE $this->table SET Password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':password', $hashedPassword);
        
        return $stmt->execute();
    }
    
    /**
     * Update user role (admin only)
     * @param int $id User ID
     * @param string $role New role
     * @return bool Success status
     */
    public function updateRole($id, $role) {
        $sql = "UPDATE $this->table SET Role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':role', $role);
        
        return $stmt->execute();
    }
    
    /**
     * Delete user
     * @param int $id User ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Count total users
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