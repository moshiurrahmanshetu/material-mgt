<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('categories.create');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$category_name = trim($_POST['category_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'Active';

if (empty($category_name)) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

// Check if category name already exists
$stmt = $pdo->prepare("SELECT id FROM categories WHERE category_name = ?");
$stmt->execute([$category_name]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Category name already exists']);
    exit;
}

// Generate category code
$prefix = 'CAT-';
$stmt = $pdo->prepare("SELECT category_code FROM categories WHERE category_code LIKE ? ORDER BY category_code DESC LIMIT 1");
$stmt->execute([$prefix . '%']);
$lastCode = $stmt->fetch();

if ($lastCode) {
    $lastNumber = (int)substr($lastCode['category_code'], 4);
    $newNumber = $lastNumber + 1;
} else {
    $newNumber = 1;
}

$category_code = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

// Insert category
$stmt = $pdo->prepare("INSERT INTO categories (category_code, category_name, description, status, created_by) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$category_code, $category_name, $description, $status, $_SESSION['user_id']]);

// Log activity
logActivity($pdo, $_SESSION['user_id'], 'Create', 'Categories', "Created category: $category_name");

echo json_encode(['success' => true, 'message' => 'Category created successfully']);
