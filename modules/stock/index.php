<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Stock Management';
requirePermission('stock.view');

// Handle search and filter
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query
$sql = "SELECT m.*, c.category_name 
        FROM materials m 
        INNER JOIN categories c ON m.category_id = c.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (m.material_code LIKE ? OR m.material_name LIKE ?)";
    $params = array_merge($params, array_fill(0, 2, "%$search%"));
}

if (!empty($category_filter)) {
    $sql .= " AND m.category_id = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY m.material_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$materials = $stmt->fetchAll();

// Get categories for filter dropdown
$stmt = $pdo->prepare("SELECT id, category_name FROM categories WHERE status = 'Active' ORDER BY category_name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Calculate summary counts
$total_materials = count($materials);
$low_stock_count = 0;
$out_of_stock_count = 0;

foreach ($materials as $material) {
    if ($material['current_stock'] == 0) {
        $out_of_stock_count++;
    } elseif ($material['current_stock'] <= $material['minimum_stock']) {
        $low_stock_count++;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Stock Management</h1>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Materials</h5>
                <h2 class="display-4"><?php echo $total_materials; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Low Stock</h5>
                <h2 class="display-4"><?php echo $low_stock_count; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Out of Stock</h5>
                <h2 class="display-4"><?php echo $out_of_stock_count; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search materials..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="In Stock" <?php echo $status_filter === 'In Stock' ? 'selected' : ''; ?>>In Stock</option>
                    <option value="Low Stock" <?php echo $status_filter === 'Low Stock' ? 'selected' : ''; ?>>Low Stock</option>
                    <option value="Out of Stock" <?php echo $status_filter === 'Out of Stock' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-5 text-end">
                <span class="text-muted"><?php echo count($materials); ?> materials found</span>
            </div>
        </div>
    </div>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $material): ?>
                    <?php
                    if ($material['current_stock'] == 0) {
                        $stock_status = 'Out of Stock';
                        $stock_badge = 'danger';
                    } elseif ($material['current_stock'] <= $material['minimum_stock']) {
                        $stock_status = 'Low Stock';
                        $stock_badge = 'warning';
                    } else {
                        $stock_status = 'In Stock';
                        $stock_badge = 'success';
                    }
                    ?>
                    <tr class="<?php echo $stock_badge === 'danger' || $stock_badge === 'warning' ? 'table-' . $stock_badge : ''; ?>">
                        <td><code><?php echo htmlspecialchars($material['material_code']); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($material['material_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($material['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($material['unit']); ?></td>
                        <td>
                            <span class="<?php echo $stock_badge === 'danger' || $stock_badge === 'warning' ? 'fw-bold' : ''; ?>">
                                <?php echo number_format($material['current_stock'], 2); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($material['minimum_stock'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $stock_badge; ?>">
                                <?php echo $stock_status; ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/modules/stock/history.php?material_id=<?php echo $material['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-clock-history"></i> History
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($materials)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No materials found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});

document.getElementById('categoryFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    let url = '?';
    const params = [];
    if (search) params.push('search=' + encodeURIComponent(search));
    if (category) params.push('category=' + encodeURIComponent(category));
    if (status) params.push('status=' + encodeURIComponent(status));
    
    window.location.href = url + params.join('&');
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
