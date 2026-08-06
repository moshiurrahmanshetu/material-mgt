<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('users.delete');

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

// Self-protection: cannot delete own account
if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
    exit;
}

try {
    // Check for related records
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM material_requests WHERE employee_id = ?");
    $stmt->execute([$user_id]);
    $requests_count = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM material_issues WHERE employee_id = ?");
    $stmt->execute([$user_id]);
    $issues_count = $stmt->fetchColumn();
    
    if ($requests_count > 0 || $issues_count > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete this user: They have related records (requests or issues). Consider deactivating instead.']);
        exit;
    }
    
    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Delete', 'Users', "Deleted user ID: $user_id");
    
    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete this user: They are referenced by other records. Consider deactivating instead.']);
}
