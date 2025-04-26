<?php
/**
 * Export Controller
 * 
 * Handles data export operations like CSV export.
 */

require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../includes/Logger.php';

class ExportController {
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
     * Export expenses to CSV
     * 
     * @return void
     */
    public function exportToCSV() {
        try {
            // Check if user is logged in
            if (!AuthController::isLoggedIn()) {
                Logger::error('Unauthorized CSV export attempt');
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
                exit;
            }
        
            // Get user ID
            $user_id = AuthController::getCurrentUserId();
            Logger::info('Starting CSV export', ['user_id' => $user_id]);
            
            // Set expense user ID
            $this->expense->user_id = $user_id;
            
            // Get all expenses
            $stmt = $this->expense->readAll($user_id);
            
            if (!$stmt) {
                Logger::error('Failed to retrieve expenses for export', ['user_id' => $user_id]);
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve expenses data']);
                exit;
            }
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="expenses.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, "\xEF\xBB\xBF");
        
        // Add headers
        fputcsv($output, ['Description', 'Category', 'Amount', 'Date', 'Created At']);
        
        // Add data
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['description'],
                $row['category_name'],
                $row['amount'],
                $row['expense_date'],
                $row['created_at']
            ]);
        }
        
            // Close output stream
            if (fclose($output)) {
                Logger::info('CSV export and download completed successfully', [
                    'user_id' => $user_id,
                    'file' => 'expenses.csv'
                ]);
            } else {
                Logger::error('CSV export completed but download failed', [
                    'user_id' => $user_id,
                    'file' => 'expenses.csv'
                ]);
            }
            exit;
        } catch (Exception $e) {
            Logger::error('CSV export failed', [
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ]);
            
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to export expenses: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}