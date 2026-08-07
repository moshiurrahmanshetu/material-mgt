<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/_lock_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$db_host = trim($_POST['db_host'] ?? '');
$db_port = trim($_POST['db_port'] ?? '3306');
$db_name = trim($_POST['db_name'] ?? '');
$db_user = trim($_POST['db_user'] ?? '');
$db_pass = trim($_POST['db_pass'] ?? '');

if (empty($db_host) || empty($db_name) || empty($db_user)) {
    echo json_encode(['success' => false, 'message' => 'Database host, name, and username are required']);
    exit;
}

try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Test a simple query to ensure connection is working
    $pdo->query("SELECT 1");
    
    echo json_encode(['success' => true, 'message' => 'Connection successful']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
