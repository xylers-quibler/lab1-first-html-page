const REFRESH_INTERVAL = 30000;

function animateNumber(element, newValue, isMoney) {
    const oldValue = parseFloat(element.dataset.value || element.textContent.replace(/[₱,]/g, '')) || 0;
    const steps = 20;
    let step = 0;

    const timer = setInterval(() => {
        step++;
        const current = oldValue + (newValue - oldValue) * (step / steps);
        element.textContent = isMoney
            ? '₱' + current.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : Math.round(current);

        if (step >= steps) {
            clearInterval(timer);
        }
    }, 25);

    element.dataset.value = newValue;
}

async function loadStats() {
    const response = await fetch('/api/dashboard/stats');
    const data = await response.json();

    animateNumber(document.getElementById('stat-total-products'), data.total_products, false);
    animateNumber(document.getElementById('stat-total-value'), data.total_value, true);
    animateNumber(document.getElementById('stat-low-stock'), data.low_stock, false);
    animateNumber(document.getElementById('stat-out-of-stock'), data.out_of_stock, false);
    document.getElementById('last-updated').textContent = data.updated_at;
}

async function loadLowStock() {
    const response = await fetch('/api/products/low-stock');
    const products = await response.json();
    const box = document.getElementById('low-stock-box');

    if (products.length === 0) {
        box.classList.add('d-none');
        return;
    }

    box.classList.remove('d-none');
    document.getElementById('low-stock-count').textContent = products.length;

    document.getElementById('low-stock-list').innerHTML = products.map(product => {
        const stockText = product.quantity <= 0 ? '<strong>OUT OF STOCK</strong>' : product.quantity + ' left';

        return `<li><a href="/products/${product.id}">${product.name}</a>: ${stockText} (reorder at ${product.reorder_level})</li>`;
    }).join('');
}

async function loadRecentTransactions() {
    const response = await fetch('/api/transactions/recent');
    const transactions = await response.json();
    const table = document.getElementById('recent-transactions-table');

    if (transactions.length === 0) {
        table.innerHTML = '<tr><td colspan="5" class="text-center">No transactions yet.</td></tr>';
        return;
    }

    table.innerHTML = transactions.map(transaction => `
        <tr>
            <td>${transaction.date}</td>
            <td><a href="/transactions/${transaction.id}">${transaction.product}</a></td>
            <td><span class="badge bg-${transaction.color}">${transaction.type}</span></td>
            <td>${transaction.quantity > 0 ? '+' : ''}${transaction.quantity}</td>
            <td>${transaction.user}</td>
        </tr>
    `).join('');
}

async function refreshDashboard() {
    const errorBox = document.getElementById('update-error');

    try {
        await loadStats();
        await loadLowStock();
        await loadRecentTransactions();
        errorBox.classList.add('d-none');
    } catch (error) {
        errorBox.classList.remove('d-none');
    }
}

setInterval(refreshDashboard, REFRESH_INTERVAL);
