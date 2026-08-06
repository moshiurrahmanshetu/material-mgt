<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Supplier Report';
requirePermission('reports.view');

// Get filters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query
$sql = "SELECT s.*, 
        COUNT(DISTINCT pm.id) as total_purchases,
        COALESCE(SUM(pm.total_amount), 0) as total_purchase_amount
        FROM suppliers s 
        LEFT JOIN purchase_master pm ON s.id = pm.supplier_id 
        WHERE 1=1";
$params = [];

if (!empty($date_from)) {
    $sql .= " AND (pm.purchase_date >= ? OR pm.id IS NULL)";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND (pm.purchase_date <= ? OR pm.id IS NULL)";
    $params[] = $date_to;
}

if (!empty($status_filter)) {
    $sql .= " AND s.status = ?";
    $params[] = $status_filter;
}

$sql .= " GROUP BY s.id ORDER BY s.supplier_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$suppliers = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Supplier Report</h1>
    <a href="<?php echo BASE_URL; ?>/modules/reports/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Reports
    </a>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4 mb-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="Active" <?php echo $status_filter === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo $status_filter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-12 mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-2"></i>Apply Filter
                </button>
            </div>
        </form>
        <div class="row">
            <div class="col-12">
                <a href="<?php echo BASE_URL; ?>/modules/reports/supplier_report.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                <a href="<?php echo BASE_URL; ?>/modules/reports/export_csv.php?report=supplier&<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-download me-1"></i>Export CSV
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Results Card -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Supplier Code</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Total Purchases</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($supplier['supplier_code']); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($supplier['supplier_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($supplier['company'] ?? '-'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $supplier['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($supplier['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $supplier['total_purchases'] ?: 0; ?></td>
                        <td><strong><?php echo number_format($supplier['total_purchase_amount'], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($suppliers)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No records found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
