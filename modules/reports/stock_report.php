<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Stock Report';
requirePermission('reports.view');

// Get filters
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Get categories for dropdown
$stmt = $pdo->prepare("SELECT id, category_name FROM categories WHERE status = 'Active' ORDER BY category_name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Build query
$sql = "SELECT m.*, c.category_name 
        FROM materials m 
        INNER JOIN categories c ON m.category_id = c.id 
        WHERE 1=1";
$params = [];

if (!empty($category_filter)) {
    $sql .= " AND m.category_id = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY m.material_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all_materials = $stmt->fetchAll();

// Filter by stock status after fetching
$materials = [];
foreach ($all_materials as $material) {
    $stock_status = '';
    if ($material['current_stock'] == 0) {
        $stock_status = 'Out of Stock';
    } elseif ($material['current_stock'] <= $material['minimum_stock']) {
        $stock_status = 'Low Stock';
    } else {
        $stock_status = 'In Stock';
    }
    
    if (empty($status_filter) || $stock_status === $status_filter) {
        $materials[] = array_merge($material, ['stock_status' => $stock_status]);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Stock Report</h1>
    <a href="<?php echo BASE_URL; ?>/modules/reports/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Reports
    </a>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4 mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="status" class="form-label">Stock Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="In Stock" <?php echo $status_filter === 'In Stock' ? 'selected' : ''; ?>>In Stock</option>
                    <option value="Low Stock" <?php echo $status_filter === 'Low Stock' ? 'selected' : ''; ?>>Low Stock</option>
                    <option value="Out of Stock" <?php echo $status_filter === 'Out of Stock' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter me-2"></i>Apply Filter
                </button>
            </div>
        </form>
        <div class="row mt-2">
            <div class="col-12">
                <a href="<?php echo BASE_URL; ?>/modules/reports/stock_report.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                <a href="<?php echo BASE_URL; ?>/modules/reports/stock_report.php?status=Low+Stock" class="btn btn-sm btn-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>Show Low Stock Only
                </a>
                <a href="<?php echo BASE_URL; ?>/modules/reports/export_csv.php?report=stock&<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-success">
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
                        <th>Material Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Current Stock</th>
                        <th>Minimum Stock</th>
                        <th>Stock Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $material): ?>
                    <?php
                    $status_colors = [
                        'In Stock' => 'success',
                        'Low Stock' => 'warning',
                        'Out of Stock' => 'danger'
                    ];
                    ?>
                    <tr class="<?php echo $material['stock_status'] === 'Out of Stock' || $material['stock_status'] === 'Low Stock' ? 'table-' . $status_colors[$material['stock_status']] : ''; ?>">
                        <td><code><?php echo htmlspecialchars($material['material_code']); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($material['material_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($material['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($material['unit']); ?></td>
                        <td>
                            <span class="<?php echo $material['stock_status'] === 'Out of Stock' || $material['stock_status'] === 'Low Stock' ? 'fw-bold' : ''; ?>">
                                <?php echo number_format($material['current_stock'], 2); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($material['minimum_stock'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $status_colors[$material['stock_status']]; ?>">
                                <?php echo htmlspecialchars($material['stock_status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($materials)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
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
