<?php


///user related actions

class User{
    private $conn;
    private $table = 'user';

    public function __construct($db){
        $this->conn = $db;
    }


    //this method finds email
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM $this->table WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt -> fetch(PDO::FETCH_ASSOC);

    }

    //register user
    public function create($firstName,$lastName,$role,$email,$phoneNumber,$address,$city,$country ,$password){
        $sql = "INSERT INTO " . $this->table . " (firstName,lastName,role,email,phoneNumber,address,city,country,password)
        VALUES (:firstName,lastName, :role, :email, :phoneNumber, :address, :city, :country, :password) ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':firstName', $firstName);
        $stmt->bindParam(':lastName', $lastName);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phoneNumber', $phoneNumber);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':password', $password);
        return $stmt->execute();



    }

}