<?php
// Prevent direct access
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar-header d-flex align-items-center p-3 border-bottom border-secondary">
    <img src="<?php echo getAvatarUrl($_SESSION['avatar'] ?? null); ?>" alt="Avatar" class="rounded-circle me-2" width="40" height="40">
    <div class="sidebar-user-info flex-grow-1">
        <div class="fw-bold text-white"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
        <small class="text-white-50"><?php echo htmlspecialchars($_SESSION['role_name']); ?></small>
    </div>
    <button class="btn btn-sm btn-outline-light d-none d-lg-block" type="button" id="sidebarCollapse">
        <i class="bi bi-chevron-left"></i>
    </button>
</div>

<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link <?php echo $current_page == 'index.php' && strpos($_SERVER['REQUEST_URI'], '/dashboard/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/dashboard/index.php">
            <i class="bi bi-speedometer2 me-2"></i>
            <span class="nav-text">Dashboard</span>
        </a>
    </li>
    
    <?php if (hasPermission('materials.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/materials/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/materials/index.php">
            <i class="bi bi-box me-2"></i>
            <span class="nav-text">Materials</span>
        </a>
    </li>
    <?php endif; ?>
    
    <?php if (hasPermission('categories.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/categories/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/categories/index.php">
            <i class="bi bi-tags me-2"></i>
            <span class="nav-text">Categories</span>
        </a>
    </li>
    <?php endif; ?>
    
    <?php if (hasPermission('suppliers.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/suppliers/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/suppliers/index.php">
            <i class="bi bi-truck me-2"></i>
            <span class="nav-text">Suppliers</span>
        </a>
    </li>
    <?php endif; ?>
    
    <?php if (hasPermission('purchase.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/purchase/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/purchase/index.php">
            <i class="bi bi-cart-plus me-2"></i>
            <span class="nav-text">Purchase</span>
        </a>
    </li>
    <?php endif; ?>
    
    <?php if (hasPermission('issue.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/issue/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/issue/index.php">
            <i class="bi bi-box-arrow-right me-2"></i>
            <span class="nav-text">Issue</span>
        </a>
    </li>
    <?php endif; ?>
    
    <?php if (hasPermission('request.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/request/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/request/index.php">
            <i class="bi bi-file-earmark-text me-2"></i>
            <span class="nav-text">Request</span>
        </a>
    </li>
    <?php endif; ?>
    
    <?php if (hasPermission('stock.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/stock/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/stock/index.php">
            <i class="bi bi-layers me-2"></i>
            <span class="nav-text">Stock</span>
        </a>
    </li>
    <?php endif; ?>
    
    <?php if (hasPermission('reports.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/reports/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/reports/index.php">
            <i class="bi bi-graph-up me-2"></i>
            <span class="nav-text">Reports</span>
        </a>
    </li>
    <?php endif; ?>
    
    <li class="nav-item mt-3">
        <div class="text-white-50 small px-3 mb-1">Administration</div>
    </li>
    
    <?php if (hasPermission('users.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/users/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/users/index.php">
            <i class="bi bi-people me-2"></i>
            <span class="nav-text">Users</span>
        </a>
    </li>
    <?php endif; ?>
    
    <?php if (hasPermission('roles.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/roles/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/roles/index.php">
            <i class="bi bi-shield-lock me-2"></i>
            <span class="nav-text">Roles</span>
        </a>
    </li>
    <?php endif; ?>
    
    <?php if (hasPermission('activity_log.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/activity-log/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/activity-log/index.php">
            <i class="bi bi-clock-history me-2"></i>
            <span class="nav-text">Activity Log</span>
        </a>
    </li>
    <?php endif; ?>
    
    <?php if (hasPermission('settings.view')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/modules/settings/') !== false ? 'active' : ''; ?>" 
           href="<?php echo BASE_URL; ?>/modules/settings/index.php">
            <i class="bi bi-gear me-2"></i>
            <span class="nav-text">Settings</span>
        </a>
    </li>
    <?php endif; ?>
</ul>
