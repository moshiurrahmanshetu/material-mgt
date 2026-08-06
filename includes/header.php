<?php
// Prevent direct access
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

require_once __DIR__ . '/functions.php';

$page_title = isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <button class="btn btn-outline-light d-lg-none me-2" type="button" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/dashboard/index.php">
                <i class="bi bi-box-seam me-2"></i><?php echo SITE_NAME; ?>
            </a>
            
            <div class="ms-auto">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <img src="<?php echo getAvatarUrl($_SESSION['avatar'] ?? null); ?>" alt="Avatar" class="rounded-circle me-2" width="32" height="32">
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header"><?php echo htmlspecialchars($_SESSION['role_name']); ?></h6></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile/index.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile/change_password.php"><i class="bi bi-key me-2"></i>Change Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="wrapper">
        <aside class="sidebar" id="sidebar">
            <?php include __DIR__ . '/sidebar.php'; ?>
        </aside>
        
        <main class="main-content">
            <div class="container-fluid py-4">
                <?php
                $flash = getFlashMessage();
                if ($flash):
                ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
