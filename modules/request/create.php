<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/permission_check.php';

$page_title = 'New Material Request';
requirePermission('request.create');

// Get materials for dropdown (with current stock)
$stmt = $pdo->prepare("SELECT id, material_name, unit, current_stock FROM materials WHERE status = 'Active' ORDER BY material_name");
$stmt->execute();
$materials = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">New Material Request</h1>
    <a href="<?php echo BASE_URL; ?>/modules/request/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to List
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form id="requestForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="material_id" class="form-label">Material *</label>
                    <select class="form-select" id="material_id" name="material_id" required>
                        <option value="">Select Material</option>
                        <?php foreach ($materials as $mat): ?>
                        <option value="<?php echo $mat['id']; ?>" data-stock="<?php echo $mat['current_stock']; ?>">
                            <?php echo htmlspecialchars($mat['material_name']); ?> (<?php echo htmlspecialchars($mat['unit']); ?>) - Stock: <?php echo number_format($mat['current_stock'], 2); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="requested_quantity" class="form-label">Requested Quantity *</label>
                    <input type="number" class="form-control" id="requested_quantity" name="requested_quantity" step="0.01" min="0.01" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="department" class="form-label">Department</label>
                    <input type="text" class="form-control" id="department" name="department">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="request_date" class="form-label">Request Date *</label>
                    <input type="date" class="form-control" id="request_date" name="request_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="purpose" class="form-label">Purpose</label>
                <textarea class="form-control" id="purpose" name="purpose" rows="3"></textarea>
            </div>
            
            <div id="errorMessage" class="alert alert-danger d-none"></div>
            
            <div class="text-end">
                <a href="<?php echo BASE_URL; ?>/modules/request/index.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('requestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Client-side validation
    const quantity = parseFloat(document.getElementById('requested_quantity').value) || 0;
    
    if (quantity <= 0) {
        showError('Requested quantity must be greater than 0');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/modules/request/save.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo BASE_URL; ?>/modules/request/index.php';
        } else {
            showError(data.message || 'Error saving request');
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
