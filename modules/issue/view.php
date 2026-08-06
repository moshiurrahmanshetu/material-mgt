<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'View Issue';
requirePermission('issue.view');

$issue_id = $_GET['id'] ?? 0;

if (!$issue_id) {
    setFlashMessage('danger', 'Invalid issue ID');
    redirect(BASE_URL . '/modules/issue/index.php');
}

// Get issue details
$stmt = $pdo->prepare("SELECT mi.*, mr.request_no, mr.requested_quantity, m.material_name, m.unit, 
                      u.full_name as employee_name, u2.full_name as issued_by_name 
                      FROM material_issues mi 
                      INNER JOIN material_requests mr ON mi.request_id = mr.id 
                      INNER JOIN materials m ON mi.material_id = m.id 
                      INNER JOIN users u ON mi.employee_id = u.id 
                      INNER JOIN users u2 ON mi.issued_by = u2.id 
                      WHERE mi.id = ?");
$stmt->execute([$issue_id]);
$issue = $stmt->fetch();

if (!$issue) {
    setFlashMessage('danger', 'Issue not found');
    redirect(BASE_URL . '/modules/issue/index.php');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Issue Details</h1>
    <a href="<?php echo BASE_URL; ?>/modules/issue/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Issues
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Issue Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>Issue No:</strong>
                <p><code><?php echo htmlspecialchars($issue['issue_no']); ?></code></p>
            </div>
            <div class="col-md-3">
                <strong>Request No:</strong>
                <p><code><?php echo htmlspecialchars($issue['request_no']); ?></code></p>
            </div>
            <div class="col-md-3">
                <strong>Issue Date:</strong>
                <p><?php echo date('M d, Y', strtotime($issue['issue_date'])); ?></p>
            </div>
            <div class="col-md-3">
                <strong>Issued By:</strong>
                <p><?php echo htmlspecialchars($issue['issued_by_name']); ?></p>
            </div>
        </div>
        <?php if (!empty($issue['remarks'])): ?>
        <div class="row mt-2">
            <div class="col-12">
                <strong>Remarks:</strong>
                <p><?php echo htmlspecialchars($issue['remarks']); ?></p>
            </div>
        </div>
        <?php endif; ?>
        <div class="row mt-2">
            <div class="col-md-6">
                <strong>Created At:</strong>
                <p><?php echo date('M d, Y H:i', strtotime($issue['created_at'])); ?></p>
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
            <div class="col-md-4">
                <strong>Material:</strong>
                <p><?php echo htmlspecialchars($issue['material_name']); ?></p>
            </div>
            <div class="col-md-4">
                <strong>Unit:</strong>
                <p><?php echo htmlspecialchars($issue['unit']); ?></p>
            </div>
            <div class="col-md-4">
                <strong>Employee:</strong>
                <p><?php echo htmlspecialchars($issue['employee_name']); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <strong>Requested Quantity:</strong>
                <p><?php echo number_format($issue['requested_quantity'], 2); ?></p>
            </div>
            <div class="col-md-6">
                <strong>Issued Quantity:</strong>
                <p class="fw-bold"><?php echo number_format($issue['issue_quantity'], 2); ?></p>
            </div>
        </div>
        <?php if ($issue['issue_quantity'] < $issue['requested_quantity']): ?>
        <div class="alert alert-warning mt-3">
            <strong>Partial Issue:</strong> Only <?php echo number_format($issue['issue_quantity'], 2); ?> out of <?php echo number_format($issue['requested_quantity'], 2); ?> was issued.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
