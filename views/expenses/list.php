<?php
/**
 * Expense List Page
 * 
 * Displays all expenses for the logged-in user with options to add, edit, and delete.
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

// Get user ID
$user_id = $_SESSION['user_id'];

// Set expense user ID
$expense->user_id = $user_id;

// Process delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $response = $expenseController->delete($_GET['id']);
    
    if ($response['status'] === 'success') {
        $_SESSION['success_message'] = 'Expense deleted successfully!';
    } else {
        $_SESSION['error_message'] = $response['message'];
    }
    
    // Redirect to remove query parameters
    header('Location: index.php?route=expenses');
    exit;
}

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get expenses with pagination
$expenses = $expense->getAllByUser($limit, $offset);

// Get total count for pagination
$total_expenses = $expense->countByUser();
$total_pages = ceil($total_expenses / $limit);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>My Expenses</h2>
        <p class="text-muted">Manage your expenses</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php?route=add-expense" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Expense
        </a>
        <a href="#" class="btn btn-outline-secondary" id="exportBtn">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php 
        echo $_SESSION['success_message']; 
        unset($_SESSION['success_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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
        <?php if (count($expenses) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $exp): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($exp['description']); ?></td>
                                <td><?php echo htmlspecialchars($exp['category_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($exp['expense_date'])); ?></td>
                                <td class="text-end">$<?php echo number_format($exp['amount'], 2); ?></td>
                                <td class="text-center">
                                    <a href="index.php?route=edit-expense&id=<?php echo $exp['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger delete-expense" data-id="<?php echo $exp['id']; ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Expense pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?route=expenses&page=<?php echo $page - 1; ?>">
                                Previous
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?route=expenses&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?route=expenses&page=<?php echo $page + 1; ?>">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-4">
                <p>No expenses found.</p>
                <a href="index.php?route=add-expense" class="btn btn-primary">
                    Add Your First Expense
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this expense? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger" id="confirmDelete">Delete</a>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for delete confirmation and CSV export -->
<script>
    // Delete confirmation and CSV export
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const exportBtn = document.getElementById('exportBtn');
        let expenseId = null;

        // Handle CSV export
        exportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'export.php';
        });
        
        // Add event listeners to all delete buttons
        document.querySelectorAll('.delete-expense').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                expenseId = this.getAttribute('data-id');
                deleteModal.show();
            });
        });
        
        // Set the href for the confirm delete button
        confirmDeleteBtn.addEventListener('click', function() {
            if (expenseId) {
                window.location.href = `index.php?route=expenses&action=delete&id=${expenseId}`;
            }
        });
        
        // CSV Export
        document.getElementById('exportBtn').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'index.php?route=expenses&action=export';
        });
    });
</script>

<?php
// Include footer
require_once __DIR__ . '/../../includes/footer.php';
?>