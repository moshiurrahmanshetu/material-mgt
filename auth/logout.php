<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log activity if user was logged in
if (isset($_SESSION['user_id'])) {
    logActivity($pdo, $_SESSION['user_id'], 'Logout', 'Auth', 'User logged out');
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
redirect(BASE_URL . '/auth/login.php');
