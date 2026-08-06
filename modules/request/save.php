<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('request.create');

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
$requested_quantity = $_POST['requested_quantity'] ?? 0;
$department = trim($_POST['department'] ?? '');
$purpose = trim($_POST['purpose'] ?? '');
$request_date = $_POST['request_date'] ?? '';

// Server-side validation
if (empty($material_id)) {
    echo json_encode(['success' => false, 'message' => 'Material is required']);
    exit;
}

if (!is_numeric($requested_quantity) || $requested_quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Requested quantity must be greater than 0']);
    exit;
}

if (empty($request_date)) {
    echo json_encode(['success' => false, 'message' => 'Request date is required']);
    exit;
}

// Check if material exists and is active
$stmt = $pdo->prepare("SELECT id, status FROM materials WHERE id = ?");
$stmt->execute([$material_id]);
$material = $stmt->fetch();

if (!$material) {
    echo json_encode(['success' => false, 'message' => 'Selected material does not exist']);
    exit;
}

if ($material['status'] !== 'Active') {
    echo json_encode(['success' => false, 'message' => 'Selected material is not active']);
    exit;
}

// Generate request number
$prefix = 'REQ-';
$stmt = $pdo->prepare("SELECT request_no FROM material_requests WHERE request_no LIKE ? ORDER BY request_no DESC LIMIT 1");
$stmt->execute([$prefix . '%']);
$lastCode = $stmt->fetch();

if ($lastCode) {
    $lastNumber = (int)substr($lastCode['request_no'], 4);
    $newNumber = $lastNumber + 1;
} else {
    $newNumber = 1;
}

$request_no = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

// Insert request (employee_id from session, not from form)
$stmt = $pdo->prepare("INSERT INTO material_requests (request_no, employee_id, department, material_id, requested_quantity, purpose, request_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$request_no, $_SESSION['user_id'], $department, $material_id, $requested_quantity, $purpose, $request_date]);

// Log activity
logActivity($pdo, $_SESSION['user_id'], 'Create', 'Request', "Created request: $request_no");

echo json_encode(['success' => true, 'message' => 'Request created successfully']);
