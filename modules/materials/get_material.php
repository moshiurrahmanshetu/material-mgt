<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('materials.view');

header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;

if (!$id) {
    echo json_encode(null);
    exit;
}

$stmt = $pdo->prepare("SELECT m.*, c.category_name FROM materials m INNER JOIN categories c ON m.category_id = c.id WHERE m.id = ?");
$stmt->execute([$id]);
$material = $stmt->fetch();

echo json_encode($material);
