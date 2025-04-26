<?php

/**
 * Dashboard Controller
 * 
 * Handles dashboard-related operations including generating chart data
 * and statistics for expense visualization.
 */

require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class DashboardController
{
    private $expense;

    /**
     * Get daily chart data
     * 
     * @param string|null $start_date Start date (Y-m-d format)
     * @param string|null $end_date End date (Y-m-d format)
     * @return array Response with status and chart data
     */
    public function getDailyChartData($start_date = null, $end_date = null)
    {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }

        // Set default dates if not provided
        if ($end_date === null) {
            $end_date = date('Y-m-d');
        }
        if ($start_date === null) {
            $start_date = date('Y-m-d', strtotime('-30 days'));
        }

        // Get user ID
        $user_id = AuthController::getCurrentUserId();

        // Get expenses for the date range
        $stmt = $this->expense->getByDateRange($user_id, $start_date, $end_date);

        // Initialize data arrays
        $daily_totals = [];
        $categories = [];
        $dates = [];

        // Generate all dates in range
        $current = strtotime($start_date);
        $end = strtotime($end_date);

        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $daily_totals[$date] = 0;
            $categories[$date] = [];
            $dates[] = date('M d', $current);
            $current = strtotime('+1 day', $current);
        }

        // Calculate daily totals and collect categories
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $date = $row['expense_date'];
            if (isset($daily_totals[$date])) {
                $daily_totals[$date] += $row['amount'];
                $categories[$date][] = $row['category_name'];
            }
        }

        // Format categories for tooltip
        $formatted_categories = [];
        foreach ($categories as $date => $cats) {
            $formatted_categories[] = implode(', ', array_unique($cats));
        }

        return [
            'status' => 'success',
            'message' => 'Daily chart data retrieved successfully',
            'code' => 200,
            'chart_data' => [
                'labels' => $dates,
                'datasets' => [
                    [
                        'label' => 'Daily Expenses',
                        'data' => array_values($daily_totals),
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1,
                        'tension' => 0.4
                    ]
                ],
                'categories' => $formatted_categories
            ]
        ];
    }

    /**
     * Constructor
     * 
     * @param Expense $expense Expense model instance
     */
    public function __construct($expense)
    {
        $this->expense = $expense;
    }

    /**
     * Get chart data for monthly expenses
     * 
     * @param int $year Year (default: current year)
     * @return array Response with status and chart data
     */
    public function getMonthlyChartData($year = null)
    {
        // Check if user is logged in
        if (!AuthController::isLoggedIn()) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized access',
                'code' => 401
            ];
        }

        // Set default year to current year if not provided
        if ($year === null) {
            $year = date('Y');
        }

        // Get user ID
        $user_id = AuthController::getCurrentUserId();

        // Calculate start and end dates for the year
        $start_date = $year . '-01-01';
        $end_date = $year . '-12-31';

        // Get expenses for the year
        $stmt = $this->expense->getByDateRange($user_id, $start_date, $end_date);

        // Initialize monthly data
        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        $monthly_totals = array_fill(0, 12, 0);

        // Calculate monthly totals
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $month = date('n', strtotime($row['expense_date'])) - 1; // 0-based index
            $monthly_totals[$month] += $row['amount'];
        }

        return [
            'status' => 'success',
            'message' => 'Monthly chart data retrieved successfully',
            'code' => 200,
            'chart_data' => [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => 'Monthly Expenses',
                        'data' => $monthly_totals,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ]
        ];
    }

    /**
     * Get chart data for category breakdown
     * 
     * @return array Response with status and chart data
     */
    public function getCategoryChartData()
    {
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

        // Get category summary
        $stmt = $this->expense->getSummaryByCategory($user_id);

        $categories = [];
        $amounts = [];
        $background_colors = [];

        // Generate random colors for each category
        $color_palette = [
            'rgba(255, 99, 132, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)',
            'rgba(255, 99, 132, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)'
        ];

        $i = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row['category_name'];
            $amounts[] = $row['total'];
            $background_colors[] = $color_palette[$i % count($color_palette)];
            $i++;
        }

        return [
            'status' => 'success',
            'message' => 'Category chart data retrieved successfully',
            'code' => 200,
            'chart_data' => [
                'labels' => $categories,
                'datasets' => [
                    [
                        'data' => $amounts,
                        'backgroundColor' => $background_colors,
                        'borderWidth' => 1
                    ]
                ]
            ]
        ];
    }



    /**
     * Get dashboard statistics
     * 
     * @return array Response with status and statistics
     */
    public function getStatistics()
    {
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

        // Get current month expenses
        $month_total = $this->expense->getTotalByMonth(date('Y-m'));

        // Get current week expenses
        $current_week_start = date('Y-m-d', strtotime('monday this week'));
        $current_week_end = date('Y-m-d', strtotime('sunday this week'));
        $stmt_week = $this->expense->getByDateRange($user_id, $current_week_start, $current_week_end);
        $week_total = 0;
        while ($row = $stmt_week->fetch(PDO::FETCH_ASSOC)) {
            $week_total += $row['amount'];
        }

        // Get today's expenses
        $today = date('Y-m-d');
        $today_total = $this->expense->getTotalByDate($today);

        // Get expense count
        $stmt_all = $this->expense->readAll($user_id);
        $expense_count = $stmt_all->rowCount();

        return [
            'status' => 'success',
            'message' => 'Statistics retrieved successfully',
            'code' => 200,
            'statistics' => [
                'total_expenses' => $total,
                'month_expenses' => $month_total,
                'week_expenses' => $week_total,
                'today_expenses' => $today_total,
                'expense_count' => $expense_count
            ]
        ];
    }

    public function getCategoryBreakdown()
    {
        // Example: return an array of categories
        return [
            'Food' => 1200,
            'Transport' => 600,
            'Utilities' => 300
        ];
    }
}
