<?php
// Check if application is installed
$config_file = __DIR__ . '/config/db.php';
$lock_file = __DIR__ . '/install/installed.lock';

if (!file_exists($config_file) || !file_exists($lock_file)) {
    // Application not installed - redirect to installer
    header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/install/index.php');
    exit;
}

define('APP_ACCESS', true);
require_once __DIR__ . '/config/constants.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect based on login status
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/dashboard/index.php');
} else {
    header('Location: ' . BASE_URL . '/auth/login.php');
}
exit;
