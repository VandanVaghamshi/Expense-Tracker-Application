<?php
/**
 * User Model
 * 
 * Handles all user-related database operations including registration,
 * login, and profile management.
 */

require_once __DIR__ . '/../config/database.php';

class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";
    
    // User properties
    public $id;
    public $name;
    public $email;
    public $password;
    public $created_at;
    public $updated_at;
    
    /**
     * Constructor with database connection
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create a new user (register)
     * 
     * @return boolean Success or failure
     */
    public function create() {
        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Hash the password
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        
        // Query to insert new user
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    name = :name,
                    email = :email,
                    password = :password";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if email already exists
     * 
     * @return boolean True if email exists, false otherwise
     */
    public function emailExists() {
        // Query to check if email exists
        $query = "SELECT id, name, password
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind email
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(1, $this->email);
        
        // Execute query
        $stmt->execute();
        
        // Get row count
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->password = $row['password'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Validate user login
     * 
     * @param string $email User email
     * @param string $password User password
     * @return boolean True if valid credentials, false otherwise
     */
    public function login($email, $password) {
        $this->email = $email;
        
        // Check if email exists
        if ($this->emailExists()) {
            // Verify password
            if (password_verify($password, $this->password)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return boolean Success or failure
     */
    public function readOne($id) {
        // Query to read one user
        $query = "SELECT *
                FROM " . $this->table_name . "
                WHERE id = ?
                LIMIT 0,1";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind ID
        $stmt->bindParam(1, $id);
        
        // Execute query
        $stmt->execute();
        
        // Get row count
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Update user profile
     * 
     * @return boolean Success or failure
     */
    public function update() {
        // If password was provided, hash it
        $password_set = !empty($this->password) ? ", password = :password" : "";
        
        // Query to update user
        $query = "UPDATE " . $this->table_name . "
                SET
                    name = :name,
                    email = :email
                    {$password_set}
                WHERE id = :id";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->id = intval($this->id);
        
        // Bind values
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);
        
        // If password was provided, bind it
        if (!empty($password_set)) {
            $this->password = password_hash($this->password, PASSWORD_BCRYPT);
            $stmt->bindParam(':password', $this->password);
        }
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}