<?php
/**
 * Login Page
 * 
 * Allows users to log in to their account.
 */

// Include header
require_once __DIR__ . '/../../includes/header.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?route=dashboard');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize user model
    $user = new User($db);
    
    // Initialize auth controller
    $authController = new AuthController($user);
    
    // Process login
    $response = $authController->login([
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? ''
    ]);
    
    // Check response
    if ($response['status'] === 'success') {
        // Redirect to dashboard
        header('Location: index.php?route=dashboard');
        exit;
    } else {
        // Display error message
        $error = $response['message'];
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Login</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">Don't have an account? <a href="index.php?route=register">Register</a></p>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../../includes/footer.php';
?>