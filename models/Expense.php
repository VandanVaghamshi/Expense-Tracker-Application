<?php
/**
 * Expense Model
 * 
 * Handles all expense-related database operations including creating,
 * reading, updating, and deleting expenses.
 */

require_once __DIR__ . '/../config/database.php';

class Expense {
    // Database connection and table name
    private $conn;
    private $table_name = "expenses";
    
    // Expense properties
    public $id;
    public $user_id;
    public $description;
    public $amount;
    public $category_id;
    public $category_name; // For joining with categories table
    public $expense_date;
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
     * Create a new expense
     * 
     * @return boolean Success or failure
     */
    public function create() {
        // Sanitize input
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->amount = floatval($this->amount);
        $this->category_id = intval($this->category_id);
        $this->user_id = intval($this->user_id);
        $this->expense_date = htmlspecialchars(strip_tags($this->expense_date));
        
        // Query to insert new expense
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    user_id = :user_id,
                    description = :description,
                    amount = :amount,
                    category_id = :category_id,
                    expense_date = :expense_date";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':expense_date', $this->expense_date);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Read all expenses for a user
     * 
     * @param int $user_id User ID
     * @param string $order_by Order by field (default: expense_date DESC)
     * @return PDOStatement The prepared statement with results
     */
    public function readAll($user_id, $order_by = 'expense_date DESC') {
        // Query to read all expenses for a user
        $query = "SELECT e.*, c.name as category_name
                FROM " . $this->table_name . " e
                LEFT JOIN categories c ON e.category_id = c.id
                WHERE e.user_id = ?
                ORDER BY " . $order_by;
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind user ID
        $stmt->bindParam(1, $user_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Read one expense
     * 
     * @param int $id Expense ID
     * @return boolean Success or failure
     */
    public function readOne($id) {
        // Query to read one expense
        $query = "SELECT e.*, c.name as category_name
                FROM " . $this->table_name . " e
                LEFT JOIN categories c ON e.category_id = c.id
                WHERE e.id = ?
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
            $this->user_id = $row['user_id'];
            $this->description = $row['description'];
            $this->amount = $row['amount'];
            $this->category_id = $row['category_id'];
            $this->category_name = $row['category_name'];
            $this->expense_date = $row['expense_date'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Update an expense
     * 
     * @return boolean Success or failure
     */
    public function update() {
        // Sanitize input
        $this->id = intval($this->id);
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->amount = floatval($this->amount);
        $this->category_id = intval($this->category_id);
        $this->expense_date = htmlspecialchars(strip_tags($this->expense_date));
        
        // Query to update expense
        $query = "UPDATE " . $this->table_name . "
                SET
                    description = :description,
                    amount = :amount,
                    category_id = :category_id,
                    expense_date = :expense_date
                WHERE
                    id = :id AND user_id = :user_id";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':amount', $this->amount);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':expense_date', $this->expense_date);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete an expense
     * 
     * @return boolean Success or failure
     */
    public function delete() {
        // Query to delete expense
        $query = "DELETE FROM " . $this->table_name . "
                WHERE id = :id AND user_id = :user_id";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize and bind values
        $this->id = intval($this->id);
        $this->user_id = intval($this->user_id);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get expenses by date range
     * 
     * @param int $user_id User ID
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return PDOStatement The prepared statement with results
     */
    public function getByDateRange($user_id, $start_date, $end_date) {
        // Query to get expenses by date range
        $query = "SELECT e.*, c.name as category_name
                FROM " . $this->table_name . " e
                LEFT JOIN categories c ON e.category_id = c.id
                WHERE e.user_id = ?
                AND e.expense_date BETWEEN ? AND ?
                ORDER BY e.expense_date DESC";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $start_date);
        $stmt->bindParam(3, $end_date);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get expenses by category
     * 
     * @param int $user_id User ID
     * @param int $category_id Category ID
     * @return PDOStatement The prepared statement with results
     */
    public function getByCategory($user_id, $category_id) {
        // Query to get expenses by category
        $query = "SELECT e.*, c.name as category_name
                FROM " . $this->table_name . " e
                LEFT JOIN categories c ON e.category_id = c.id
                WHERE e.user_id = ?
                AND e.category_id = ?
                ORDER BY e.expense_date DESC";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $category_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get total expenses by user
     * 
     * @param int $user_id User ID
     * @return float Total expenses amount
     */
    public function getTotalByUser($user_id) {
        // Query to get total expenses
        $query = "SELECT SUM(amount) as total
                FROM " . $this->table_name . "
                WHERE user_id = ?";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind user ID
        $stmt->bindParam(1, $user_id);
        
        // Execute query
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ? floatval($row['total']) : 0;
    }

    /**
     * Get daily expense summary for a date range
     * 
     * @param int $user_id User ID
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return PDOStatement The prepared statement with results
     */
    public function getDailySummary($user_id, $start_date, $end_date) {
        // Query to get daily expense summary
        $query = "SELECT DATE(expense_date) as date,
                         SUM(amount) as total,
                         GROUP_CONCAT(DISTINCT c.name) as categories
                  FROM " . $this->table_name . " e
                  LEFT JOIN categories c ON e.category_id = c.id
                  WHERE e.user_id = ?
                  AND e.expense_date BETWEEN ? AND ?
                  GROUP BY DATE(expense_date)
                  ORDER BY date DESC";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $start_date);
        $stmt->bindParam(3, $end_date);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get category-wise summary for a date range
     * 
     * @param int $user_id User ID
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return PDOStatement The prepared statement with results
     */
    public function getCategoryWiseSummary($user_id, $start_date, $end_date) {
        // Query to get category-wise summary
        $query = "SELECT c.name as category_name,
                         SUM(e.amount) as total
                  FROM " . $this->table_name . " e
                  LEFT JOIN categories c ON e.category_id = c.id
                  WHERE e.user_id = ?
                  AND e.expense_date BETWEEN ? AND ?
                  GROUP BY c.id, c.name
                  ORDER BY total DESC";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $start_date);
        $stmt->bindParam(3, $end_date);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
        
    
    
    /**
     * Get expenses summary by category
     * 
     * @param int $user_id User ID
     * @return PDOStatement The prepared statement with results
     */
    public function getSummaryByCategory($user_id) {
        // Query to get summary by category
        $query = "SELECT c.name as category_name, SUM(e.amount) as total
                FROM " . $this->table_name . " e
                LEFT JOIN categories c ON e.category_id = c.id
                WHERE e.user_id = ?
                GROUP BY e.category_id
                ORDER BY total DESC";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind user ID
        $stmt->bindParam(1, $user_id);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    

    
    /**
     * Get all categories
     * 
     * @return PDOStatement The prepared statement with results
     */
    public function getAllCategories() {
        // Query to get all categories
        $query = "SELECT * FROM categories ORDER BY name";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Export expenses to CSV
     * 
     * @param int $user_id User ID
     * @return array Expenses data for CSV export
     */
    public function exportToCSV($user_id) {
        $expenses = [];
        
        // Get all expenses for the user
        $stmt = $this->readAll($user_id);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $expenses[] = [
                'id' => $row['id'],
                'description' => $row['description'],
                'amount' => $row['amount'],
                'category' => $row['category_name'],
                'date' => $row['expense_date'],
                'created_at' => $row['created_at']
            ];
        }
        
        return $expenses;
    }
    
    /**
     * Get all expenses for a user with pagination
     * 
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return array Expenses with pagination
     */
    public function getAllByUser($limit = 10, $offset = 0) {
        // Query to get all expenses with pagination
        $query = "SELECT e.*, c.name as category_name
                FROM " . $this->table_name . " e
                LEFT JOIN categories c ON e.category_id = c.id
                WHERE e.user_id = :user_id
                ORDER BY e.expense_date DESC
                LIMIT :limit OFFSET :offset";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        // Execute query
        $stmt->execute();
        
        // Fetch all results as an associative array
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $expenses;
    }
    
    /**
     * Count total expenses for a user
     * 
     * @return int Total number of expenses
     */
    public function countByUser() {
        // Query to count expenses
        $query = "SELECT COUNT(*) as total
                FROM " . $this->table_name . "
                WHERE user_id = :user_id";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
        
        // Execute query
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ? (int)$row['total'] : 0;
    }
    
    /**
     * Get total expenses by month
     * 
     * @param string $month Month in format YYYY-MM
     * @return float Total expenses for the month
     */
    public function getTotalByMonth($month) {
        // Create date range for the month
        $start_date = $month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        
        // Query to get total expenses for the month
        $query = "SELECT SUM(amount) as total
                FROM " . $this->table_name . "
                WHERE user_id = :user_id
                AND expense_date BETWEEN :start_date AND :end_date";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        
        // Execute query
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ? floatval($row['total']) : 0;
    }
    
    /**
     * Get total expenses by date
     * 
     * @param string $date Date in format YYYY-MM-DD
     * @return float Total expenses for the date
     */
    public function getTotalByDate($date) {
        // Query to get total expenses for the date
        $query = "SELECT SUM(amount) as total
                FROM " . $this->table_name . "
                WHERE user_id = :user_id
                AND expense_date = :date";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date);
        
        // Execute query
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ? floatval($row['total']) : 0;
    }
    
    /**
     * Get recent expenses by user
     * 
     * @param int $limit Number of recent expenses to return
     * @return array Recent expenses
     */
    public function getRecentByUser($limit = 5) {
        // Query to get recent expenses
        $query = "SELECT e.*, c.name as category_name
                FROM " . $this->table_name . " e
                LEFT JOIN categories c ON e.category_id = c.id
                WHERE e.user_id = :user_id
                ORDER BY e.expense_date DESC
                LIMIT :limit";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        
        // Execute query
        $stmt->execute();
        
        // Fetch all results as an associative array
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $expenses;
    }
}