<?php
/**
 * Export Endpoint
 * 
 * Handles CSV export requests.
 */

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Expense.php';
require_once __DIR__ . '/controllers/ExportController.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize expense model
$expense = new Expense($db);

// Initialize export controller
$exportController = new ExportController($expense);

// Export to CSV
$exportController->exportToCSV();