<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('issue.create');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$request_id = $_POST['request_id'] ?? 0;
$material_id = $_POST['material_id'] ?? 0;
$employee_id = $_POST['employee_id'] ?? 0;
$issue_quantity = $_POST['issue_quantity'] ?? 0;
$issue_date = $_POST['issue_date'] ?? '';
$remarks = trim($_POST['remarks'] ?? '');
$requested_quantity = $_POST['requested_quantity'] ?? 0;

// Server-side validation
if (empty($request_id)) {
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit;
}

if (!is_numeric($issue_quantity) || $issue_quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Issue quantity must be greater than 0']);
    exit;
}

if (empty($issue_date)) {
    echo json_encode(['success' => false, 'message' => 'Issue date is required']);
    exit;
}

// Get request details
$stmt = $pdo->prepare("SELECT * FROM material_requests WHERE id = ?");
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if (!$request) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit;
}

if ($request['status'] !== 'Approved') {
    echo json_encode(['success' => false, 'message' => 'Request must be approved before issuing']);
    exit;
}

// Get current stock
$stmt = $pdo->prepare("SELECT current_stock FROM materials WHERE id = ?");
$stmt->execute([$material_id]);
$current_stock = $stmt->fetchColumn();

// Validate issue quantity against current stock
if ($issue_quantity > $current_stock) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock. Available: ' . number_format($current_stock, 2)]);
    exit;
}

// Validate issue quantity against requested quantity
if ($issue_quantity > $requested_quantity) {
    echo json_encode(['success' => false, 'message' => 'Issue quantity cannot exceed requested quantity']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Generate issue number
    $prefix = 'ISS-';
    $stmt = $pdo->prepare("SELECT issue_no FROM material_issues WHERE issue_no LIKE ? ORDER BY issue_no DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $lastCode = $stmt->fetch();
    
    if ($lastCode) {
        $lastNumber = (int)substr($lastCode['issue_no'], 4);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    $issue_no = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    
    // Insert material issue
    $stmt = $pdo->prepare("INSERT INTO material_issues (issue_no, request_id, employee_id, material_id, issue_quantity, issue_date, issued_by, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$issue_no, $request_id, $employee_id, $material_id, $issue_quantity, $issue_date, $_SESSION['user_id'], $remarks]);
    
    // Calculate new stock
    $new_stock = $current_stock - $issue_quantity;
    
    // Update material stock
    $stmt = $pdo->prepare("UPDATE materials SET current_stock = ? WHERE id = ?");
    $stmt->execute([$new_stock, $material_id]);
    
    // Insert stock movement
    $stmt = $pdo->prepare("INSERT INTO stock_movements (material_id, movement_type, reference_no, quantity_change, previous_stock, new_stock, remarks, created_by) VALUES (?, 'Issue', ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$material_id, $issue_no, -$issue_quantity, $current_stock, $new_stock, "Issue: $issue_no", $_SESSION['user_id']]);
    
    // Update request status to Issued
    $stmt = $pdo->prepare("UPDATE material_requests SET status = 'Issued' WHERE id = ?");
    $stmt->execute([$request_id]);
    
    // Commit transaction
    $pdo->commit();
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Create', 'Issue', "Issued material: $issue_no");
    
    echo json_encode(['success' => true, 'message' => 'Material issued successfully']);
    
} catch (PDOException $e) {
    // Roll back transaction on error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error issuing material: ' . $e->getMessage()]);
}
