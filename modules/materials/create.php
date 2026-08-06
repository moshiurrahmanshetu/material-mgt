<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('materials.create');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$material_name = trim($_POST['material_name'] ?? '');
$category_id = $_POST['category_id'] ?? 0;
$unit = $_POST['unit'] ?? '';
$minimum_stock = $_POST['minimum_stock'] ?? 0;
$current_stock = $_POST['current_stock'] ?? 0;
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'Active';

// Server-side validation
if (empty($material_name)) {
    echo json_encode(['success' => false, 'message' => 'Material name is required']);
    exit;
}

if (empty($category_id)) {
    echo json_encode(['success' => false, 'message' => 'Category is required']);
    exit;
}

if (empty($unit)) {
    echo json_encode(['success' => false, 'message' => 'Unit is required']);
    exit;
}

if (!is_numeric($minimum_stock) || $minimum_stock < 0) {
    echo json_encode(['success' => false, 'message' => 'Minimum stock must be a non-negative number']);
    exit;
}

if (!is_numeric($current_stock) || $current_stock < 0) {
    echo json_encode(['success' => false, 'message' => 'Current stock must be a non-negative number']);
    exit;
}

// Check if category exists and is active
$stmt = $pdo->prepare("SELECT id, status FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    echo json_encode(['success' => false, 'message' => 'Selected category does not exist']);
    exit;
}

if ($category['status'] !== 'Active') {
    echo json_encode(['success' => false, 'message' => 'Selected category is not active']);
    exit;
}

// Generate material code
$prefix = 'MAT-';
$stmt = $pdo->prepare("SELECT material_code FROM materials WHERE material_code LIKE ? ORDER BY material_code DESC LIMIT 1");
$stmt->execute([$prefix . '%']);
$lastCode = $stmt->fetch();

if ($lastCode) {
    $lastNumber = (int)substr($lastCode['material_code'], 4);
    $newNumber = $lastNumber + 1;
} else {
    $newNumber = 1;
}

$material_code = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

// Insert material
$stmt = $pdo->prepare("INSERT INTO materials (material_code, material_name, category_id, unit, minimum_stock, current_stock, description, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$material_code, $material_name, $category_id, $unit, $minimum_stock, $current_stock, $description, $status, $_SESSION['user_id']]);

// Log activity
logActivity($pdo, $_SESSION['user_id'], 'Create', 'Materials', "Created material: $material_name");

echo json_encode(['success' => true, 'message' => 'Material created successfully']);
