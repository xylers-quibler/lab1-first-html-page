/**
 * dataManager.js
 * ------------------------------------------------------------------
 * Central data management module for the Restaurant Manager
 * Dashboard (Laboratory Exercise 4).
 *
 * "product" = a menu item. quantity = stock count on hand.
 * unitPrice = price per item. totalValue = quantity * unitPrice.
 * stockStatus = "In Stock" / "Low Stock" / "Out of Stock", derived
 * from quantity vs reorderLevel.
 *
 * Exposes a single global `dataManager` object (module pattern / IIFE)
 * so state lives in one place instead of scattered globals.
 * ------------------------------------------------------------------
 */

const dataManager = (function () {
    'use strict';

    // ---------------------------------------------------------------
    // Private state
    // ---------------------------------------------------------------
    let menuItems = [];

    let activeFilters = {
        category: 'all',
        stockStatus: 'all',
        priceMin: null,
        priceMax: null,
        searchQuery: ''
    };

    const REORDER_LEVEL = 10; // quantity at/below this = Low Stock
    const OUT_OF_STOCK = 0;

    // ---------------------------------------------------------------
    // Sample "backend" data.
    // In Part 7 this can be swapped for a fetch() call to api.php
    // without changing any function signature below.
    // ---------------------------------------------------------------
    const SAMPLE_MENU_ITEMS = [
        { id: 1, sku: 'APP-001', name: 'Spring Rolls', category: 'Appetizer', quantity: 48, unitPrice: 120 },
        { id: 2, sku: 'APP-002', name: 'Calamari', category: 'Appetizer', quantity: 0, unitPrice: 180 },
        { id: 3, sku: 'APP-003', name: 'Garlic Bread', category: 'Appetizer', quantity: 41, unitPrice: 90 },
        { id: 4, sku: 'MAIN-001', name: 'Grilled Salmon', category: 'Main Course', quantity: 15, unitPrice: 450 },
        { id: 5, sku: 'MAIN-002', name: 'Beef Steak', category: 'Main Course', quantity: 0, unitPrice: 520 },
        { id: 6, sku: 'MAIN-003', name: 'Chicken Alfredo', category: 'Main Course', quantity: 12, unitPrice: 280 },
        { id: 7, sku: 'MAIN-004', name: 'Margherita Pizza', category: 'Main Course', quantity: 40, unitPrice: 320 },
        { id: 8, sku: 'DES-001', name: 'Chocolate Lava Cake', category: 'Dessert', quantity: 21, unitPrice: 150 },
        { id: 9, sku: 'DES-002', name: 'Cheesecake', category: 'Dessert', quantity: 17, unitPrice: 160 },
        { id: 10, sku: 'DES-003', name: 'Ice Cream Sundae', category: 'Dessert', quantity: 14, unitPrice: 110 },
        { id: 11, sku: 'BEV-001', name: 'Iced Tea', category: 'Beverage', quantity: 61, unitPrice: 60 },
        { id: 12, sku: 'BEV-002', name: 'Fresh Lemonade', category: 'Beverage', quantity: 5, unitPrice: 70 },
        { id: 13, sku: 'BEV-003', name: 'Craft Soda', category: 'Beverage', quantity: 0, unitPrice: 85 },
        { id: 14, sku: 'BEV-004', name: 'House Wine', category: 'Beverage', quantity: 18, unitPrice: 220 },
    ];

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    function deriveStatus(quantity) {
        if (quantity <= OUT_OF_STOCK) return 'Out of Stock';
        if (quantity <= REORDER_LEVEL) return 'Low Stock';
        return 'In Stock';
    }

    // Attaches computed fields (stockStatus, reorderLevel, totalValue)
    // to a raw menu item record.
    function withComputed(item) {
        return {
            ...item,
            stockStatus: deriveStatus(item.quantity),
            reorderLevel: REORDER_LEVEL,
            totalValue: +(item.quantity * item.unitPrice).toFixed(2)
        };
    }

    // ---------------------------------------------------------------
    // Step 1: Initialization & basic getters
    // ---------------------------------------------------------------

    // Part 7: backend endpoint. If api.php isn't deployed/running (e.g.
    // the page was opened directly via file://, or XAMPP's Apache isn't
    // started), initializeData() falls back to the bundled sample data
    // below so the dashboard still works standalone.
    const API_ENDPOINT = 'api.php?action=list';
    const API_TIMEOUT_MS = 2000;

    /** Attempts to fetch the menu items from api.php, with a short timeout. */
    function fetchFromApi() {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), API_TIMEOUT_MS);

        return fetch(API_ENDPOINT, { signal: controller.signal })
            .then(res => {
                clearTimeout(timeoutId);
                if (!res.ok) throw new Error(`API responded with status ${res.status}`);
                return res.json();
            })
            .then(data => {
                if (!Array.isArray(data)) throw new Error('Unexpected API response shape');
                return data;
            });
    }

    /**
     * Sets up the initial dataset. Tries api.php first (Part 7); if that
     * fails for any reason, falls back to the bundled sample data after
     * a short simulated delay, so the dashboard is never blocked on a
     * live backend. Either way returns a Promise resolving to the menu items.
     */
    function initializeData() {
        return fetchFromApi()
            .then(apiItems => {
                menuItems = apiItems.map(withComputed);
                return getProducts();
            })
            .catch(() => {
                console.warn('dataManager: api.php unreachable — using bundled sample data.');
                return new Promise((resolve) => {
                    setTimeout(() => {
                        menuItems = SAMPLE_MENU_ITEMS.map(withComputed);
                        resolve(getProducts());
                    }, 300);
                });
            });
    }

    /** Returns a shallow copy of the full current menu item list. */
    function getProducts() {
        return [...menuItems];
    }

    /** Returns a single menu item by numeric id or SKU string. */
    function getProductById(id) {
        return menuItems.find(i => i.id === id || i.sku === id) || null;
    }

    /** Returns menu items belonging to a given category. */
    function getProductsByCategory(category) {
        return menuItems.filter(i => i.category === category);
    }

    /** Returns menu items that are Low Stock or Out of Stock. */
    function getLowStockProducts() {
        return menuItems.filter(i => i.stockStatus !== 'In Stock');
    }

    /**
     * Returns aggregate statistics. Pass a pre-filtered list (e.g. from
     * applyFilters()) to get stats scoped to the current view; omit it
     * to summarize the entire menu.
     */
    function getStockStatistics(list) {
        const source = list || menuItems;
        const total = source.length;
        const totalValue = source.reduce((sum, i) => sum + i.totalValue, 0);
        const inStock = source.filter(i => i.stockStatus === 'In Stock').length;
        const lowStock = source.filter(i => i.stockStatus === 'Low Stock').length;
        const outOfStock = source.filter(i => i.stockStatus === 'Out of Stock').length;
        const avgPrice = total
            ? +(source.reduce((sum, i) => sum + i.unitPrice, 0) / total).toFixed(2)
            : 0;

        return {
            totalProducts: total,
            totalValue: +totalValue.toFixed(2),
            inStock,
            lowStock,
            outOfStock,
            averagePrice: avgPrice
        };
    }

    /**
     * Returns per-category totals (quantity, value, item count).
     * Pass a pre-filtered list to scope the summary to the current view.
     */
    function getCategorySummary(list) {
        const source = list || menuItems;
        const map = {};
        source.forEach(i => {
            if (!map[i.category]) {
                map[i.category] = {
                    category: i.category,
                    totalQuantity: 0,
                    totalValue: 0,
                    count: 0
                };
            }
            map[i.category].totalQuantity += i.quantity;
            map[i.category].totalValue += i.totalValue;
            map[i.category].count += 1;
        });
        return Object.values(map).map(c => ({ ...c, totalValue: +c.totalValue.toFixed(2) }));
    }

    /** Returns the distinct list of categories present in the data. */
    function getCategories() {
        return [...new Set(menuItems.map(i => i.category))];
    }

    // ---------------------------------------------------------------
    // Step 2: Filtering & search
    // ---------------------------------------------------------------

    function filterByCategory(category) {
        activeFilters.category = category;
        return applyFilters();
    }

    function filterByStockStatus(status) {
        activeFilters.stockStatus = status;
        return applyFilters();
    }

    function filterByPriceRange(min, max) {
        activeFilters.priceMin = (min === '' || min == null) ? null : Number(min);
        activeFilters.priceMax = (max === '' || max == null) ? null : Number(max);
        return applyFilters();
    }

    /** Applies every active filter + the search query together. */
    function applyFilters() {
        return menuItems.filter(i => {
            const matchCategory = activeFilters.category === 'all' || i.category === activeFilters.category;
            const matchStatus = activeFilters.stockStatus === 'all' || i.stockStatus === activeFilters.stockStatus;
            const matchMin = activeFilters.priceMin == null || i.unitPrice >= activeFilters.priceMin;
            const matchMax = activeFilters.priceMax == null || i.unitPrice <= activeFilters.priceMax;
            const q = activeFilters.searchQuery.trim().toLowerCase();
            const matchSearch = !q ||
                i.name.toLowerCase().includes(q) ||
                i.sku.toLowerCase().includes(q);
            return matchCategory && matchStatus && matchMin && matchMax && matchSearch;
        });
    }

    function resetFilters() {
        activeFilters = { category: 'all', stockStatus: 'all', priceMin: null, priceMax: null, searchQuery: '' };
        return getProducts();
    }

    function searchProducts(query) {
        activeFilters.searchQuery = query || '';
        return applyFilters();
    }

    /** Convenience alias matching the spec's naming for the search handler. */
    function updateSearchResults(query) {
        return searchProducts(query);
    }

    function getActiveFilters() {
        return { ...activeFilters };
    }

    // ---------------------------------------------------------------
    // Step 3: CSV export
    // ---------------------------------------------------------------

    /** Converts a list of menu item records into a CSV string. */
    function exportToCSV(data) {
        const rows = (data && data.length) ? data : menuItems;
        const headers = ['SKU', 'Name', 'Category', 'Quantity', 'Unit Price', 'Total Value', 'Status'];

        const escape = (val) => `"${String(val).replace(/"/g, '""')}"`;

        const lines = rows.map(i => [
            i.sku,
            escape(i.name),
            escape(i.category),
            i.quantity,
            i.unitPrice.toFixed(2),
            i.totalValue.toFixed(2),
            i.stockStatus
        ].join(','));

        return [headers.join(','), ...lines].join('\r\n');
    }

    /** Triggers a browser download of the given CSV content. */
    function downloadCSV(csvContent, filename) {
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', filename || 'menu_inventory.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    // ---------------------------------------------------------------
    // Real-time simulation hook (wired up by app.js in Part 5)
    // ---------------------------------------------------------------

    /**
     * Sends the updated quantity to api.php so the change is saved to
     * data/menu_items.json, not just kept in memory. Fire-and-forget:
     * if api.php isn't running (no XAMPP/Apache), this silently fails
     * and the dashboard keeps working from the in-memory copy only.
     */
    function pushUpdateToApi(item) {
        fetch(`api.php?action=update&id=${encodeURIComponent(item.sku)}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ quantity: item.quantity })
        }).catch(() => {
            console.warn('dataManager: could not persist update to api.php (is Apache running?).');
        });
    }

    /** Updates stock through the PHP API and syncs the local state with the server response. */
    function updateProductStock(sku, quantity) {
        const numericQuantity = Number(quantity);
        if (!Number.isInteger(numericQuantity) || numericQuantity < 0) {
            return Promise.reject(new Error('Quantity must be a whole number greater than or equal to 0.'));
        }
        const existing = getProductById(sku);
        if (!existing) return Promise.reject(new Error('Menu item not found.'));

        return fetch(`api.php?action=update&id=${encodeURIComponent(existing.sku)}`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ quantity: numericQuantity })
        }).then(async res => {
            let data = null;
            try { data = await res.json(); } catch (_) {}
            if (!res.ok) throw new Error(data && data.error ? data.error : `API responded with status ${res.status}`);
            if (!data || data.sku !== existing.sku) throw new Error('Unexpected API response.');
            const updated = withComputed(data);
            const index = menuItems.findIndex(i => i.sku === existing.sku);
            if (index !== -1) menuItems[index] = updated;
            return updated;
        });
    }

    /** Nudges one random menu item's quantity to mimic a live data feed, and persists it. */
    function simulateUpdate() {
        if (!menuItems.length) return null;
        const idx = Math.floor(Math.random() * menuItems.length);
        const delta = Math.floor(Math.random() * 11) - 5; // -5 to +5
        let newQty = menuItems[idx].quantity + delta;
        newQty = Math.max(0, newQty);
        menuItems[idx] = withComputed({ ...menuItems[idx], quantity: newQty });
        pushUpdateToApi(menuItems[idx]);
        return menuItems[idx];
    }

    // ---------------------------------------------------------------
    // Public API
    // ---------------------------------------------------------------
    return {
        initializeData,
        getProducts,
        getProductById,
        getProductsByCategory,
        getLowStockProducts,
        getStockStatistics,
        getCategorySummary,
        getCategories,
        filterByCategory,
        filterByStockStatus,
        filterByPriceRange,
        applyFilters,
        resetFilters,
        searchProducts,
        updateSearchResults,
        getActiveFilters,
        exportToCSV,
        downloadCSV,
        updateProductStock,
        simulateUpdate
    };
})();
