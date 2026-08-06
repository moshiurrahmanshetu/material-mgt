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

// Server-side validation
if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

// Self-protection: cannot toggle own status
if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot change your own status']);
    exit;
}

// Get current status
$stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Toggle status
$new_status = $user['status'] === 'Active' ? 'Inactive' : 'Active';
$stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->execute([$new_status, $user_id]);

// Log activity
logActivity($pdo, $_SESSION['user_id'], 'Update', 'Users', "Changed status to $new_status for user ID: $user_id");

echo json_encode(['success' => true, 'message' => 'User status updated successfully']);
