<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'New Material Return';
requirePermission('return.create');

// Get issues with returnable balance
$sql = "SELECT mi.*, m.material_name, m.unit, mr.request_no, u.full_name as employee_name,
        mi.issue_quantity - COALESCE(SUM(mr_ret.quantity), 0) as returnable_quantity
        FROM material_issues mi
        INNER JOIN material_requests mr ON mi.request_id = mr.id
        INNER JOIN materials m ON mi.material_id = m.id
        INNER JOIN users u ON mi.employee_id = u.id
        LEFT JOIN material_returns mr_ret ON mi.id = mr_ret.issue_id
        GROUP BY mi.id
        HAVING returnable_quantity > 0
        ORDER BY mi.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$issues = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">New Material Return</h1>
    <a href="<?php echo BASE_URL; ?>/modules/return/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to List
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form id="returnForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="mb-3">
                <label for="issue_id" class="form-label">Select Issue *</label>
                <select class="form-select" id="issue_id" name="issue_id" required onchange="loadIssueDetails()">
                    <option value="">Select Issue</option>
                    <?php foreach ($issues as $issue): ?>
                    <option value="<?php echo $issue['id']; ?>" 
                            data-material-id="<?php echo $issue['material_id']; ?>"
                            data-material-name="<?php echo htmlspecialchars($issue['material_name']); ?>"
                            data-unit="<?php echo htmlspecialchars($issue['unit']); ?>"
                            data-issue-quantity="<?php echo $issue['issue_quantity']; ?>"
                            data-returnable-quantity="<?php echo $issue['returnable_quantity']; ?>"
                            data-issue-no="<?php echo htmlspecialchars($issue['issue_no']); ?>">
                        <?php echo htmlspecialchars($issue['issue_no']); ?> - 
                        <?php echo htmlspecialchars($issue['material_name']); ?> (<?php echo htmlspecialchars($issue['unit']); ?>) - 
                        Employee: <?php echo htmlspecialchars($issue['employee_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="issueDetails" class="card bg-light mb-3 d-none">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Issue No:</strong>
                            <p id="detailIssueNo">-</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Material:</strong>
                            <p id="detailMaterial">-</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Issued Qty:</strong>
                            <p id="detailIssuedQty">-</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Returnable:</strong>
                            <p id="detailReturnable" class="fw-bold text-success">-</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="quantity" class="form-label">Return Quantity *</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" step="0.01" min="0.01" required disabled>
                    <small class="text-muted" id="quantityHint">Select an issue first</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="return_date" class="form-label">Return Date *</label>
                    <input type="date" class="form-control" id="return_date" name="return_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
            </div>
            
            <input type="hidden" id="material_id" name="material_id">
            
            <div id="errorMessage" class="alert alert-danger d-none"></div>
            
            <div class="text-end">
                <a href="<?php echo BASE_URL; ?>/modules/return/index.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="bi bi-check-circle me-2"></i>Record Return
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let returnableQuantity = 0;

function loadIssueDetails() {
    const select = document.getElementById('issue_id');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        document.getElementById('issueDetails').classList.remove('d-none');
        document.getElementById('detailIssueNo').textContent = option.dataset.issueNo;
        document.getElementById('detailMaterial').textContent = option.dataset.materialName;
        document.getElementById('detailIssuedQty').textContent = parseFloat(option.dataset.issueQuantity).toFixed(2);
        document.getElementById('detailReturnable').textContent = parseFloat(option.dataset.returnableQuantity).toFixed(2);
        
        returnableQuantity = parseFloat(option.dataset.returnableQuantity);
        
        document.getElementById('quantity').disabled = false;
        document.getElementById('quantity').max = returnableQuantity;
        document.getElementById('quantity').value = returnableQuantity;
        document.getElementById('quantityHint').textContent = `Maximum returnable: ${returnableQuantity.toFixed(2)}`;
        document.getElementById('material_id').value = option.dataset.materialId;
        document.getElementById('submitBtn').disabled = false;
    } else {
        document.getElementById('issueDetails').classList.add('d-none');
        document.getElementById('quantity').disabled = true;
        document.getElementById('quantity').value = '';
        document.getElementById('quantityHint').textContent = 'Select an issue first';
        document.getElementById('material_id').value = '';
        document.getElementById('submitBtn').disabled = true;
        returnableQuantity = 0;
    }
}

document.getElementById('returnForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    
    if (quantity <= 0) {
        showError('Return quantity must be greater than 0');
        return;
    }
    
    if (quantity > returnableQuantity) {
        showError(`Return quantity cannot exceed returnable balance (${returnableQuantity.toFixed(2)})`);
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/return/save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo BASE_URL; ?>/modules/return/index.php';
        } else {
            showError(data.message || 'Error recording return');
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
