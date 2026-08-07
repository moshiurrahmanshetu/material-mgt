<?php
if (!defined('APP_ACCESS')) {
    define('APP_ACCESS', true);
}

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/functions.php';

// Graceful fallback if config file is missing
$config_file = __DIR__ . '/../config/db.php';
if (!file_exists($config_file)) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    // Go up two levels since we're in includes/ subfolder
    $path = dirname(dirname($path));
    $path = rtrim($path, '/\\');
    header('Location: ' . $protocol . '://' . $host . $path . '/install/index.php');
    exit;
}

require_once $config_file;
require_once __DIR__ . '/permission_check.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect(BASE_URL . '/auth/login.php');
}

// Check session timeout
if (isset($_SESSION['last_activity'])) {
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        redirect(BASE_URL . '/auth/login.php');
    }
}
$_SESSION['last_activity'] = time();

// Load user permissions if not already loaded
if (!isset($_SESSION['permissions'])) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.permission_key 
        FROM permissions p
        INNER JOIN role_permissions rp ON p.id = rp.permission_id
        WHERE rp.role_id = ?
    ");
    $stmt->execute([$_SESSION['role_id']]);
    $_SESSION['permissions'] = array_column($stmt->fetchAll(), 'permission_key');
}
