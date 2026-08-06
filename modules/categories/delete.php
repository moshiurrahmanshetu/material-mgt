<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('categories.delete');

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

if (!$category_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

try {
    // Check if category has materials
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM materials WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete category: It has materials assigned to it. Please delete or reassign the materials first.']);
        exit;
    }
    
    // Delete category
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Delete', 'Categories', "Deleted category ID: $category_id");
    
    echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete category: It is referenced by other records.']);
}
