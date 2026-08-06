<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('roles.delete');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$role_id = $_POST['role_id'] ?? 0;

// Server-side validation
if (empty($role_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role ID']);
    exit;
}

// Get role details
$stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$role_id]);
$role = $stmt->fetch();

if (!$role) {
    echo json_encode(['success' => false, 'message' => 'Role not found']);
    exit;
}

// Self-protection: cannot delete Super Admin role
if ($role['role_name'] === 'Super Admin') {
    echo json_encode(['success' => false, 'message' => 'Cannot delete the Super Admin role']);
    exit;
}

// Check if any users have this role
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role_id = ?");
$stmt->execute([$role_id]);
$user_count = $stmt->fetchColumn();

if ($user_count > 0) {
    echo json_encode(['success' => false, 'message' => "Cannot delete this role: $user_count user(s) are still assigned to it"]);
    exit;
}

try {
    // Delete role permissions first
    $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$role_id]);
    
    // Delete role
    $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
    $stmt->execute([$role_id]);
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Delete', 'Roles', "Deleted role: {$role['role_name']}");
    
    echo json_encode(['success' => true, 'message' => 'Role deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error deleting role: ' . $e->getMessage()]);
}
