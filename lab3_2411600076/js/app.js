/**
 * app.js
 * ------------------------------------------------------------------
 * DOM handlers for the Restaurant Manager Dashboard (Lab 4, Part 5).
 * Wires dataManager (state) and dashboardCharts (visualization) to
 * the actual page: filter controls, live search, low-stock alerts,
 * CSV export, and a simulated real-time feed.
 * ------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', () => {
    // Guard: only run on pages that actually have the inventory section.
    const tableBody = document.getElementById('menuTableBody');
    if (!tableBody || typeof dataManager === 'undefined') return;

    const els = {
        categoryFilter: document.getElementById('categoryFilter'),
        statusFilter: document.getElementById('statusFilter'),
        priceMin: document.getElementById('priceMinInput'),
        priceMax: document.getElementById('priceMaxInput'),
        applyBtn: document.getElementById('applyFiltersBtn'),
        resetBtn: document.getElementById('resetFiltersBtn'),
        searchInput: document.getElementById('searchInput'),
        exportBtn: document.getElementById('exportCsvBtn'),
        tableBody,
        resultsCount: document.getElementById('resultsCount'),
        alertBox: document.getElementById('lowStockAlert'),
        alertText: document.getElementById('lowStockAlertText'),
        toastContainer: document.getElementById('toastContainer'),
        adjustStockModal: document.getElementById('adjustStockModal'),
        adjustStockProductName: document.getElementById('adjustStockProductName'),
        adjustStockSku: document.getElementById('adjustStockSku'),
        adjustStockCurrent: document.getElementById('adjustStockCurrent'),
        adjustStockQuantity: document.getElementById('adjustStockQuantity'),
        adjustStockError: document.getElementById('adjustStockError'),
        saveStockBtn: document.getElementById('saveStockBtn')
    };

    let currentQuery = '';

    // ---- Boot -----------------------------------------------------
    renderLoadingRow();

    dataManager.initializeData().then(() => {
        populateCategoryDropdown();
        refreshView();
        updateStatistics();
        addActivity('Inventory loaded successfully from the data source.', 'success');
        attachListeners();
        setupStockAdjustment();
        startRealtimeSimulation();
    }).catch(error => {
        console.error('Dashboard initialization failed:', error);
        addActivity('Unable to initialize inventory data.', 'danger');
    });

    // ---- Setup ------------------------------------------------------

    function populateCategoryDropdown() {
        dataManager.getCategories().forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = cat;
            els.categoryFilter.appendChild(opt);
        });
    }

    function attachListeners() {
        els.categoryFilter.addEventListener('change', (e) => {
            dataManager.filterByCategory(e.target.value);
            refreshView();
        });

        els.statusFilter.addEventListener('change', (e) => {
            dataManager.filterByStockStatus(e.target.value);
            refreshView();
        });

        let priceDebounce;
        function handlePriceChange() {
            clearTimeout(priceDebounce);
            priceDebounce = setTimeout(() => {
                dataManager.filterByPriceRange(els.priceMin.value, els.priceMax.value);
                refreshView();
            }, 250);
        }
        els.priceMin.addEventListener('input', handlePriceChange);
        els.priceMax.addEventListener('input', handlePriceChange);

        els.applyBtn.addEventListener('click', () => {
            dataManager.filterByCategory(els.categoryFilter.value);
            dataManager.filterByStockStatus(els.statusFilter.value);
            dataManager.filterByPriceRange(els.priceMin.value, els.priceMax.value);
            dataManager.updateSearchResults(currentQuery);
            refreshView();
            showToast('Filters applied', 'Inventory view updated.', 'success');
        });

        els.resetBtn.addEventListener('click', () => {
            dataManager.resetFilters();
            currentQuery = '';
            els.categoryFilter.value = 'all';
            els.statusFilter.value = 'all';
            els.priceMin.value = '';
            els.priceMax.value = '';
            els.searchInput.value = '';
            refreshView();
        });

        let searchDebounce;
        els.searchInput.addEventListener('input', (e) => {
            currentQuery = e.target.value;
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(() => {
                dataManager.updateSearchResults(currentQuery);
                refreshView();
            }, 150);
        });

        els.exportBtn.addEventListener('click', () => {
            const filtered = dataManager.applyFilters();
            if (!filtered.length) {
                showToast('Nothing to export', 'No menu items match the current filters.', 'warning');
                return;
            }
            const csv = dataManager.exportToCSV(filtered);
            const stamp = new Date().toISOString().slice(0, 10);
            dataManager.downloadCSV(csv, `menu_inventory_${stamp}.csv`);
            addActivity(`Exported ${filtered.length} menu item(s) to CSV.`, 'success');
            showToast('Export complete', `${filtered.length} menu item(s) exported to CSV.`, 'success');
        });
    }

    // ---- Rendering --------------------------------------------------

    function renderLoadingRow() {
        els.tableBody.innerHTML = `
            <tr><td colspan="8" class="text-center text-muted py-4">Loading menu inventory...</td></tr>
        `;
    }

    function statusBadgeClass(status) {
        if (status === 'In Stock') return 'bg-success';
        if (status === 'Low Stock') return 'bg-warning text-dark';
        return 'bg-danger';
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    function escapeRegExp(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /** Wraps the matching substring of `text` in <mark> for live search highlighting. */
    function highlight(text, query) {
        const safe = escapeHtml(text);
        if (!query) return safe;
        const re = new RegExp(`(${escapeRegExp(query)})`, 'ig');
        return safe.replace(re, '<mark>$1</mark>');
    }

    /**
     * Rebuilds the menu item table body from scratch using DOM methods:
     * innerHTML reset (removeChild-equivalent clear), then createElement
     * + appendChild per row. Kept as explicit DOM construction (rather
     * than one big innerHTML string) so individual rows can carry
     * data attributes and per-row classes for the low-stock highlight
     * and the real-time "flash" animation.
     */
    function renderTable(list, query) {
        els.tableBody.innerHTML = '';

        if (!list.length) {
            const emptyRow = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 7;
            cell.className = 'text-center text-muted py-4';
            cell.textContent = 'No menu items match the current filters.';
            emptyRow.appendChild(cell);
            els.tableBody.appendChild(emptyRow);
        } else {
            list.forEach(i => {
                const row = document.createElement('tr');
                if (i.stockStatus === 'Low Stock') row.classList.add('row-low-stock');
                if (i.stockStatus === 'Out of Stock') row.classList.add('row-out-of-stock');
                row.dataset.sku = i.sku;

                row.innerHTML = `
                    <td>${highlight(i.sku, query)}</td>
                    <td>${highlight(i.name, query)}</td>
                    <td>${escapeHtml(i.category)}</td>
                    <td>${i.quantity}</td>
                    <td>₱${i.unitPrice.toFixed(2)}</td>
                    <td>₱${i.totalValue.toFixed(2)}</td>
                    <td><span class="badge status-badge ${statusBadgeClass(i.stockStatus)}">${i.stockStatus}</span></td>
                    <td><button type="button" class="btn btn-sm btn-outline-primary adjust-stock-btn" data-sku="${escapeHtml(i.sku)}">Adjust Stock</button></td>
                `;
                els.tableBody.appendChild(row);
            });
        }

        els.resultsCount.textContent = `Showing ${list.length} of ${dataManager.getProducts().length} menu items`;
    }

    function openAdjustStockModal(sku) {
        const item = dataManager.getProductById(sku);
        if (!item || !els.adjustStockModal || typeof bootstrap === 'undefined') return;
        els.adjustStockProductName.textContent = item.name;
        els.adjustStockSku.textContent = item.sku;
        els.adjustStockCurrent.textContent = item.quantity;
        els.adjustStockQuantity.value = item.quantity;
        els.adjustStockError.classList.add('d-none');
        els.adjustStockError.textContent = '';
        els.saveStockBtn.dataset.sku = item.sku;
        bootstrap.Modal.getOrCreateInstance(els.adjustStockModal).show();
    }

    function setupStockAdjustment() {
        els.tableBody.addEventListener('click', event => {
            const button = event.target.closest('.adjust-stock-btn');
            if (button) openAdjustStockModal(button.dataset.sku);
        });

        els.saveStockBtn.addEventListener('click', async () => {
            const sku = els.saveStockBtn.dataset.sku;
            const quantity = Number(els.adjustStockQuantity.value);
            if (!Number.isInteger(quantity) || quantity < 0) {
                els.adjustStockError.textContent = 'Enter a whole number greater than or equal to 0.';
                els.adjustStockError.classList.remove('d-none');
                return;
            }
            els.saveStockBtn.disabled = true;
            els.saveStockBtn.textContent = 'Saving...';
            try {
                const previous = dataManager.getProductById(sku);
                const updated = await dataManager.updateProductStock(sku, quantity);
                bootstrap.Modal.getOrCreateInstance(els.adjustStockModal).hide();
                refreshView();
                addActivity(`${updated.name}: stock manually changed from ${previous ? previous.quantity : '?'} to ${updated.quantity}.`, 'success');
                showToast('Stock updated', `${updated.name} is now at ${updated.quantity} unit(s).`, 'success');
            } catch (error) {
                console.error('Stock update failed:', error);
                els.adjustStockError.textContent = error.message || 'Unable to update stock.';
                els.adjustStockError.classList.remove('d-none');
                showToast('Update failed', 'The stock change could not be saved to the API.', 'danger');
            } finally {
                els.saveStockBtn.disabled = false;
                els.saveStockBtn.textContent = 'Save Stock';
            }
        });
    }

    /** Low-stock banner always reflects the whole menu, not just the current filter. */
    function updateLowStockAlert() {
        const lowStock = dataManager.getLowStockProducts();
        if (lowStock.length === 0) {
            els.alertBox.classList.add('d-none');
            return;
        }
        const names = lowStock.slice(0, 3).map(i => i.name).join(', ');
        const extra = lowStock.length > 3 ? ` and ${lowStock.length - 3} more` : '';
        els.alertText.textContent = `${lowStock.length} item(s) are Low Stock or Out of Stock: ${names}${extra}.`;
        els.alertBox.classList.remove('d-none');
    }

    /** Re-pulls the filtered list from dataManager and repaints table + alert + charts. */
    function refreshView() {
        const filtered = dataManager.applyFilters();
        renderTable(filtered, currentQuery);
        updateLowStockAlert();
        updateStatistics();
        if (typeof dashboardCharts !== 'undefined') {
            dashboardCharts.renderAll(filtered);
        }
    }

    // ---- Notifications ------------------------------------------------

    function showToast(title, message, variant = 'primary') {
        if (!els.toastContainer || typeof bootstrap === 'undefined') return;

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-bg-${variant} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body"><strong>${escapeHtml(title)}:</strong> ${escapeHtml(message)}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        els.toastContainer.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    // ---- Part 5 Step 5: simulated real-time updates ---------------------

    function startRealtimeSimulation() {
        setInterval(() => {
            const before = dataManager.getProducts();
            const updated = dataManager.simulateUpdate();
            if (!updated) return;

            const previous = before.find(item => item.sku === updated.sku);
            refreshView();
            addActivity(
                `${updated.name}: stock changed from ${previous ? previous.quantity : '?'} to ${updated.quantity}.`,
                updated.stockStatus === 'Out of Stock' ? 'danger' : updated.stockStatus === 'Low Stock' ? 'warning' : 'info'
            );

            const row = els.tableBody.querySelector(`tr[data-sku="${CSS.escape(updated.sku)}"]`);
            if (row) {
                row.classList.add('row-flash');
                setTimeout(() => row.classList.remove('row-flash'), 1200);
            }

            showToast(
                'Live update',
                `${updated.name} stock changed to ${updated.quantity} (${updated.stockStatus}).`,
                'info'
            );
        }, 8000); // every 8 seconds
    }
});
