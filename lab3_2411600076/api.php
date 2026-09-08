<?php
/**
 * api.php
 * ------------------------------------------------------------------
 * Simple PHP backend for the Restaurant Manager Dashboard
 * (Laboratory Exercise 4, Part 7). Serves the menu inventory as
 * JSON and supports adding items and updating an item's stock
 * quantity.
 *
 * Run under XAMPP (Apache) and called from js/dataManager.js.
 *
 * Endpoints (relative to this file, e.g. http://localhost/lab4_YourStudentID/api.php):
 *   GET  ?action=list              -> full menu inventory as JSON
 *   GET  ?action=get&id=APP-001    -> single menu item
 *   POST ?action=add               -> add a menu item (JSON body)
 *   POST ?action=update&id=APP-001 -> patch one item's fields (JSON body)
 *   POST ?action=delete&id=APP-001 -> remove a menu item
 *
 * Data persists to data/menu_items.json so changes survive requests.
 * A real deployment would use MySQL (XAMPP ships with it), but a flat
 * JSON file keeps this lab focused on the JS <-> API data flow rather
 * than SQL/PDO setup.
 * ------------------------------------------------------------------
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight support for cross-origin fetch() calls during development.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('DATA_FILE', __DIR__ . '/data/menu_items.json');
define('REORDER_LEVEL', 10);
define('OUT_OF_STOCK', 0);

// ---------------------------------------------------------------
// Storage helpers
// ---------------------------------------------------------------

function loadMenuItems(): array {
    if (!file_exists(DATA_FILE)) {
        return [];
    }
    $json = file_get_contents(DATA_FILE);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function saveMenuItems(array $items): void {
    $dir = dirname(DATA_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(DATA_FILE, json_encode(array_values($items), JSON_PRETTY_PRINT));
}

// ---------------------------------------------------------------
// Domain helpers (mirrors js/dataManager.js so the API and the
// client-side fallback data behave identically)
// ---------------------------------------------------------------

function deriveStatus(int $quantity): string {
    if ($quantity <= OUT_OF_STOCK) return 'Out of Stock';
    if ($quantity <= REORDER_LEVEL) return 'Low Stock';
    return 'In Stock';
}

function withComputed(array $i): array {
    $qty = (int) ($i['quantity'] ?? 0);
    $price = (float) ($i['unitPrice'] ?? 0);
    $i['stockStatus'] = deriveStatus($qty);
    $i['reorderLevel'] = REORDER_LEVEL;
    $i['totalValue'] = round($qty * $price, 2);
    return $i;
}

function respond($payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function readJsonBody(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ---------------------------------------------------------------
// Routing
// ---------------------------------------------------------------

$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];
$items = loadMenuItems();

if ($method === 'GET' && $action === 'list') {
    respond(array_map('withComputed', $items));
}

if ($method === 'GET' && $action === 'get') {
    $id = $_GET['id'] ?? '';
    $found = array_values(array_filter($items, fn($i) => $i['sku'] === $id));
    if (!$found) {
        respond(['error' => 'Menu item not found'], 404);
    }
    respond(withComputed($found[0]));
}

if ($method === 'POST' && $action === 'add') {
    $body = readJsonBody();
    $required = ['sku', 'name', 'category', 'quantity', 'unitPrice'];
    foreach ($required as $field) {
        if (!isset($body[$field])) {
            respond(['error' => "Missing field: $field"], 400);
        }
    }
    $duplicate = array_filter($items, fn($i) => $i['sku'] === $body['sku']);
    if ($duplicate) {
        respond(['error' => 'SKU already exists'], 409);
    }
    $body['id'] = count($items) ? max(array_column($items, 'id')) + 1 : 1;
    $items[] = $body;
    saveMenuItems($items);
    respond(withComputed($body), 201);
}

if ($method === 'POST' && $action === 'update') {
    $id = $_GET['id'] ?? '';
    $body = readJsonBody();
    $updated = null;

    foreach ($items as &$i) {
        if ($i['sku'] === $id) {
            foreach ($body as $key => $value) {
                if ($key !== 'id' && $key !== 'sku') {
                    $i[$key] = $value;
                }
            }
            $updated = $i;
            break;
        }
    }
    unset($i);

    if (!$updated) {
        respond(['error' => 'Menu item not found'], 404);
    }
    saveMenuItems($items);
    respond(withComputed($updated));
}

if ($method === 'POST' && $action === 'delete') {
    $id = $_GET['id'] ?? '';
    $before = count($items);
    $items = array_values(array_filter($items, fn($i) => $i['sku'] !== $id));
    if (count($items) === $before) {
        respond(['error' => 'Menu item not found'], 404);
    }
    saveMenuItems($items);
    respond(['success' => true]);
}

respond(['error' => 'Unknown action or method'], 400);
