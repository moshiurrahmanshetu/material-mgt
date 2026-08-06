<?php
// Prevent direct access
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Sanitize input to prevent XSS
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token
function generateCsrfToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// Verify CSRF token
function verifyCsrfToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME]) || !hash_equals($_SESSION[CSRF_TOKEN_NAME], $token)) {
        return false;
    }
    return true;
}

// Set flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get and clear flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

// Generate unique user code
function generateUserCode($pdo) {
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
    
    return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
}

// Log activity
function logActivity($pdo, $user_id, $action, $module, $description = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, module, description, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $module, $description, $ip_address]);
}

// Check if email exists
function emailExists($pdo, $email, $exclude_id = null) {
    $sql = "SELECT id FROM users WHERE email = ?";
    $params = [$email];
    
    if ($exclude_id) {
        $sql .= " AND id != ?";
        $params[] = $exclude_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() !== false;
}

// Check if username exists
function usernameExists($pdo, $username, $exclude_id = null) {
    $sql = "SELECT id FROM users WHERE username = ?";
    $params = [$username];
    
    if ($exclude_id) {
        $sql .= " AND id != ?";
        $params[] = $exclude_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() !== false;
}

// Validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Get avatar URL
function getAvatarUrl($avatar) {
    if ($avatar) {
        return BASE_URL . '/uploads/avatars/' . $avatar;
    }
    return BASE_URL . '/assets/images/avatars/default-avatar.png';
}

// Redirect helper
function redirect($url) {
    header("Location: $url");
    exit;
}
