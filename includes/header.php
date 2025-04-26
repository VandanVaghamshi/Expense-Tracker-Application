<?php
/**
 * Header Template
 * 
 * Contains the HTML header, navigation, and opening body tags.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - <?php echo ucfirst(isset($_GET['route']) ? $_GET['route'] : 'Home'); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/expenses.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Expense Tracker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?route=dashboard">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?route=expenses">Expenses</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?route=add-expense">Add Expense</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?route=logout">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?route=login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?route=register">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container mb-4">