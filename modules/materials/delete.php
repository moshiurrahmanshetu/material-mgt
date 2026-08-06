<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('materials.delete');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$material_id = $_POST['material_id'] ?? 0;

if (!$material_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid material ID']);
    exit;
}

try {
    // Delete material (FK constraints will prevent deletion if referenced by future tables)
    $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
    $stmt->execute([$material_id]);
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Delete', 'Materials', "Deleted material ID: $material_id");
    
    echo json_encode(['success' => true, 'message' => 'Material deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete material: It is referenced by other records (e.g., purchase orders, issues).']);
}
