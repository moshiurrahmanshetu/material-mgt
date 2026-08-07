<?php
// Site Configuration
define('SITE_NAME', 'Material Management System');

// Dynamic BASE_URL - works regardless of installation folder
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Calculate base path from document root to site root
// Since this file is at /config/constants.php, site root is one level up
$doc_root = $_SERVER['DOCUMENT_ROOT'];
$config_path = str_replace('\\', '/', __DIR__); // Normalize path

// Calculate relative path from document root to config folder
if (strpos($config_path, $doc_root) === 0) {
    $base_path = substr($config_path, strlen($doc_root));
    // Remove /config to get site root
    $base_path = dirname($base_path);
} else {
    // Fallback: use SCRIPT_NAME and navigate up
    $base_path = dirname($_SERVER['SCRIPT_NAME']);
    // Navigate up until we find the config folder
    $max_iterations = 10;
    $iterations = 0;
    while (!file_exists($doc_root . $base_path . '/config/constants.php') && $base_path !== '/' && $iterations < $max_iterations) {
        $base_path = dirname($base_path);
        $iterations++;
    }
}

// Remove trailing slash
$base_path = rtrim($base_path, '/\\');
define('BASE_URL', $protocol . '://' . $host . $base_path);

// File Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('AVATAR_UPLOAD_PATH', __DIR__ . '/../uploads/avatars/');
define('MAX_FILE_SIZE', 2097152); // 2MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Session Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds

// Security Configuration
define('CSRF_TOKEN_NAME', 'csrf_token');
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 60); // 60 seconds

// Date/Time Configuration
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('TIMEZONE', 'UTC');
date_default_timezone_set(TIMEZONE);

// Settings Configuration
define('SYSTEM_NAME', 'Material Management System');
