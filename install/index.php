<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include lock check
require_once __DIR__ . '/_lock_check.php';

// Define step order
$steps = [
    1 => 'step1_welcome.php',
    2 => 'step2_database.php',
    3 => 'step3_upload_sql.php',
    4 => 'step4_admin_setup.php',
    5 => 'step5_finish.php'
];

// Get current step from session or default to 1
$current_step = $_SESSION['install_step'] ?? 1;

// Validate step
if (!isset($steps[$current_step])) {
    $current_step = 1;
}

// Allow direct step navigation via URL parameter if valid
if (isset($_GET['step']) && isset($steps[$_GET['step']])) {
    // Only allow going forward or to current step, not skipping ahead
    if ($_GET['step'] <= $current_step) {
        $current_step = $_GET['step'];
    }
}

// Include the current step file
require_once __DIR__ . '/' . $steps[$current_step];
