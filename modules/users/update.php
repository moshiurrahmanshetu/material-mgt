<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('users.edit');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$user_id = $_POST['user_id'] ?? 0;
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$role_id = $_POST['role_id'] ?? 0;
$status = $_POST['status'] ?? 'Active';

// Server-side validation
if (empty($full_name) || empty($email) || empty($role_id)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit;
}

if (!isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Self-protection: cannot deactivate or change own role
if ($user_id == $_SESSION['user_id']) {
    $stmt = $pdo->prepare("SELECT role_id, status FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_user = $stmt->fetch();
    
    if ($status !== $current_user['status']) {
        echo json_encode(['success' => false, 'message' => 'You cannot change your own status']);
        exit;
    }
    
    if ($role_id != $current_user['role_id']) {
        echo json_encode(['success' => false, 'message' => 'You cannot change your own role']);
        exit;
    }
}

// Check email uniqueness (excluding current user)
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->execute([$email, $user_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Email already exists']);
    exit;
}

// Update user
$stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role_id = ?, status = ? WHERE id = ?");
$stmt->execute([$full_name, $email, $phone, $role_id, $status, $user_id]);

// Log activity
logActivity($pdo, $_SESSION['user_id'], 'Update', 'Users', "Updated user ID: $user_id");

echo json_encode(['success' => true, 'message' => 'User updated successfully']);
