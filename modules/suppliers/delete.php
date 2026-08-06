<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('suppliers.delete');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$supplier_id = $_POST['supplier_id'] ?? 0;

if (!$supplier_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid supplier ID']);
    exit;
}

try {
    // Check if supplier has purchases
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM purchase_master WHERE supplier_id = ?");
    $stmt->execute([$supplier_id]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete supplier: It has purchase records associated with it.']);
        exit;
    }
    
    // Delete supplier
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->execute([$supplier_id]);
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Delete', 'Suppliers', "Deleted supplier ID: $supplier_id");
    
    echo json_encode(['success' => true, 'message' => 'Supplier deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete supplier: It is referenced by other records.']);
}
