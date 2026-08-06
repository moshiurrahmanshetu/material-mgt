<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('danger', 'Invalid request method');
    redirect(BASE_URL . '/auth/login.php');
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    setFlashMessage('danger', 'Invalid security token');
    redirect(BASE_URL . '/auth/login.php');
}

// Get and sanitize inputs
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validate inputs
if (empty($username) || empty($password)) {
    setFlashMessage('danger', 'Please fill in all fields');
    redirect(BASE_URL . '/auth/login.php');
}

// Check for login lockout
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$stmt = $pdo->prepare("SELECT * FROM login_attempts WHERE username = ? AND ip_address = ? AND locked_until > NOW()");
$stmt->execute([$username, $ip_address]);
$locked_attempt = $stmt->fetch();

if ($locked_attempt) {
    $remaining_time = strtotime($locked_attempt['locked_until']) - time();
    setFlashMessage('danger', "Too many failed attempts. Please try again in $remaining_time seconds.");
    redirect(BASE_URL . '/auth/login.php');
}

// Fetch user by username or email
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $username]);
$user = $stmt->fetch();

// Verify credentials
if ($user && password_verify($password, $user['password'])) {
    // Check if user is active
    if ($user['status'] !== 'Active') {
        setFlashMessage('danger', 'Your account is inactive. Please contact administrator.');
        redirect(BASE_URL . '/auth/login.php');
    }
    
    // Clear login attempts on successful login
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE username = ? AND ip_address = ?");
    $stmt->execute([$username, $ip_address]);
    
    // Fetch role name
    $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
    $stmt->execute([$user['role_id']]);
    $role = $stmt->fetch();
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_code'] = $user['user_code'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['role_name'] = $role['role_name'];
    $_SESSION['avatar'] = $user['avatar'];
    $_SESSION['last_activity'] = time();
    
    // Load permissions
    $stmt = $pdo->prepare("
        SELECT p.permission_key 
        FROM permissions p
        INNER JOIN role_permissions rp ON p.id = rp.permission_id
        WHERE rp.role_id = ?
    ");
    $stmt->execute([$user['role_id']]);
    $_SESSION['permissions'] = array_column($stmt->fetchAll(), 'permission_key');
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Log activity
    logActivity($pdo, $user['id'], 'Login', 'Auth', 'User logged in successfully');
    
    redirect(BASE_URL . '/dashboard/index.php');
} else {
    // Increment login attempts
    $stmt = $pdo->prepare("SELECT * FROM login_attempts WHERE username = ? AND ip_address = ?");
    $stmt->execute([$username, $ip_address]);
    $attempt = $stmt->fetch();
    
    if ($attempt) {
        $attempt_count = $attempt['attempt_count'] + 1;
        if ($attempt_count >= LOGIN_MAX_ATTEMPTS) {
            // Lock the account
            $locked_until = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_TIME);
            $stmt = $pdo->prepare("UPDATE login_attempts SET attempt_count = ?, locked_until = ? WHERE id = ?");
            $stmt->execute([$attempt_count, $locked_until, $attempt['id']]);
            setFlashMessage('danger', "Too many failed attempts. Account locked for " . LOGIN_LOCKOUT_TIME . " seconds.");
        } else {
            $stmt = $pdo->prepare("UPDATE login_attempts SET attempt_count = ?, last_attempt = NOW() WHERE id = ?");
            $stmt->execute([$attempt_count, $attempt['id']]);
            $remaining = LOGIN_MAX_ATTEMPTS - $attempt_count;
            setFlashMessage('danger', "Invalid credentials. $remaining attempts remaining.");
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO login_attempts (username, ip_address, attempt_count) VALUES (?, ?, 1)");
        $stmt->execute([$username, $ip_address]);
        setFlashMessage('danger', 'Invalid credentials. ' . (LOGIN_MAX_ATTEMPTS - 1) . ' attempts remaining.');
    }
    
    redirect(BASE_URL . '/auth/login.php');
}
