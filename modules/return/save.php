<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('return.create');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$issue_id = $_POST['issue_id'] ?? 0;
$material_id = $_POST['material_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 0;
$return_date = $_POST['return_date'] ?? '';
$remarks = trim($_POST['remarks'] ?? '');

// Server-side validation
if (empty($issue_id)) {
    echo json_encode(['success' => false, 'message' => 'Issue is required']);
    exit;
}

if (!is_numeric($quantity) || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Return quantity must be greater than 0']);
    exit;
}

if (empty($return_date)) {
    echo json_encode(['success' => false, 'message' => 'Return date is required']);
    exit;
}

// Get issue details
$stmt = $pdo->prepare("SELECT * FROM material_issues WHERE id = ?");
$stmt->execute([$issue_id]);
$issue = $stmt->fetch();

if (!$issue) {
    echo json_encode(['success' => false, 'message' => 'Issue not found']);
    exit;
}

// Calculate returnable balance
$stmt = $pdo->prepare("SELECT issue_quantity - COALESCE(SUM(quantity), 0) as returnable_quantity 
                      FROM material_issues mi 
                      LEFT JOIN material_returns mr ON mi.id = mr.issue_id 
                      WHERE mi.id = ? 
                      GROUP BY mi.id");
$stmt->execute([$issue_id]);
$result = $stmt->fetch();

$returnable_quantity = $result['returnable_quantity'] ?? 0;

// Validate return quantity against returnable balance
if ($quantity > $returnable_quantity) {
    echo json_encode(['success' => false, 'message' => 'Return quantity cannot exceed returnable balance. Maximum returnable: ' . number_format($returnable_quantity, 2)]);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Generate return number
    $prefix = 'RET-';
    $stmt = $pdo->prepare("SELECT return_no FROM material_returns WHERE return_no LIKE ? ORDER BY return_no DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $lastCode = $stmt->fetch();
    
    if ($lastCode) {
        $lastNumber = (int)substr($lastCode['return_no'], 4);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    $return_no = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    
    // Insert material return
    $stmt = $pdo->prepare("INSERT INTO material_returns (return_no, issue_id, material_id, quantity, return_date, remarks, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$return_no, $issue_id, $material_id, $quantity, $return_date, $remarks, $_SESSION['user_id']]);
    
    // Get current stock
    $stmt = $pdo->prepare("SELECT current_stock FROM materials WHERE id = ?");
    $stmt->execute([$material_id]);
    $current_stock = $stmt->fetchColumn();
    
    // Calculate new stock
    $new_stock = $current_stock + $quantity;
    
    // Update material stock
    $stmt = $pdo->prepare("UPDATE materials SET current_stock = ? WHERE id = ?");
    $stmt->execute([$new_stock, $material_id]);
    
    // Insert stock movement
    $stmt = $pdo->prepare("INSERT INTO stock_movements (material_id, movement_type, reference_no, quantity_change, previous_stock, new_stock, remarks, created_by) VALUES (?, 'Return', ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$material_id, $return_no, $quantity, $current_stock, $new_stock, "Return: $return_no", $_SESSION['user_id']]);
    
    // Commit transaction
    $pdo->commit();
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Create', 'Return', "Recorded return: $return_no");
    
    echo json_encode(['success' => true, 'message' => 'Return recorded successfully']);
    
} catch (PDOException $e) {
    // Roll back transaction on error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error recording return: ' . $e->getMessage()]);
}
