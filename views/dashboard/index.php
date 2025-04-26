<?php
/**
 * Dashboard Page
 * 
 * Displays expense summary and charts for visualization.
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

// Initialize dashboard controller
$dashboardController = new DashboardController($expense);

// Get user ID
$user_id = $_SESSION['user_id'];

// Set expense user ID
$expense->user_id = $user_id;

// Get current year
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get monthly chart data
$monthlyData = $dashboardController->getMonthlyChartData($year);

// Get category chart data
$categoryData = $dashboardController->getCategoryChartData();

// Get total expenses
$totalExpenses = $expense->getTotalByUser($user_id);

// Get recent expenses
$recentExpenses = $expense->getRecentByUser(5);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2>Dashboard</h2>
        <p class="text-muted">Overview of your expenses</p>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Expenses</h5>
                <h2 class="display-6">$<?php echo number_format($totalExpenses, 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">This Month</h5>
                <h2 class="display-6">$<?php echo number_format($expense->getTotalByMonth(date('Y-m')), 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Today</h5>
                <h2 class="display-6">$<?php echo number_format($expense->getTotalByDate(date('Y-m-d')), 2); ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Monthly Expenses (<?php echo $year; ?>)</h5>
                <div>
                    <a href="index.php?route=dashboard&year=<?php echo $year - 1; ?>" class="btn btn-sm btn-outline-secondary">Previous Year</a>
                    <a href="index.php?route=dashboard&year=<?php echo $year + 1; ?>" class="btn btn-sm btn-outline-secondary">Next Year</a>
                </div>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">Expense Categories</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Daily Expenses Chart -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daily Expenses (Last 30 Days)</h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updateDailyChart('week')">Week</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary active" onclick="updateDailyChart('month')">Month</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="dailyChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Expenses -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Expenses</h5>
                <a href="index.php?route=expenses" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (count($recentExpenses) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentExpenses as $expense): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($expense['description']); ?></td>
                                        <td><?php echo htmlspecialchars($expense['category_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($expense['expense_date'])); ?></td>
                                        <td class="text-end">$<?php echo number_format($expense['amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">No expenses found. <a href="index.php?route=add-expense">Add your first expense</a>.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Charts -->
<script>
    // Monthly Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Monthly Expenses ($)',
                data: <?php echo json_encode(array_values($monthlyData['chart_data']['datasets'][0]['data'])); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Expenses: $' + context.raw.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            }
        }
    });
    
    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($categoryData['chart_data']['labels']); ?>,
            datasets: [{
                data: <?php echo json_encode($categoryData['chart_data']['datasets'][0]['data']); ?>,
                backgroundColor: <?php echo json_encode($categoryData['chart_data']['datasets'][0]['backgroundColor']); ?>,
                borderColor: <?php echo json_encode($categoryData['chart_data']['datasets'][0]['backgroundColor']); ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
</script>

<?php
// Include footer
require_once __DIR__ . '/../../includes/footer.php';
?>
</script>

<?php
// Include footer
require_once __DIR__ . '/../../includes/footer.php';
?>