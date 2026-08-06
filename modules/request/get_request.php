<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('request.view');

header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;

if (!$id) {
    echo json_encode(null);
    exit;
}

$can_approve = hasPermission('issue.approve');

// If user cannot approve, only allow viewing their own requests
if (!$can_approve) {
    $stmt = $pdo->prepare("SELECT mr.*, m.material_name FROM material_requests mr INNER JOIN materials m ON mr.material_id = m.id WHERE mr.id = ? AND mr.employee_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT mr.*, m.material_name FROM material_requests mr INNER JOIN materials m ON mr.material_id = m.id WHERE mr.id = ?");
    $stmt->execute([$id]);
}

$request = $stmt->fetch();

echo json_encode($request);
