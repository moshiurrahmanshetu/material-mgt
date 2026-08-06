<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Reports';
requirePermission('reports.view');

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Reports</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-cart-plus display-4 text-primary mb-3"></i>
                <h5>Purchase Report</h5>
                <p class="text-muted">View purchase history by date range and supplier</p>
                <a href="<?php echo BASE_URL; ?>/modules/reports/purchase_report.php" class="btn btn-primary">
                    View Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-box-arrow-right display-4 text-danger mb-3"></i>
                <h5>Issue Report</h5>
                <p class="text-muted">View material issues by date, employee, and material</p>
                <a href="<?php echo BASE_URL; ?>/modules/reports/issue_report.php" class="btn btn-danger">
                    View Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-file-earmark-text display-4 text-warning mb-3"></i>
                <h5>Request Report</h5>
                <p class="text-muted">View material requests by status and employee</p>
                <a href="<?php echo BASE_URL; ?>/modules/reports/request_report.php" class="btn btn-warning">
                    View Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-truck display-4 text-success mb-3"></i>
                <h5>Supplier Report</h5>
                <p class="text-muted">View supplier performance and purchase totals</p>
                <a href="<?php echo BASE_URL; ?>/modules/reports/supplier_report.php" class="btn btn-success">
                    View Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-box display-4 text-info mb-3"></i>
                <h5>Material Report</h5>
                <p class="text-muted">View material inventory by category</p>
                <a href="<?php echo BASE_URL; ?>/modules/reports/material_report.php" class="btn btn-info">
                    View Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-layers display-4 text-secondary mb-3"></i>
                <h5>Stock Report</h5>
                <p class="text-muted">View current stock levels and low stock items</p>
                <a href="<?php echo BASE_URL; ?>/modules/reports/stock_report.php" class="btn btn-secondary">
                    View Report
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
