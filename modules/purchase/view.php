<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'View Purchase';
requirePermission('purchase.view');

$purchase_id = $_GET['id'] ?? 0;

if (!$purchase_id) {
    setFlashMessage('danger', 'Invalid purchase ID');
    redirect(BASE_URL . '/modules/purchase/index.php');
}

// Get purchase details
$stmt = $pdo->prepare("SELECT pm.*, s.supplier_name, u.full_name as created_by_name 
                      FROM purchase_master pm 
                      INNER JOIN suppliers s ON pm.supplier_id = s.id 
                      LEFT JOIN users u ON pm.created_by = u.id 
                      WHERE pm.id = ?");
$stmt->execute([$purchase_id]);
$purchase = $stmt->fetch();

if (!$purchase) {
    setFlashMessage('danger', 'Purchase not found');
    redirect(BASE_URL . '/modules/purchase/index.php');
}

// Get purchase items
$stmt = $pdo->prepare("SELECT pi.*, m.material_name, m.unit 
                      FROM purchase_items pi 
                      INNER JOIN materials m ON pi.material_id = m.id 
                      WHERE pi.purchase_id = ?");
$stmt->execute([$purchase_id]);
$items = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Purchase Details</h1>
    <a href="<?php echo BASE_URL; ?>/modules/purchase/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to List
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Purchase Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>Purchase No:</strong>
                <p><code><?php echo htmlspecialchars($purchase['purchase_no']); ?></code></p>
            </div>
            <div class="col-md-3">
                <strong>Purchase Date:</strong>
                <p><?php echo date('M d, Y', strtotime($purchase['purchase_date'])); ?></p>
            </div>
            <div class="col-md-3">
                <strong>Supplier:</strong>
                <p><?php echo htmlspecialchars($purchase['supplier_name']); ?></p>
            </div>
            <div class="col-md-3">
                <strong>Invoice Number:</strong>
                <p><?php echo htmlspecialchars($purchase['invoice_number'] ?? '-'); ?></p>
            </div>
        </div>
        <?php if (!empty($purchase['remarks'])): ?>
        <div class="row mt-2">
            <div class="col-12">
                <strong>Remarks:</strong>
                <p><?php echo htmlspecialchars($purchase['remarks']); ?></p>
            </div>
        </div>
        <?php endif; ?>
        <div class="row mt-2">
            <div class="col-md-6">
                <strong>Created By:</strong>
                <p><?php echo htmlspecialchars($purchase['created_by_name'] ?? 'System'); ?></p>
            </div>
            <div class="col-md-6">
                <strong>Created At:</strong>
                <p><?php echo date('M d, Y H:i', strtotime($purchase['created_at'])); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Items</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Material</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($item['material_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td><?php echo number_format($item['quantity'], 2); ?></td>
                        <td><?php echo number_format($item['unit_price'], 2); ?></td>
                        <td><?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                        <td><strong><?php echo number_format($purchase['total_amount'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
