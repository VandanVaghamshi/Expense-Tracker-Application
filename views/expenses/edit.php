<?php
/**
 * Edit Expense Page
 * 
 * Allows users to edit an existing expense with description, amount, category, and date.
 */

// Include header
require_once __DIR__ . '/../../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?route=login');
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize expense model
$expense = new Expense($db);

// Initialize expense controller
$expenseController = new ExpenseController($expense);

// Get expense ID from URL
$expense_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get expense details
$response = $expenseController->getOne($expense_id);

if ($response['status'] === 'error') {
    $_SESSION['error_message'] = $response['message'];
    header('Location: index.php?route=expenses');
    exit;
}

$expense_data = $response['expense'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $data = [
        'description' => trim($_POST['description']),
        'amount' => trim($_POST['amount']),
        'category_id' => intval($_POST['category_id']),
        'expense_date' => trim($_POST['expense_date'])
    ];
    
    // Update expense
    $response = $expenseController->update($expense_id, $data);
    
    if ($response['status'] === 'success') {
        $_SESSION['success_message'] = 'Expense updated successfully!';
        header('Location: index.php?route=expenses');
        exit;
    } else {
        $_SESSION['error_message'] = $response['message'];
    }
}

// Get all categories for dropdown
$categories = $expense->getAllCategories();
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>Edit Expense</h2>
        <p class="text-muted">Modify existing expense record</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php?route=expenses" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Expenses
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php 
        echo $_SESSION['error_message']; 
        unset($_SESSION['error_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <form action="index.php?route=edit-expense&id=<?php echo $expense_id; ?>" method="POST" id="editExpenseForm">
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <input type="text" class="form-control" id="description" name="description" 
                       value="<?php echo htmlspecialchars($expense_data['description']); ?>" required>
                <div class="form-text">Enter a brief description of the expense</div>
            </div>
            
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" id="amount" name="amount" 
                           step="0.01" min="0.01" value="<?php echo $expense_data['amount']; ?>" required>
                </div>
                <div class="form-text">Enter the expense amount</div>
            </div>
            
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php while ($category = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo ($category['id'] == $expense_data['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <div class="form-text">Select the expense category</div>
            </div>
            
            <div class="mb-3">
                <label for="expense_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="expense_date" name="expense_date" 
                       value="<?php echo $expense_data['expense_date']; ?>" required>
                <div class="form-text">Select the date of the expense</div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php?route=expenses" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Expense</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editExpenseForm');
        
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const description = document.getElementById('description');
            const amount = document.getElementById('amount');
            const category = document.getElementById('category_id');
            const date = document.getElementById('expense_date');
            
            // Validate description
            if (description.value.trim() === '') {
                isValid = false;
                description.classList.add('is-invalid');
            } else {
                description.classList.remove('is-invalid');
            }
            
            // Validate amount
            if (amount.value <= 0) {
                isValid = false;
                amount.classList.add('is-invalid');
            } else {
                amount.classList.remove('is-invalid');
            }
            
            // Validate category
            if (category.value === '') {
                isValid = false;
                category.classList.add('is-invalid');
            } else {
                category.classList.remove('is-invalid');
            }
            
            // Validate date
            if (date.value === '') {
                isValid = false;
                date.classList.add('is-invalid');
            } else {
                date.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>