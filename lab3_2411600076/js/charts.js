
const dashboardCharts = (function () {
    'use strict';

    let categoryChart = null;
    let statusChart = null;
    let topChart = null;


    const COLORS = {
        primary: '#202940',
        secondary: '#4B4038',
        accent: '#CAAA98',
        success: '#28a745',
        warning: '#f3a712',
        danger: '#dc3545'
    };

    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 500 }
    };

    function renderCategoryValueChart(categorySummary) {
        const canvas = document.getElementById('categoryValueChart');
        if (!canvas) return;

        const labels = categorySummary.map(c => c.category);
        const values = categorySummary.map(c => c.totalValue);

        if (categoryChart) categoryChart.destroy();
        categoryChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Inventory Value (₱)',
                    data: values,
                    backgroundColor: COLORS.primary,
                    hoverBackgroundColor: COLORS.secondary,
                    borderRadius: 6,
                    maxBarThickness: 48
                }]
            },
            options: {
                ...baseOptions,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `₱${ctx.parsed.y.toFixed(2)}`
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { ticks: { autoSkip: false, maxRotation: 30, minRotation: 0 } }
                }
            }
        });
    }

    function renderStockStatusChart(stats) {
        const canvas = document.getElementById('stockStatusChart');
        if (!canvas) return;

        if (statusChart) statusChart.destroy();
        statusChart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                datasets: [{
                    data: [stats.inStock, stats.lowStock, stats.outOfStock],
                    backgroundColor: [COLORS.success, COLORS.warning, COLORS.danger],
                    hoverOffset: 8
                }]
            },
            options: {
                ...baseOptions,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '60%'
            }
        });
    }

    function renderTopProductsChart(items) {
        const canvas = document.getElementById('topProductsChart');
        if (!canvas) return;

        const top5 = [...items]
            .sort((a, b) => b.totalValue - a.totalValue)
            .slice(0, 5);

        if (topChart) topChart.destroy();
        topChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: top5.map(i => i.name),
                datasets: [{
                    label: 'Value (₱)',
                    data: top5.map(i => i.totalValue),
                    backgroundColor: COLORS.secondary,
                    hoverBackgroundColor: COLORS.primary,
                    borderRadius: 6
                }]
            },
            options: {
                ...baseOptions,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `₱${ctx.parsed.x.toFixed(2)}`
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
    }

    function renderAll(list) {
        const source = list || dataManager.getProducts();
        renderCategoryValueChart(dataManager.getCategorySummary(source));
        renderStockStatusChart(dataManager.getStockStatistics(source));
        renderTopProductsChart(source);
    }

    return {
        renderAll,
        renderCategoryValueChart,
        renderStockStatusChart,
        renderTopProductsChart
    };
})();
