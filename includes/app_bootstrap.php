<?php
// Application Bootstrap - Loads settings and applies timezone
// Must be included after database connection is available

if (!isset($pdo)) {
    return; // No DB connection available yet
}

try {
    // Load settings from database
    $stmt = $pdo->prepare("SELECT * FROM settings WHERE id = 1");
    $stmt->execute();
    $app_settings = $stmt->fetch();
    
    if ($app_settings) {
        // Apply timezone setting
        if (!empty($app_settings['timezone'])) {
            date_default_timezone_set($app_settings['timezone']);
        }
        
        // Override system name constant
        if (!empty($app_settings['system_name'])) {
            if (!defined('SYSTEM_NAME')) {
                define('SYSTEM_NAME', $app_settings['system_name']);
            }
        }
    }
} catch (PDOException $e) {
    // Silently fail if settings can't be loaded - use defaults
}
