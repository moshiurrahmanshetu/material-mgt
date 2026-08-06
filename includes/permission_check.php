<?php
// Prevent direct access
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Check if user has a specific permission
function hasPermission($permissionKey) {
    if (!isset($_SESSION['permissions'])) {
        return false;
    }
    return in_array($permissionKey, $_SESSION['permissions']);
}

// Require a permission, redirect to 403 if not granted
function requirePermission($permissionKey) {
    if (!hasPermission($permissionKey)) {
        // Create a simple 403 page
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>403 - Access Denied</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="text-center">
                    <h1 class="display-1 fw-bold text-danger">403</h1>
                    <h2 class="mb-4">Access Denied</h2>
                    <p class="lead">You don't have permission to access this page.</p>
                    <a href="<?php echo BASE_URL; ?>/dashboard/index.php" class="btn btn-primary">Return to Dashboard</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Check if user has any of the given permissions
function hasAnyPermission($permissionKeys) {
    foreach ($permissionKeys as $key) {
        if (hasPermission($key)) {
            return true;
        }
    }
    return false;
}
