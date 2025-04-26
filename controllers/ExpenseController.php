<?php
/**
 * Expense Controller
 * 
 * Handles all expense-related operations including creating, reading,
 * updating, and deleting expenses.
 */

require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class ExpenseController {
    private $expense;
    
    /**
     * Constructor
     * 
     * @param Expense $expense Expense model instance
     */
    public function __construct($expense) {
        $this->expense = $expense;
    }
    
    /**
     * Create a new expense
     * 
     * @param array $data Expense data
     * @return array Response with status and message
     */
    public function create($data) {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }
        
        // Validate input
        if (empty($data['description']) || empty($data['amount']) || empty($data['category_id']) || empty($data['expense_date'])) {
            return [
                'status' => 'error',
                'message' => 'All fields are required',
                'code' => 400
            ];
        }
        
        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            return [
                'status' => 'error',
                'message' => 'Amount must be a positive number',
                'code' => 400
            ];
        }
        
        // Set expense properties
        $this->expense->user_id = AuthController::getCurrentUserId();
        $this->expense->description = $data['description'];
        $this->expense->amount = $data['amount'];
        $this->expense->category_id = $data['category_id'];
        $this->expense->expense_date = $data['expense_date'];
        
        // Create the expense
        if ($this->expense->create()) {
            return [
                'status' => 'success',
                'message' => 'Expense created successfully',
                'code' => 201,
                'expense_id' => $this->expense->id
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Unable to create expense',
                'code' => 500
            ];
        }
    }
    
    /**
     * Get all expenses for the current user
     * 
     * @param string $order_by Order by field (default: expense_date DESC)
     * @return array Response with status and expenses
     */
    public function getAll($order_by = 'expense_date DESC') {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }
        
        // Get user ID
        $user_id = AuthController::getCurrentUserId();
        
        // Get all expenses
        $stmt = $this->expense->readAll($user_id, $order_by);
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $expenses = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $expenses[] = [
                    'id' => $row['id'],
                    'description' => $row['description'],
                    'amount' => $row['amount'],
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name'],
                    'expense_date' => $row['expense_date'],
                    'created_at' => $row['created_at']
                ];
            }
            
            return [
                'status' => 'success',
                'message' => 'Expenses retrieved successfully',
                'code' => 200,
                'count' => $num,
                'expenses' => $expenses
            ];
        } else {
            return [
                'status' => 'success',
                'message' => 'No expenses found',
                'code' => 200,
                'count' => 0,
                'expenses' => []
            ];
        }
    }
    
    /**
     * Get a single expense by ID
     * 
     * @param int $id Expense ID
     * @return array Response with status and expense
     */
    public function getOne($id) {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }
        
        // Get the expense
        if ($this->expense->readOne($id)) {
            // Check if expense belongs to current user
            if ($this->expense->user_id != AuthController::getCurrentUserId()) {
                return [
                    'status' => 'error',
                    'message' => 'Unauthorized access',
                    'code' => 403
                ];
            }
            
            return [
                'status' => 'success',
                'message' => 'Expense retrieved successfully',
                'code' => 200,
                'expense' => [
                    'id' => $this->expense->id,
                    'description' => $this->expense->description,
                    'amount' => $this->expense->amount,
                    'category_id' => $this->expense->category_id,
                    'category_name' => $this->expense->category_name,
                    'expense_date' => $this->expense->expense_date,
                    'created_at' => $this->expense->created_at
                ]
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Expense not found',
                'code' => 404
            ];
        }
    }
    
    /**
     * Update an expense
     * 
     * @param int $id Expense ID
     * @param array $data Expense data
     * @return array Response with status and message
     */
    public function update($id, $data) {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }
        
        // Get the expense
        if (!$this->expense->readOne($id)) {
            return [
                'status' => 'error',
                'message' => 'Expense not found',
                'code' => 404
            ];
        }
        
        // Check if expense belongs to current user
        if ($this->expense->user_id != AuthController::getCurrentUserId()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 403
            ];
        }
        
        // Validate input
        if (empty($data['description']) || empty($data['amount']) || empty($data['category_id']) || empty($data['expense_date'])) {
            return [
                'status' => 'error',
                'message' => 'All fields are required',
                'code' => 400
            ];
        }
        
        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            return [
                'status' => 'error',
                'message' => 'Amount must be a positive number',
                'code' => 400
            ];
        }
        
        // Set expense properties
        $this->expense->description = $data['description'];
        $this->expense->amount = $data['amount'];
        $this->expense->category_id = $data['category_id'];
        $this->expense->expense_date = $data['expense_date'];
        
        // Update the expense
        if ($this->expense->update()) {
            return [
                'status' => 'success',
                'message' => 'Expense updated successfully',
                'code' => 200
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Unable to update expense',
                'code' => 500
            ];
        }
    }
    
    /**
     * Delete an expense
     * 
     * @param int $id Expense ID
     * @return array Response with status and message
     */
    public function delete($id) {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }
        
        // Get the expense
        if (!$this->expense->readOne($id)) {
            return [
                'status' => 'error',
                'message' => 'Expense not found',
                'code' => 404
            ];
        }
        
        // Check if expense belongs to current user
        if ($this->expense->user_id != AuthController::getCurrentUserId()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 403
            ];
        }
        
        // Delete the expense
        if ($this->expense->delete()) {
            return [
                'status' => 'success',
                'message' => 'Expense deleted successfully',
                'code' => 200
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Unable to delete expense',
                'code' => 500
            ];
        }
    }
    
    /**
     * Get expenses by date range
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Response with status and expenses
     */
    public function getByDateRange($start_date, $end_date) {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }
        
        // Get user ID
        $user_id = AuthController::getCurrentUserId();
        
        // Get expenses by date range
        $stmt = $this->expense->getByDateRange($user_id, $start_date, $end_date);
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $expenses = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $expenses[] = [
                    'id' => $row['id'],
                    'description' => $row['description'],
                    'amount' => $row['amount'],
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name'],
                    'expense_date' => $row['expense_date'],
                    'created_at' => $row['created_at']
                ];
            }
            
            return [
                'status' => 'success',
                'message' => 'Expenses retrieved successfully',
                'code' => 200,
                'count' => $num,
                'expenses' => $expenses
            ];
        } else {
            return [
                'status' => 'success',
                'message' => 'No expenses found in the date range',
                'code' => 200,
                'count' => 0,
                'expenses' => []
            ];
        }
    }
    
    /**
     * Get expenses by category
     * 
     * @param int $category_id Category ID
     * @return array Response with status and expenses
     */
    public function getByCategory($category_id) {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }
        
        // Get user ID
        $user_id = AuthController::getCurrentUserId();
        
        // Get expenses by category
        $stmt = $this->expense->getByCategory($user_id, $category_id);
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $expenses = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $expenses[] = [
                    'id' => $row['id'],
                    'description' => $row['description'],
                    'amount' => $row['amount'],
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name'],
                    'expense_date' => $row['expense_date'],
                    'created_at' => $row['created_at']
                ];
            }
            
            return [
                'status' => 'success',
                'message' => 'Expenses retrieved successfully',
                'code' => 200,
                'count' => $num,
                'expenses' => $expenses
            ];
        } else {
            return [
                'status' => 'success',
                'message' => 'No expenses found for this category',
                'code' => 200,
                'count' => 0,
                'expenses' => []
            ];
        }
    }
    
    /**
     * Get total expenses for the current user
     * 
     * @return array Response with status and total
     */
    public function getTotal() {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }
        
        // Get user ID
        $user_id = AuthController::getCurrentUserId();
        
        // Get total expenses
        $total = $this->expense->getTotalByUser($user_id);
        
        return [
            'status' => 'success',
            'message' => 'Total expenses retrieved successfully',
            'code' => 200,
            'total' => $total
        ];
    }
    
    /**
     * Get expenses summary by category
     * 
     * @return array Response with status and summary
     */
    public function getSummaryByCategory() {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }
        
        // Get user ID
        $user_id = AuthController::getCurrentUserId();
        
        // Get summary by category
        $stmt = $this->expense->getSummaryByCategory($user_id);
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $summary = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $summary[] = [
                    'category' => $row['category_name'],
                    'total' => $row['total']
                ];
            }
            
            return [
                'status' => 'success',
                'message' => 'Category summary retrieved successfully',
                'code' => 200,
                'summary' => $summary
            ];
        } else {
            return [
                'status' => 'success',
                'message' => 'No expenses found',
                'code' => 200,
                'summary' => []
            ];
        }
    }
    
    /**
     * Get daily expenses summary
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Response with status and summary
     */
    public function getDailySummary($start_date, $end_date) {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }
        
        // Get user ID
        $user_id = AuthController::getCurrentUserId();
        
        // Get daily summary
        $stmt = $this->expense->getDailySummary($user_id, $start_date, $end_date);
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $summary = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $summary[] = [
                    'date' => $row['expense_date'],
                    'total' => $row['total']
                ];
            }
            
            return [
                'status' => 'success',
                'message' => 'Daily summary retrieved successfully',
                'code' => 200,
                'summary' => $summary
            ];
        } else {
            return [
                'status' => 'success',
                'message' => 'No expenses found in the date range',
                'code' => 200,
                'summary' => []
            ];
        }
    }
    
    /**
     * Get all categories
     * 
     * @return array Response with status and categories
     */
    public function getAllCategories() {
        // Get all categories
        $stmt = $this->expense->getAllCategories();
        $num = $stmt->rowCount();
        
        if ($num > 0) {
            $categories = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categories[] = [
                    'id' => $row['id'],
                    'name' => $row['name']
                ];
            }
            
            return [
                'status' => 'success',
                'message' => 'Categories retrieved successfully',
                'code' => 200,
                'categories' => $categories
            ];
        } else {
            return [
                'status' => 'success',
                'message' => 'No categories found',
                'code' => 200,
                'categories' => []
            ];
        }
    }
    
    /**
     * Export expenses to CSV
     * 
     * @return array Response with status and CSV data
     */
    public function exportToCSV() {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }
        
        // Get user ID
        $user_id = AuthController::getCurrentUserId();
        
        // Get expenses for CSV export
        $expenses = $this->expense->exportToCSV($user_id);
        
        if (!empty($expenses)) {
            return [
                'status' => 'success',
                'message' => 'Expenses exported successfully',
                'code' => 200,
                'data' => $expenses
            ];
        } else {
            return [
                'status' => 'success',
                'message' => 'No expenses to export',
                'code' => 200,
                'data' => []
            ];
        }
    }
}