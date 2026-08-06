<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/permission_check.php';

$page_title = 'Dashboard';
requirePermission('dashboard.view');
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Dashboard</h1>
    <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
</div>

<!-- Stat Cards -->
<div class="row">
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stat-card primary">
            <h3>0</h3>
            <p>Total Materials</p>
            <i class="bi bi-box"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stat-card success">
            <h3>0</h3>
            <p>Total Categories</p>
            <i class="bi bi-tags"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stat-card warning">
            <h3>0</h3>
            <p>Total Suppliers</p>
            <i class="bi bi-truck"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stat-card info">
            <h3>0</h3>
            <p>Pending Requests</p>
            <i class="bi bi-file-earmark-text"></i>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Recent Activity</span>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="text-muted mt-3">Activity tracking coming soon</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Quick Actions
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (hasPermission('materials.create')): ?>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/modules/materials/create.php" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-plus-circle me-2"></i>Add Material
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('purchase.create')): ?>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/modules/purchase/create.php" class="btn btn-outline-success w-100 py-3">
                            <i class="bi bi-cart-plus me-2"></i>New Purchase
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('issue.create')): ?>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/modules/issue/create.php" class="btn btn-outline-warning w-100 py-3">
                            <i class="bi bi-box-arrow-right me-2"></i>Issue Material
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('request.create')): ?>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/modules/request/create.php" class="btn btn-outline-info w-100 py-3">
                            <i class="bi bi-file-earmark-plus me-2"></i>New Request
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
