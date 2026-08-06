<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('categories.view');

header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;

if (!$id) {
    echo json_encode(null);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

echo json_encode($category);
