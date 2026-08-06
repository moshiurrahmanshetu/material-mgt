<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'Issue Material';
requirePermission('issue.create');

$request_id = $_GET['request_id'] ?? 0;

if (!$request_id) {
    setFlashMessage('danger', 'Invalid request ID');
    redirect(BASE_URL . '/modules/issue/index.php');
}

// Get request details
$stmt = $pdo->prepare("SELECT mr.*, m.material_name, m.unit, m.current_stock, u.full_name as employee_name 
                      FROM material_requests mr 
                      INNER JOIN materials m ON mr.material_id = m.id 
                      INNER JOIN users u ON mr.employee_id = u.id 
                      WHERE mr.id = ?");
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if (!$request) {
    setFlashMessage('danger', 'Request not found');
    redirect(BASE_URL . '/modules/issue/index.php');
}

if ($request['status'] !== 'Approved') {
    setFlashMessage('danger', 'Request must be approved before issuing');
    redirect(BASE_URL . '/modules/issue/index.php');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Issue Material</h1>
    <a href="<?php echo BASE_URL; ?>/modules/issue/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Issues
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Request Details</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>Request No:</strong>
                <p><code><?php echo htmlspecialchars($request['request_no']); ?></code></p>
            </div>
            <div class="col-md-6">
                <strong>Employee:</strong>
                <p><?php echo htmlspecialchars($request['employee_name']); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <strong>Material:</strong>
                <p><?php echo htmlspecialchars($request['material_name']); ?> (<?php echo htmlspecialchars($request['unit']); ?>)</p>
            </div>
            <div class="col-md-6">
                <strong>Requested Quantity:</strong>
                <p><?php echo number_format($request['requested_quantity'], 2); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <strong>Current Stock:</strong>
                <p class="<?php echo $request['current_stock'] < $request['requested_quantity'] ? 'text-danger fw-bold' : ''; ?>">
                    <?php echo number_format($request['current_stock'], 2); ?>
                    <?php if ($request['current_stock'] < $request['requested_quantity']): ?>
                    <span class="badge bg-danger">Insufficient Stock</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-6">
                <strong>Department:</strong>
                <p><?php echo htmlspecialchars($request['department'] ?? '-'); ?></p>
            </div>
        </div>
        <?php if (!empty($request['purpose'])): ?>
        <div class="row">
            <div class="col-12">
                <strong>Purpose:</strong>
                <p><?php echo htmlspecialchars($request['purpose']); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form id="issueForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
            <input type="hidden" name="material_id" value="<?php echo $request['material_id']; ?>">
            <input type="hidden" name="employee_id" value="<?php echo $request['employee_id']; ?>">
            <input type="hidden" name="requested_quantity" value="<?php echo $request['requested_quantity']; ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="issue_quantity" class="form-label">Issue Quantity *</label>
                    <input type="number" class="form-control" id="issue_quantity" name="issue_quantity" 
                           step="0.01" min="0.01" max="<?php echo $request['current_stock']; ?>" 
                           value="<?php echo min($request['requested_quantity'], $request['current_stock']); ?>" required>
                    <small class="text-muted">Max available: <?php echo number_format($request['current_stock'], 2); ?></small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="issue_date" class="form-label">Issue Date *</label>
                    <input type="date" class="form-control" id="issue_date" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
            </div>
            
            <?php if ($request['current_stock'] < $request['requested_quantity']): ?>
            <div class="alert alert-warning">
                <strong>Partial Issue:</strong> Current stock (<?php echo number_format($request['current_stock'], 2); ?>) is less than requested quantity (<?php echo number_format($request['requested_quantity'], 2); ?>). You can issue up to the available stock.
            </div>
            <?php endif; ?>
            
            <div id="errorMessage" class="alert alert-danger d-none"></div>
            
            <div class="text-end">
                <a href="<?php echo BASE_URL; ?>/modules/issue/index.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Issue Material
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('issueForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Client-side validation
    const issueQuantity = parseFloat(document.getElementById('issue_quantity').value) || 0;
    const maxStock = parseFloat(<?php echo $request['current_stock']; ?>);
    const requestedQty = parseFloat(<?php echo $request['requested_quantity']; ?>);
    
    if (issueQuantity <= 0) {
        showError('Issue quantity must be greater than 0');
        return;
    }
    
    if (issueQuantity > maxStock) {
        showError('Issue quantity cannot exceed current stock');
        return;
    }
    
    if (issueQuantity > requestedQty) {
        showError('Issue quantity cannot exceed requested quantity');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/issue/save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo BASE_URL; ?>/modules/issue/index.php';
        } else {
            showError(data.message || 'Error issuing material');
        }
    })
    .catch(error => {
        showError('Error: ' + error);
    });
});

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
    setTimeout(() => {
        errorDiv.classList.add('d-none');
    }, 5000);
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
