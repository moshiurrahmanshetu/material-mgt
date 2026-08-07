<?php
// Site Configuration
define('SITE_NAME', 'Material Management System');
define('BASE_URL', 'http://localhost/material-mgt');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'material_management_system');
define('DB_USER', 'root');
define('DB_PASS', '');

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
