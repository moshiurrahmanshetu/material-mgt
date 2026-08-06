<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('suppliers.edit');

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

if (!$supplier_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid supplier ID']);
    exit;
}

// Email validation
if (!empty($email) && !isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Phone validation
if (!empty($phone) && !preg_match('/^[\d\s\+\-\(\)]+$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone format']);
    exit;
}

// Update supplier
$stmt = $pdo->prepare("UPDATE suppliers SET supplier_name = ?, company = ?, contact_person = ?, phone = ?, email = ?, address = ?, status = ? WHERE id = ?");
$stmt->execute([$supplier_name, $company, $contact_person, $phone, $email, $address, $status, $supplier_id]);

// Log activity
logActivity($pdo, $_SESSION['user_id'], 'Update', 'Suppliers', "Updated supplier ID: $supplier_id");

echo json_encode(['success' => true, 'message' => 'Supplier updated successfully']);
