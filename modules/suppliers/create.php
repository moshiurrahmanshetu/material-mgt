<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('suppliers.create');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$supplier_name = trim($_POST['supplier_name'] ?? '');
$company = trim($_POST['company'] ?? '');
$contact_person = trim($_POST['contact_person'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$status = $_POST['status'] ?? 'Active';

// Server-side validation
if (empty($supplier_name)) {
    echo json_encode(['success' => false, 'message' => 'Supplier name is required']);
    exit;
}

// Email validation
if (!empty($email) && !isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Phone validation (basic - allow digits, spaces, +, -, parentheses)
if (!empty($phone) && !preg_match('/^[\d\s\+\-\(\)]+$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone format']);
    exit;
}

// Generate supplier code
$prefix = 'SUP-';
$stmt = $pdo->prepare("SELECT supplier_code FROM suppliers WHERE supplier_code LIKE ? ORDER BY supplier_code DESC LIMIT 1");
$stmt->execute([$prefix . '%']);
$lastCode = $stmt->fetch();

if ($lastCode) {
    $lastNumber = (int)substr($lastCode['supplier_code'], 4);
    $newNumber = $lastNumber + 1;
} else {
    $newNumber = 1;
}

$supplier_code = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

// Insert supplier
$stmt = $pdo->prepare("INSERT INTO suppliers (supplier_code, supplier_name, company, contact_person, phone, email, address, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$supplier_code, $supplier_name, $company, $contact_person, $phone, $email, $address, $status, $_SESSION['user_id']]);

// Log activity
logActivity($pdo, $_SESSION['user_id'], 'Create', 'Suppliers', "Created supplier: $supplier_name");

echo json_encode(['success' => true, 'message' => 'Supplier created successfully']);
