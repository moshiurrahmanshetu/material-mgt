<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('roles.create');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$role_name = trim($_POST['role_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'Active';
$permissions = $_POST['permissions'] ?? [];

// Server-side validation
if (empty($role_name)) {
    echo json_encode(['success' => false, 'message' => 'Role name is required']);
    exit;
}

// Check role name uniqueness
$stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
$stmt->execute([$role_name]);
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
    
    // Insert role
    $stmt = $pdo->prepare("INSERT INTO roles (role_name, description, status) VALUES (?, ?, ?)");
    $stmt->execute([$role_name, $description, $status]);
    $role_id = $pdo->lastInsertId();
    
    // Insert role permissions
    if (!empty($permissions)) {
        $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($permissions as $permission_id) {
            $stmt->execute([$role_id, $permission_id]);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Log activity
    logActivity($pdo, $_SESSION['user_id'], 'Create', 'Roles', "Created role: $role_name");
    
    echo json_encode(['success' => true, 'message' => 'Role created successfully']);
} catch (PDOException $e) {
    // Roll back transaction on error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error creating role: ' . $e->getMessage()]);
}
