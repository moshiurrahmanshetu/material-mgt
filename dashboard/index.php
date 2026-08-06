<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/permission_check.php';

$page_title = 'Dashboard';
requirePermission('dashboard.view');

// Get dashboard statistics
$total_materials = $pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
$total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$total_suppliers = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$low_stock_count = $pdo->query("SELECT COUNT(*) FROM materials WHERE current_stock <= minimum_stock AND current_stock > 0")->fetchColumn();
$pending_requests = $pdo->query("SELECT COUNT(*) FROM material_requests WHERE status = 'Pending'")->fetchColumn();
$approved_requests = $pdo->query("SELECT COUNT(*) FROM material_requests WHERE status = 'Approved'")->fetchColumn();

// Get recent purchases
$recent_purchases = $pdo->query("SELECT pm.*, s.supplier_name FROM purchase_master pm INNER JOIN suppliers s ON pm.supplier_id = s.id ORDER BY pm.created_at DESC LIMIT 5")->fetchAll();

// Get recent issues
$recent_issues = $pdo->query("SELECT mi.*, m.material_name, u.full_name as employee_name FROM material_issues mi INNER JOIN materials m ON mi.material_id = m.id INNER JOIN users u ON mi.employee_id = u.id ORDER BY mi.created_at DESC LIMIT 5")->fetchAll();

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
            <h3><?php echo $total_materials; ?></h3>
            <p>Total Materials</p>
            <i class="bi bi-box"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stat-card success">
            <h3><?php echo $total_categories; ?></h3>
            <p>Total Categories</p>
            <i class="bi bi-tags"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stat-card warning">
            <h3><?php echo $total_suppliers; ?></h3>
            <p>Total Suppliers</p>
            <i class="bi bi-truck"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="stat-card info">
            <h3><?php echo $pending_requests; ?></h3>
            <p>Pending Requests</p>
            <i class="bi bi-file-earmark-text"></i>
        </div>
    </div>
</div>

<!-- Additional Stat Cards -->
<div class="row mb-4">
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="stat-card secondary">
            <h3><?php echo $low_stock_count; ?></h3>
            <p>Low Stock Items</p>
            <i class="bi bi-exclamation-triangle"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="stat-card primary">
            <h3><?php echo $approved_requests; ?></h3>
            <p>Approved Requests</p>
            <i class="bi bi-check-circle"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="stat-card success">
            <h3><?php echo count($recent_purchases); ?></h3>
            <p>Recent Purchases</p>
            <i class="bi bi-cart-plus"></i>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cart-plus me-2"></i>Recent Purchases
            </div>
            <div class="card-body">
                <?php if (empty($recent_purchases)): ?>
                <div class="text-center py-3">
                    <p class="text-muted">No recent purchases</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Purchase No</th>
                                <th>Supplier</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_purchases as $purchase): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($purchase['purchase_no']); ?></code></td>
                                <td><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                                <td><?php echo number_format($purchase['total_amount'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-box-arrow-right me-2"></i>Recent Issues
            </div>
            <div class="card-body">
                <?php if (empty($recent_issues)): ?>
                <div class="text-center py-3">
                    <p class="text-muted">No recent issues</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Issue No</th>
                                <th>Material</th>
                                <th>Employee</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_issues as $issue): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($issue['issue_no']); ?></code></td>
                                <td><?php echo htmlspecialchars($issue['material_name']); ?></td>
                                <td><?php echo htmlspecialchars($issue['employee_name']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
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
