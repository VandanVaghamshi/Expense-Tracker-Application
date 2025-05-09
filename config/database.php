<?php
/**
 * Database Connection Class
 * 
 * Establishes a connection to the MySQL database using PDO.
 */

class Database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "expense_tracker";
    private $username = "root";
    private $password = "";
    public $conn;
    
    /**
     * Get the database connection
     * 
     * @return PDO Database connection
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}