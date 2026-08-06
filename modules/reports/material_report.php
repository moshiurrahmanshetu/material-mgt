<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Material Report';
requirePermission('reports.view');

// Get filters
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Get categories for dropdown
$stmt = $pdo->prepare("SELECT id, category_name FROM categories WHERE status = 'Active' ORDER BY category_name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Build query
$sql = "SELECT m.*, c.category_name,
        COALESCE(SUM(pi.quantity), 0) as total_purchased,
        COALESCE(SUM(mi.issue_quantity), 0) as total_issued
        FROM materials m 
        INNER JOIN categories c ON m.category_id = c.id 
        LEFT JOIN purchase_items pi ON m.id = pi.material_id
        LEFT JOIN material_issues mi ON m.id = mi.material_id
        WHERE 1=1";
$params = [];

if (!empty($category_filter)) {
    $sql .= " AND m.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($status_filter)) {
    $sql .= " AND m.status = ?";
    $params[] = $status_filter;
}

$sql .= " GROUP BY m.id ORDER BY m.material_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$materials = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Material Report</h1>
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
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="Active" <?php echo $status_filter === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo $status_filter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
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
                <a href="<?php echo BASE_URL; ?>/modules/reports/material_report.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                <a href="<?php echo BASE_URL; ?>/modules/reports/export_csv.php?report=material&<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-success">
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
                        <th>Min Stock</th>
                        <th>Total Purchased</th>
                        <th>Total Issued</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $material): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($material['material_code']); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($material['material_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($material['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($material['unit']); ?></td>
                        <td><?php echo number_format($material['current_stock'], 2); ?></td>
                        <td><?php echo number_format($material['minimum_stock'], 2); ?></td>
                        <td class="text-success">+<?php echo number_format($material['total_purchased'], 2); ?></td>
                        <td class="text-danger">-<?php echo number_format($material['total_issued'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($materials)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
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
