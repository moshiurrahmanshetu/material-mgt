<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('roles.edit');

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
$role_name = trim($_POST['role_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'Active';
$permissions = $_POST['permissions'] ?? [];

// Server-side validation
if (empty($role_id) || empty($role_name)) {
    echo json_encode(['success' => false, 'message' => 'Role ID and name are required']);
    exit;
}

// Get current role details
$stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$role_id]);
$role = $stmt->fetch();

if (!$role) {
    echo json_encode(['success' => false, 'message' => 'Role not found']);
    exit;
}

// Self-protection: cannot remove critical permissions from Super Admin role
if ($role['role_name'] === 'Super Admin') {
    if (!empty($permissions)) {
        $placeholders = str_repeat('?,', count($permissions) - 1) . '?';
        $stmt = $pdo->prepare("SELECT permission_key FROM permissions WHERE id IN ($placeholders)");
        $stmt->execute($permissions);
        $selected_permission_keys = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permission_key');
    } else {
        $selected_permission_keys = [];
    }
    
    $critical_permissions = ['users.view', 'roles.view', 'roles.edit'];
    foreach ($critical_permissions as $critical) {
        if (!in_array($critical, $selected_permission_keys)) {
            echo json_encode(['success' => false, 'message' => 'You cannot remove critical admin permissions (users.view, roles.view, roles.edit) from the Super Admin role']);
            exit;
        }
    }
}

// Check role name uniqueness (excluding current role)
$stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ? AND id != ?");
$stmt->execute([$role_name, $role_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Role name already exists']);
    exit;
}

// Validate all permission IDs exist
if (!empty($permissions)) {
    $placeholders = str_repeat('?,', count($permissions) - 1) . '?';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM permissions WHERE id IN ($placeholders)");
    $stmt->execute($permissions);
    if ($stmt->fetchColumn() != count($permissions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid permission IDs']);
        exit;
    }
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Update role
    $stmt = $pdo->prepare("UPDATE roles SET role_name = ?, description = ?, status = ? WHERE id = ?");
    $stmt->execute([$role_name, $description, $status, $role_id]);
    
    // Delete existing role permissions
    $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$role_id]);
    
    // Insert new role permissions
    if (!empty($permissions)) {
        $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($permissions as $permission_id) {
            $stmt->execute([$role_id, $permission_id]);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Update', 'Roles', "Updated role: $role_name");
    
    echo json_encode(['success' => true, 'message' => 'Role updated successfully']);
} catch (PDOException $e) {
    // Roll back transaction on error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error updating role: ' . $e->getMessage()]);
}
