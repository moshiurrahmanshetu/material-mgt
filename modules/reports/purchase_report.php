<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Purchase Report';
requirePermission('reports.view');

// Get filters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$supplier_filter = $_GET['supplier'] ?? '';

// Get suppliers for dropdown
$stmt = $pdo->prepare("SELECT id, supplier_name FROM suppliers WHERE status = 'Active' ORDER BY supplier_name");
$stmt->execute();
$suppliers = $stmt->fetchAll();

// Build query
$sql = "SELECT pm.*, s.supplier_name, u.full_name as created_by_name 
        FROM purchase_master pm 
        INNER JOIN suppliers s ON pm.supplier_id = s.id 
        LEFT JOIN users u ON pm.created_by = u.id 
        WHERE 1=1";
$params = [];

if (!empty($date_from)) {
    $sql .= " AND pm.purchase_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND pm.purchase_date <= ?";
    $params[] = $date_to;
}

if (!empty($supplier_filter)) {
    $sql .= " AND pm.supplier_id = ?";
    $params[] = $supplier_filter;
}

$sql .= " ORDER BY pm.purchase_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$purchases = $stmt->fetchAll();

// Calculate grand total
$grand_total = array_sum(array_column($purchases, 'total_amount'));

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Purchase Report</h1>
    <a href="<?php echo BASE_URL; ?>/modules/reports/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Reports
    </a>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3 mb-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label for="supplier" class="form-label">Supplier</label>
                <select class="form-select" id="supplier" name="supplier">
                    <option value="">All Suppliers</option>
                    <?php foreach ($suppliers as $sup): ?>
                    <option value="<?php echo $sup['id']; ?>" <?php echo $supplier_filter == $sup['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sup['supplier_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-2"></i>Apply Filter
                </button>
            </div>
        </form>
        <div class="row mt-2">
            <div class="col-12">
                <a href="<?php echo BASE_URL; ?>/modules/reports/purchase_report.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                <a href="<?php echo BASE_URL; ?>/modules/reports/export_csv.php?report=purchase&<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-success">
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
                        <th>Purchase No</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Invoice No</th>
                        <th>Total Amount</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($purchase['purchase_no']); ?></code></td>
                        <td><?php echo date('M d, Y', strtotime($purchase['purchase_date'])); ?></td>
                        <td><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                        <td><?php echo htmlspecialchars($purchase['invoice_number'] ?? '-'); ?></td>
                        <td><strong><?php echo number_format($purchase['total_amount'], 2); ?></strong></td>
                        <td><?php echo htmlspecialchars($purchase['created_by_name'] ?? 'System'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($purchases)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No records found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($purchases)): ?>
                <tfoot>
                    <tr class="table-primary">
                        <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                        <td><strong><?php echo number_format($grand_total, 2); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
