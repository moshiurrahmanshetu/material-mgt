<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('categories.edit');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$category_id = $_POST['category_id'] ?? 0;
$category_name = trim($_POST['category_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'Active';

if (empty($category_name)) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

if (!$category_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

// Check if category name already exists (excluding current category)
$stmt = $pdo->prepare("SELECT id FROM categories WHERE category_name = ? AND id != ?");
$stmt->execute([$category_name, $category_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Category name already exists']);
    exit;
}

// Update category
$stmt = $pdo->prepare("UPDATE categories SET category_name = ?, description = ?, status = ? WHERE id = ?");
$stmt->execute([$category_name, $description, $status, $category_id]);

// Log activity
logActivity($pdo, $_SESSION['user_id'], 'Update', 'Categories', "Updated category ID: $category_id");

echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
