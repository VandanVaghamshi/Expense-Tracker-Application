/**
 * Expense-related JavaScript functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle CSV export
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'index.php?route=export-csv';
        });
    }

    // Initialize daily chart if it exists
    const dailyChartCanvas = document.getElementById('dailyChart');
    if (dailyChartCanvas) {
        initializeDailyChart();
    }
});

// Initialize daily expenses chart
function initializeDailyChart() {
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    fetch('index.php?route=daily-chart-data')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const chartData = data.chart_data;
                new Chart(dailyCtx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: chartData.datasets
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    afterBody: function(context) {
                                        const index = context[0].dataIndex;
                                        return 'Categories: ' + chartData.categories[index];
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
}

// Update daily chart based on time range
function updateDailyChart(range) {
    const buttons = document.querySelectorAll('[onclick^="updateDailyChart"]');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');

    const endDate = new Date();
    const startDate = new Date();
    if (range === 'week') {
        startDate.setDate(startDate.getDate() - 7);
    } else {
        startDate.setDate(startDate.getDate() - 30);
    }

    const params = new URLSearchParams({
        start_date: startDate.toISOString().split('T')[0],
        end_date: endDate.toISOString().split('T')[0]
    });

    fetch(`index.php?route=daily-chart-data&${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const chart = Chart.getChart('dailyChart');
                if (chart) {
                    chart.data.labels = data.chart_data.labels;
                    chart.data.datasets = data.chart_data.datasets;
                    chart.update();
                }
            }
        });
}