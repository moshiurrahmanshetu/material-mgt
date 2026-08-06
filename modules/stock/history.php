<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Stock History';
requirePermission('stock.view');

$material_id = $_GET['material_id'] ?? 0;

if (!$material_id) {
    setFlashMessage('danger', 'Invalid material ID');
    redirect(BASE_URL . '/modules/stock/index.php');
}

// Get material details
$stmt = $pdo->prepare("SELECT m.*, c.category_name FROM materials m INNER JOIN categories c ON m.category_id = c.id WHERE m.id = ?");
$stmt->execute([$material_id]);
$material = $stmt->fetch();

if (!$material) {
    setFlashMessage('danger', 'Material not found');
    redirect(BASE_URL . '/modules/stock/index.php');
}

// Get stock movements
$stmt = $pdo->prepare("SELECT sm.*, u.full_name as recorded_by_name 
                      FROM stock_movements sm 
                      LEFT JOIN users u ON sm.created_by = u.id 
                      WHERE sm.material_id = ? 
                      ORDER BY sm.created_at DESC");
$stmt->execute([$material_id]);
$movements = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Stock History</h1>
    <a href="<?php echo BASE_URL; ?>/modules/stock/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Stock
    </a>
</div>

<!-- Material Info Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Material Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>Material Code:</strong>
                <p><code><?php echo htmlspecialchars($material['material_code']); ?></code></p>
            </div>
            <div class="col-md-3">
                <strong>Material Name:</strong>
                <p><?php echo htmlspecialchars($material['material_name']); ?></p>
            </div>
            <div class="col-md-3">
                <strong>Category:</strong>
                <p><?php echo htmlspecialchars($material['category_name']); ?></p>
            </div>
            <div class="col-md-3">
                <strong>Unit:</strong>
                <p><?php echo htmlspecialchars($material['unit']); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <strong>Current Stock:</strong>
                <p class="display-6"><?php echo number_format($material['current_stock'], 2); ?></p>
            </div>
            <div class="col-md-4">
                <strong>Minimum Stock:</strong>
                <p class="h4"><?php echo number_format($material['minimum_stock'], 2); ?></p>
            </div>
            <div class="col-md-4">
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
                <strong>Stock Status:</strong>
                <p><span class="badge bg-<?php echo $stock_badge; ?> fs-6"><?php echo $stock_status; ?></span></p>
            </div>
        </div>
    </div>
</div>

<!-- Stock Movements Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Stock Movement History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Movement Type</th>
                        <th>Reference No</th>
                        <th>Quantity Change</th>
                        <th>Previous Stock</th>
                        <th>New Stock</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $movement): ?>
                    <?php
                    $type_colors = [
                        'Purchase' => 'success',
                        'Issue' => 'danger',
                        'Return' => 'info',
                        'Adjustment' => 'secondary'
                    ];
                    $color = $type_colors[$movement['movement_type']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><?php echo date('M d, Y H:i', strtotime($movement['created_at'])); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $color; ?>">
                                <?php echo htmlspecialchars($movement['movement_type']); ?>
                            </span>
                        </td>
                        <td><code><?php echo htmlspecialchars($movement['reference_no'] ?? '-'); ?></code></td>
                        <td>
                            <span class="<?php echo $movement['quantity_change'] > 0 ? 'text-success' : 'text-danger'; ?> fw-bold">
                                <?php echo $movement['quantity_change'] > 0 ? '+' : ''; ?><?php echo number_format($movement['quantity_change'], 2); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($movement['previous_stock'], 2); ?></td>
                        <td><strong><?php echo number_format($movement['new_stock'], 2); ?></strong></td>
                        <td><?php echo htmlspecialchars($movement['recorded_by_name'] ?? 'System'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($movements)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No stock movements found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
