<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

requirePermission('users.create');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$role_id = $_POST['role_id'] ?? 0;
$status = $_POST['status'] ?? 'Active';

// Server-side validation
if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($role_id)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit;
}

if (!isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
    exit;
}

// Check email uniqueness
if (emailExists($pdo, $email)) {
    echo json_encode(['success' => false, 'message' => 'Email already exists']);
    exit;
}

// Check username uniqueness
if (usernameExists($pdo, $username)) {
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    exit;
}

// Generate user code
$prefix = 'USR-';
$stmt = $pdo->prepare("SELECT user_code FROM users WHERE user_code LIKE ? ORDER BY user_code DESC LIMIT 1");
$stmt->execute([$prefix . '%']);
$lastCode = $stmt->fetch();

if ($lastCode) {
    $lastNumber = (int)substr($lastCode['user_code'], 4);
    $newNumber = $lastNumber + 1;
} else {
    $newNumber = 1;
}

$user_code = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $pdo->prepare("INSERT INTO users (user_code, full_name, email, username, password, phone, role_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$user_code, $full_name, $email, $username, $hashed_password, $phone, $role_id, $status]);

// Log activity
logActivity($pdo, $_SESSION['user_id'], 'Create', 'Users', "Created user: $username");

echo json_encode(['success' => true, 'message' => 'User created successfully']);
