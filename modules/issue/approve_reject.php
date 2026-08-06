<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('issue.approve');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$request_id = $_POST['request_id'] ?? 0;
$action = $_POST['action'] ?? '';

if (!$request_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
    exit;
}

// Get request details
$stmt = $pdo->prepare("SELECT * FROM material_requests WHERE id = ?");
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if (!$request) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit;
}

if ($request['status'] !== 'Pending') {
    echo json_encode(['success' => false, 'message' => 'Request has already been processed']);
    exit;
}

try {
    if ($action === 'reject') {
        $rejection_reason = trim($_POST['rejection_reason'] ?? '');
        
        if (empty($rejection_reason)) {
            echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
            exit;
        }
        
        // Reject request
        $stmt = $pdo->prepare("UPDATE material_requests SET status = 'Rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $rejection_reason, $request_id]);
        
        // Log activity
        logActivity($pdo, $_SESSION['user_id'], 'Reject', 'Request', "Rejected request: {$request['request_no']}");
        
        echo json_encode(['success' => true, 'message' => 'Request rejected successfully']);
        
    } else {
        // Approve request
        $stmt = $pdo->prepare("UPDATE material_requests SET status = 'Approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $request_id]);
        
        // Log activity
        logActivity($pdo, $_SESSION['user_id'], 'Approve', 'Request', "Approved request: {$request['request_no']}");
        
        echo json_encode(['success' => true, 'message' => 'Request approved successfully']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error processing request: ' . $e->getMessage()]);
}
