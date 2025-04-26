<?php
/**
 * Expense Tracker Application
 * 
 * Main entry point for the application that handles routing and initialization.
 */

// Start session
session_start();

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ExpenseController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/ExportController.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$user = new User($db);
$expense = new Expense($db);

// Initialize controllers
$authController = new AuthController($user);
$expenseController = new ExpenseController($expense);
$dashboardController = new DashboardController($expense);

// Simple router
$route = isset($_GET['route']) ? $_GET['route'] : 'home';

// Check if user is logged in for protected routes
$protected_routes = ['dashboard', 'expenses', 'add-expense', 'edit-expense', 'export-csv'];
if (in_array($route, $protected_routes) && !isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: index.php?route=login');
    exit;
}

// Handle routing
switch ($route) {
    case 'register':
        include __DIR__ . '/views/auth/register.php';
        break;
    case 'login':
        include __DIR__ . '/views/auth/login.php';
        break;
    case 'logout':
        // Destroy session and redirect to login
        session_destroy();
        header('Location: index.php?route=login');
        exit;
    case 'dashboard':
        include __DIR__ . '/views/dashboard/index.php';
        break;
    case 'expenses':
        include __DIR__ . '/views/expenses/list.php';
        break;
    case 'add-expense':
        include __DIR__ . '/views/expenses/add.php';
        break;
    case 'edit-expense':
        include __DIR__ . '/views/expenses/edit.php';
        break;
    case 'export-csv':
        // Initialize export controller
        $exportController = new ExportController($expense);
        $exportController->exportToCSV();
        break;
    case 'daily-chart-data':
        // Get start and end dates from request
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        
        // Get daily chart data
        $dailyData = $dashboardController->getDailyChartData($start_date, $end_date);
        
        // Set JSON header
        header('Content-Type: application/json');
        echo json_encode($dailyData);
        exit;
        break;
    default:
        // Default to home/login page
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?route=dashboard');
        } else {
            header('Location: index.php?route=login');
        }
        exit;
}