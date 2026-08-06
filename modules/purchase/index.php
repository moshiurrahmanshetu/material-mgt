<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Purchases';
requirePermission('purchase.view');

// Handle search and filter
$search = $_GET['search'] ?? '';
$supplier_filter = $_GET['supplier'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$sql = "SELECT pm.*, s.supplier_name, u.full_name as created_by_name 
        FROM purchase_master pm 
        INNER JOIN suppliers s ON pm.supplier_id = s.id 
        LEFT JOIN users u ON pm.created_by = u.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (pm.purchase_no LIKE ? OR pm.invoice_number LIKE ?)";
    $params = array_fill(0, 2, "%$search%");
}

if (!empty($supplier_filter)) {
    $sql .= " AND pm.supplier_id = ?";
    $params[] = $supplier_filter;
}

if (!empty($date_from)) {
    $sql .= " AND pm.purchase_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND pm.purchase_date <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY pm.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$purchases = $stmt->fetchAll();

// Get suppliers for filter dropdown
$stmt = $pdo->prepare("SELECT id, supplier_name FROM suppliers WHERE status = 'Active' ORDER BY supplier_name");
$stmt->execute();
$suppliers = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Purchases</h1>
    <?php if (hasPermission('purchase.create')): ?>
    <a href="<?php echo BASE_URL; ?>/modules/purchase/create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>New Purchase
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search purchases..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="supplierFilter">
                    <option value="">All Suppliers</option>
                    <?php foreach ($suppliers as $sup): ?>
                    <option value="<?php echo $sup['id']; ?>" <?php echo $supplier_filter == $sup['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sup['supplier_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" id="dateFrom" value="<?php echo htmlspecialchars($date_from); ?>" placeholder="From Date">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" id="dateTo" value="<?php echo htmlspecialchars($date_to); ?>" placeholder="To Date">
            </div>
            <div class="col-md-3 text-end">
                <button class="btn btn-outline-secondary" onclick="applyFilters()">
                    <i class="bi bi-filter me-2"></i>Apply Filters
                </button>
                <button class="btn btn-outline-secondary" onclick="clearFilters()">
                    <i class="bi bi-x-circle me-2"></i>Clear
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Purchase No</th>
                        <th>Purchase Date</th>
                        <th>Supplier</th>
                        <th>Invoice Number</th>
                        <th>Total Amount</th>
                        <th>Created By</th>
                        <th>Actions</th>
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
                        <td>
                            <a href="<?php echo BASE_URL; ?>/modules/purchase/view.php?id=<?php echo $purchase['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($purchases)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No purchases found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const supplier = document.getElementById('supplierFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    let url = '?';
    const params = [];
    if (search) params.push('search=' + encodeURIComponent(search));
    if (supplier) params.push('supplier=' + encodeURIComponent(supplier));
    if (dateFrom) params.push('date_from=' + encodeURIComponent(dateFrom));
    if (dateTo) params.push('date_to=' + encodeURIComponent(dateTo));
    
    window.location.href = url + params.join('&');
}

function clearFilters() {
    window.location.href = '?';
}

document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
