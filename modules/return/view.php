<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'View Return';
requirePermission('return.view');

$return_id = $_GET['id'] ?? 0;

if (!$return_id) {
    setFlashMessage('danger', 'Invalid return ID');
    redirect(BASE_URL . '/modules/return/index.php');
}

// Get return details
$stmt = $pdo->prepare("SELECT mr.*, mi.issue_no, m.material_name, m.unit, 
                      u.full_name as created_by_name 
                      FROM material_returns mr 
                      INNER JOIN material_issues mi ON mr.issue_id = mi.id 
                      INNER JOIN materials m ON mr.material_id = m.id 
                      LEFT JOIN users u ON mr.created_by = u.id 
                      WHERE mr.id = ?");
$stmt->execute([$return_id]);
$return = $stmt->fetch();

if (!$return) {
    setFlashMessage('danger', 'Return not found');
    redirect(BASE_URL . '/modules/return/index.php');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Return Details</h1>
    <a href="<?php echo BASE_URL; ?>/modules/return/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Returns
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Return Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>Return No:</strong>
                <p><code><?php echo htmlspecialchars($return['return_no']); ?></code></p>
            </div>
            <div class="col-md-3">
                <strong>Issue No:</strong>
                <p><code><?php echo htmlspecialchars($return['issue_no']); ?></code></p>
            </div>
            <div class="col-md-3">
                <strong>Return Date:</strong>
                <p><?php echo date('M d, Y', strtotime($return['return_date'])); ?></p>
            </div>
            <div class="col-md-3">
                <strong>Recorded By:</strong>
                <p><?php echo htmlspecialchars($return['created_by_name'] ?? 'System'); ?></p>
            </div>
        </div>
        <?php if (!empty($return['remarks'])): ?>
        <div class="row mt-2">
            <div class="col-12">
                <strong>Remarks:</strong>
                <p><?php echo htmlspecialchars($return['remarks']); ?></p>
            </div>
        </div>
        <?php endif; ?>
        <div class="row mt-2">
            <div class="col-md-6">
                <strong>Created At:</strong>
                <p><?php echo date('M d, Y H:i', strtotime($return['created_at'])); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Material Details</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>Material:</strong>
                <p><?php echo htmlspecialchars($return['material_name']); ?></p>
            </div>
            <div class="col-md-6">
                <strong>Unit:</strong>
                <p><?php echo htmlspecialchars($return['unit']); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <strong>Returned Quantity:</strong>
                <p class="fw-bold text-success">+<?php echo number_format($return['quantity'], 2); ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
