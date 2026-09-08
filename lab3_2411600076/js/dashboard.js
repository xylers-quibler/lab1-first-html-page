document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('isLoggedIn') !== 'true') {
        window.location.href = 'index.html';
        return;
    }

    const username = localStorage.getItem('user') || 'User';
    updateGreeting(username);
    updateStatistics();
    renderActivityTable();
    setupLogout();

    const userName = document.getElementById('userName');
    if (userName) userName.textContent = username;
});

function updateGreeting(username) {
    const hour = new Date().getHours();
    let timeOfDay = '';
    if (hour >= 5 && hour < 12) timeOfDay = 'Good Morning';
    else if (hour >= 12 && hour < 17) timeOfDay = 'Good Afternoon';
    else if (hour >= 17 && hour < 21) timeOfDay = 'Good Evening';
    else timeOfDay = 'Good Night';

    const greeting = document.getElementById('greeting');
    if (greeting) greeting.textContent = `${timeOfDay}, ${username}!`;
}

/**
 * Lab 4: Dashboard statistics are calculated from the current inventory
 * instead of using hardcoded values.
 */
function updateStatistics() {
    if (typeof dataManager === 'undefined') return;
    const products = dataManager.getProducts();
    const stats = dataManager.getStockStatistics(products);

    const overview = [
        { id: 1, title: 'Daily Orders', value: '47', color: 'text-primary' },
        { id: 2, title: 'Revenue', value: '₱2,450', color: 'text-success' },
        { id: 3, title: 'Menu Items', value: String(stats.totalProducts), color: 'text-info' },
        { id: 4, title: 'Available Tables', value: '8', color: 'text-warning' }
    ];
    const inventory = [
        { id: 5, title: 'Inventory Items', value: stats.totalProducts, color: 'text-primary' },
        { id: 6, title: 'Inventory Value', value: `₱${stats.totalValue.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`, color: 'text-success' },
        { id: 7, title: 'Low Stock', value: stats.lowStock, color: 'text-warning' },
        { id: 8, title: 'Out of Stock', value: stats.outOfStock, color: 'text-danger' }
    ];
    [...overview, ...inventory].forEach(stat => {
        const titleEl = document.getElementById(`stat${stat.id}-title`);
        const valueEl = document.getElementById(`stat${stat.id}-value`);
        if (titleEl) titleEl.textContent = stat.title;
        if (valueEl) { valueEl.textContent = stat.value; valueEl.className = `card-text fw-bold ${stat.color}`; }
    });
}

const ACTIVITY_STORAGE_KEY = 'restaurantManagerRecentActivity';
const MAX_ACTIVITY_ITEMS = 8;

function getActivityHistory() {
    try {
        const saved = JSON.parse(localStorage.getItem(ACTIVITY_STORAGE_KEY) || '[]');
        return Array.isArray(saved) ? saved : [];
    } catch (error) {
        return [];
    }
}

function saveActivityHistory(history) {
    localStorage.setItem(ACTIVITY_STORAGE_KEY, JSON.stringify(history.slice(0, MAX_ACTIVITY_ITEMS)));
}

function addActivity(message, status = 'info') {
    const history = getActivityHistory();
    history.unshift({
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        message,
        status
    });
    saveActivityHistory(history);
    renderActivityTable();
}

function renderActivityTable() {
    const tableBody = document.getElementById('activityTableBody');
    if (!tableBody) return;

    const activities = getActivityHistory();
    tableBody.innerHTML = '';

    if (!activities.length) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 3;
        cell.className = 'text-center text-muted py-4';
        cell.textContent = 'No recent inventory activity yet.';
        row.appendChild(cell);
        tableBody.appendChild(row);
        return;
    }

    activities.forEach(activity => {
        const row = document.createElement('tr');
        const timeCell = document.createElement('td');
        const messageCell = document.createElement('td');
        const statusCell = document.createElement('td');
        const badge = document.createElement('span');

        timeCell.textContent = activity.time;
        messageCell.textContent = activity.message;

        const status = activity.status || 'info';
        badge.textContent = status;
        badge.className = 'badge';
        if (status === 'success') badge.classList.add('bg-success');
        else if (status === 'warning') badge.classList.add('bg-warning', 'text-dark');
        else if (status === 'danger') badge.classList.add('bg-danger');
        else badge.classList.add('bg-info', 'text-dark');

        statusCell.appendChild(badge);
        row.append(timeCell, messageCell, statusCell);
        tableBody.appendChild(row);
    });
}

function setupLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutLink = document.getElementById('logoutLink');

    function performLogout(e) {
        e.preventDefault();
        localStorage.removeItem('isLoggedIn');
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    }

    if (logoutBtn) logoutBtn.addEventListener('click', performLogout);
    if (logoutLink) logoutLink.addEventListener('click', performLogout);
}
