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
$password = $_POST['new_password'] ?? '';

// Server-side validation
if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Update password
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$hashed_password, $user_id]);

// Log activity
logActivity($pdo, $_SESSION['user_id'], 'Update', 'Users', "Password reset by admin for user ID: $user_id");

echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
